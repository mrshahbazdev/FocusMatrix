<?php

namespace App\Http\Controllers;

use App\Models\Delegation;
use App\Models\Task;
use App\Services\Ai\AiManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DelegationController extends Controller
{
    public function __construct(private readonly AiManager $ai) {}

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
            ->with(['task:id,title', 'delegateUser:id,name,profile_photo_path'])
            ->latest()
            ->get();

        return Inertia::render('Delegations/Index', [
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

        Delegation::updateOrCreate(
            ['task_id' => $task->id],
            [
                ...$data,
                'delegator_id' => $request->user()->id,
                'status' => 'open',
                'health_score' => 100,
            ]
        );

        return redirect()->route('delegations.index')
            ->with('success', __('Delegated with a clear frame.'));
    }

    public function show(Delegation $delegation): Response
    {
        abort_unless($delegation->delegator_id === request()->user()->id, 403);
        $delegation->load(['task', 'delegateUser']);
        return Inertia::render('Delegations/Show', ['delegation' => $delegation]);
    }

    public function update(Request $request, Delegation $delegation): RedirectResponse
    {
        abort_unless($delegation->delegator_id === $request->user()->id, 403);
        $data = $request->validate([
            'status' => ['sometimes', 'in:open,in_progress,done,overdue,cancelled'],
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
}
