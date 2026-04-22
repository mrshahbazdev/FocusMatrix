<?php

namespace App\Http\Controllers;

use App\Models\Delegation;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DelegationController extends Controller
{
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
