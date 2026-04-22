<?php

namespace App\Http\Controllers;

use App\Models\KillListItem;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class KillListController extends Controller
{
    public function index(Request $request): Response
    {
        $items = KillListItem::where('user_id', $request->user()->id)
            ->latest('killed_at')
            ->get();

        return Inertia::render('KillList/Index', [
            'items' => $items,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'task_id' => ['nullable', 'exists:tasks,id'],
            'item_type' => ['required', 'in:task,meeting,report,process,other'],
            'title' => ['required', 'string', 'max:255'],
            'reason' => ['nullable', 'string'],
            'was_necessary' => ['nullable', 'boolean'],
            'served_clear_goal' => ['nullable', 'boolean'],
            'anything_missing' => ['nullable', 'boolean'],
        ]);

        if (! empty($data['task_id'])) {
            $task = Task::where('user_id', $request->user()->id)->find($data['task_id']);
            if ($task) {
                $task->update(['status' => Task::STATUS_DROP]);
            }
        }

        KillListItem::create([
            ...$data,
            'user_id' => $request->user()->id,
            'killed_at' => now(),
        ]);

        return redirect()->route('kill-list.index')
            ->with('success', __('Boldly dropped.'));
    }

    public function destroy(Request $request, KillListItem $item): RedirectResponse
    {
        abort_unless($item->user_id === $request->user()->id, 403);
        $item->delete();
        return back();
    }
}
