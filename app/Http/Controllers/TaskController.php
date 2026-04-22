<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TaskController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $status = $request->string('status')->toString() ?: 'inbox';
        $validStatuses = ['inbox', 'keep', 'delegate', 'drop', 'done'];
        if (! in_array($status, $validStatuses, true)) {
            $status = 'inbox';
        }

        $tasks = Task::where('user_id', $user->id)
            ->where('status', $status)
            ->orderByDesc('created_at')
            ->get();

        return Inertia::render('Tasks/Index', [
            'tasks' => $tasks,
            'status' => $status,
            'counts' => [
                'inbox' => Task::where('user_id', $user->id)->where('status', 'inbox')->count(),
                'keep' => Task::where('user_id', $user->id)->where('status', 'keep')->count(),
                'delegate' => Task::where('user_id', $user->id)->where('status', 'delegate')->count(),
                'drop' => Task::where('user_id', $user->id)->where('status', 'drop')->count(),
                'done' => Task::where('user_id', $user->id)->where('status', 'done')->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'due_at' => ['nullable', 'date'],
        ]);

        $task = $request->user()->tasks()->create([
            ...$data,
            'team_id' => $request->user()->currentTeam?->id,
            'status' => Task::STATUS_INBOX,
            'source' => 'manual',
        ]);

        return back()->with('success', __('Task captured.'));
    }

    public function show(Task $task): Response
    {
        $this->authorizeTask($task);
        $task->load('delegation.delegateUser');
        return Inertia::render('Tasks/Show', ['task' => $task]);
    }

    public function update(Request $request, Task $task): RedirectResponse
    {
        $this->authorizeTask($task);

        $data = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['sometimes', 'in:inbox,keep,delegate,drop,done'],
            'only_you_category' => ['nullable', 'in:strategy,key_decisions,key_people,responsibility'],
            'due_at' => ['nullable', 'date'],
            'focused_block_at' => ['nullable', 'date'],
        ]);

        if (($data['status'] ?? null) === 'done') {
            $data['completed_at'] = now();
        }

        $task->update($data);
        return back()->with('success', __('Task updated.'));
    }

    public function destroy(Task $task): RedirectResponse
    {
        $this->authorizeTask($task);
        $task->delete();
        return back()->with('success', __('Task deleted.'));
    }

    public function create() { abort(404); }
    public function edit() { abort(404); }

    private function authorizeTask(Task $task): void
    {
        abort_unless($task->user_id === request()->user()?->id, 403);
    }
}
