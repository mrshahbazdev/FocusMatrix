<?php

namespace App\Http\Controllers;

use App\Mail\DelegationAssigned;
use App\Models\Delegation;
use App\Models\Task;
use App\Models\User;
use App\Services\Ai\AiManager;
use App\Services\WebhookNotifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class DelegationController extends Controller
{
    public function __construct(
        private readonly AiManager $ai,
        private readonly WebhookNotifier $notifier,
    ) {}

    public function aiDraft(Request $request): JsonResponse
    {
        $data = $request->validate([
            'task_id' => ['required', 'exists:tasks,id'],
            'delegate_name' => ['nullable', 'string'],
        ]);
        $task = Task::where('user_id', $request->user()->id)->findOrFail($data['task_id']);

        $locale = $request->user()->locale ?? app()->getLocale();
        $system = "You draft a delegation brief for a manager following the Only-You-Principle. "
            . "Return JSON with keys: goal (1-2 sentences, concrete outcome), "
            . "decision_scope (one of: inform, consult, decide), "
            . "resources (short, what they need), "
            . "deadline_hint (short phrase). Language: " . $locale . ".";

        $delegateName = $data['delegate_name'] ?? 'the best person for this';
        $user_msg = "TASK: {$task->title}\nDETAILS: " . ($task->description ?? '(none)') . "\nDELEGATE: {$delegateName}\n\n"
            . "Write a crystal-clear delegation: what good result looks like, how much decision authority they have, and what resources/backing they need. No micromanagement.";

        $json = $this->ai->promptJsonFor($request->user(), $system, $user_msg);
        if (! $json) {
            return response()->json([
                'ok' => false,
                'message' => 'AI not configured. Add your Gemini/OpenAI/Anthropic key in Settings → AI.',
            ], 200);
        }
        return response()->json(['ok' => true, 'draft' => $json]);
    }

    public function index(Request $request): Response
    {
        $delegations = Delegation::where('delegator_id', $request->user()->id)
            ->with(['task:id,title', 'delegateUser:id,name,email,profile_photo_path'])
            ->latest()
            ->get();

        return Inertia::render('Delegations/Index', [
            'delegations' => $delegations,
        ]);
    }

    public function assignedIndex(Request $request): Response
    {
        $delegations = Delegation::where('delegate_user_id', $request->user()->id)
            ->with(['task:id,title,description', 'delegator:id,name,email,profile_photo_path'])
            ->latest()
            ->get();

        return Inertia::render('Delegations/Assigned', [
            'delegations' => $delegations,
        ]);
    }

    public function create(Request $request): Response
    {
        $taskId = $request->query('task');
        $task = $taskId ? Task::where('user_id', $request->user()->id)->find($taskId) : null;

        $team = $request->user()->currentTeam;
        $candidates = collect();
        if ($team) {
            $candidates = $team->allUsers()
                ->where('id', '!=', $request->user()->id)
                ->map(fn ($u) => [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                ])
                ->values();
        }

        return Inertia::render('Delegations/Create', [
            'task' => $task,
            'candidates' => $candidates,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'task_id' => ['required', 'exists:tasks,id'],
            'delegate_user_id' => ['nullable', 'exists:users,id'],
            'delegate_name_fallback' => ['nullable', 'string', 'max:255'],
            'goal' => ['required', 'string'],
            'decision_scope' => ['required', 'in:inform,consult,decide'],
            'deadline' => ['nullable', 'date'],
            'resources' => ['nullable', 'string'],
            'inform_list' => ['nullable', 'array'],
            'no_micromanagement' => ['boolean'],
        ]);

        $task = Task::where('user_id', $request->user()->id)->findOrFail($data['task_id']);
        $task->update(['status' => Task::STATUS_DELEGATE]);

        $isTeamMember = !empty($data['delegate_user_id']);
        $initialStatus = $isTeamMember ? Delegation::STATUS_INVITED : 'open';

        $delegation = Delegation::updateOrCreate(
            ['task_id' => $task->id],
            [
                ...$data,
                'delegator_id' => $request->user()->id,
                'original_owner_id' => $task->user_id,
                'status' => $initialStatus,
                'health_score' => 100,
                'invited_at' => $isTeamMember ? now() : null,
                'invite_token' => $isTeamMember ? Str::random(48) : null,
                'accepted_at' => null,
                'declined_at' => null,
                'decline_reason' => null,
            ]
        );

        // Email the delegate if they are a platform user
        if ($isTeamMember) {
            $delegate = User::find($data['delegate_user_id']);
            if ($delegate && $delegate->email) {
                try {
                    Mail::to($delegate->email)->queue(new DelegationAssigned($delegation));
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        }

        $delegateLabel = $data['delegate_name_fallback']
            ?? optional($delegation->fresh()->delegateUser)->name
            ?? '—';
        $this->notifier->notify(
            $request->user(),
            'New delegation: ' . $task->title,
            $data['goal'],
            [
                'Delegate' => $delegateLabel,
                'Decision scope' => ucfirst($data['decision_scope']),
                'Deadline' => $data['deadline'] ?? '—',
            ],
            url('/delegations'),
        );

        return redirect()->route('delegations.index')
            ->with('success', __('Delegated with a clear frame.'));
    }

    public function accept(Request $request, Delegation $delegation): RedirectResponse
    {
        $this->authorizeDelegate($request, $delegation);

        if ($delegation->status === Delegation::STATUS_DECLINED) {
            return back()->with('error', __('This delegation was declined and cannot be accepted.'));
        }

        $delegation->update([
            'status' => Delegation::STATUS_ACCEPTED,
            'accepted_at' => now(),
            'declined_at' => null,
            'decline_reason' => null,
        ]);

        // Ownership transfer: task moves to the delegate
        if ($delegation->task) {
            $delegation->task->update([
                'user_id' => $delegation->delegate_user_id,
            ]);
        }

        return redirect()->route('delegations.assigned')
            ->with('success', __('Delegation accepted — the task is now yours.'));
    }

    public function decline(Request $request, Delegation $delegation): RedirectResponse
    {
        $this->authorizeDelegate($request, $delegation);

        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $delegation->update([
            'status' => Delegation::STATUS_DECLINED,
            'declined_at' => now(),
            'decline_reason' => $data['reason'] ?? null,
        ]);

        // Revert ownership to original owner if it had been transferred
        if ($delegation->task && $delegation->original_owner_id) {
            $delegation->task->update([
                'user_id' => $delegation->original_owner_id,
                'status' => Task::STATUS_INBOX, // back to triage
            ]);
        }

        return redirect()->route('delegations.assigned')
            ->with('success', __('Delegation declined. The delegator has been notified.'));
    }

    public function acceptByToken(string $token): RedirectResponse
    {
        $delegation = Delegation::where('invite_token', $token)->firstOrFail();
        if (auth()->check() && auth()->id() !== $delegation->delegate_user_id) {
            abort(403);
        }
        if (!auth()->check()) {
            session(['invite_token' => $token]);
            return redirect()->route('login');
        }
        return $this->accept(request(), $delegation);
    }

    public function declineByToken(string $token): RedirectResponse
    {
        $delegation = Delegation::where('invite_token', $token)->firstOrFail();
        if (auth()->check() && auth()->id() !== $delegation->delegate_user_id) {
            abort(403);
        }
        if (!auth()->check()) {
            session(['invite_token' => $token]);
            return redirect()->route('login');
        }
        return $this->decline(request(), $delegation);
    }

    public function show(Delegation $delegation): Response
    {
        $user = request()->user();
        abort_unless(
            $delegation->delegator_id === $user->id || $delegation->delegate_user_id === $user->id,
            403
        );
        $delegation->load(['task', 'delegateUser', 'delegator', 'originalOwner']);
        return Inertia::render('Delegations/Show', ['delegation' => $delegation]);
    }

    public function update(Request $request, Delegation $delegation): RedirectResponse
    {
        abort_unless($delegation->delegator_id === $request->user()->id, 403);
        $data = $request->validate([
            'status' => ['sometimes', 'in:open,invited,accepted,declined,in_progress,done,overdue,cancelled'],
            'goal' => ['sometimes', 'string'],
            'deadline' => ['nullable', 'date'],
            'decision_scope' => ['sometimes', 'in:inform,consult,decide'],
            'resources' => ['nullable', 'string'],
            'health_score' => ['sometimes', 'integer', 'between:0,100'],
        ]);
        $delegation->update($data);
        return back()->with('success', __('Delegation updated.'));
    }

    public function destroy(Delegation $delegation): RedirectResponse
    {
        abort_unless($delegation->delegator_id === request()->user()->id, 403);
        $delegation->delete();
        return back()->with('success', __('Delegation removed.'));
    }

    public function edit() { abort(404); }

    private function authorizeDelegate(Request $request, Delegation $delegation): void
    {
        abort_unless(
            $request->user() && $delegation->delegate_user_id === $request->user()->id,
            403,
        );
    }
}
