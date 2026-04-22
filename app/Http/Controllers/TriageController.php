<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TriageController extends Controller
{
    public function show(Request $request, Task $task): Response
    {
        abort_unless($task->user_id === $request->user()->id, 403);
        return Inertia::render('Tasks/Triage', [
            'task' => $task,
        ]);
    }

    public function decide(Request $request, Task $task): RedirectResponse
    {
        abort_unless($task->user_id === $request->user()->id, 403);

        $data = $request->validate([
            'answer' => ['required', 'in:yes,no,maybe'],
            'only_you_category' => ['nullable', 'in:strategy,key_decisions,key_people,responsibility'],
        ]);

        if ($data['answer'] === 'yes') {
            $task->update([
                'status' => Task::STATUS_KEEP,
                'only_you_category' => $data['only_you_category'] ?? 'key_decisions',
            ]);
            return redirect()->route('tasks.index', ['status' => 'keep'])
                ->with('success', __('Task kept. Block focus time for it.'));
        }

        if ($data['answer'] === 'no') {
            $task->update(['status' => Task::STATUS_DELEGATE]);
            return redirect()->route('delegations.create', ['task' => $task->id])
                ->with('success', __('Great. Let\'s delegate this right.'));
        }

        // AI path — heuristic suggestion for now
        $suggestion = $this->heuristicSuggestion($task);
        $task->update([
            'ai_suggestion' => $suggestion,
            'ai_confidence' => 0.7,
        ]);
        return back()->with('success', __('Co-Pilot suggestion ready.'));
    }

    private function heuristicSuggestion(Task $task): string
    {
        $text = strtolower($task->title . ' ' . ($task->description ?? ''));
        if (str_contains($text, 'strategy') || str_contains($text, 'vision') || str_contains($text, 'strategie')) {
            return 'keep:strategy';
        }
        if (str_contains($text, 'hire') || str_contains($text, 'einstell') || str_contains($text, 'people')) {
            return 'keep:key_people';
        }
        if (str_contains($text, 'report') || str_contains($text, 'bericht') || str_contains($text, 'meeting')) {
            return 'drop';
        }
        return 'delegate';
    }
}
