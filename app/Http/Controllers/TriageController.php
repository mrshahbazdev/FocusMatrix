<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Services\Ai\AiManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TriageController extends Controller
{
    public function __construct(private readonly AiManager $ai) {}

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

        // Maybe → AI Co-Pilot suggestion (LLM if configured, heuristic otherwise)
        $suggestion = $this->aiOrHeuristic($request, $task);
        $task->update([
            'ai_suggestion' => $suggestion['label'],
            'ai_confidence' => $suggestion['confidence'],
        ]);
        return back()->with('success', __('Co-Pilot suggestion ready.'));
    }

    public function aiSuggest(Request $request, Task $task): JsonResponse
    {
        abort_unless($task->user_id === $request->user()->id, 403);
        $suggestion = $this->aiOrHeuristic($request, $task);
        $task->update([
            'ai_suggestion' => $suggestion['label'],
            'ai_confidence' => $suggestion['confidence'],
        ]);
        return response()->json($suggestion);
    }

    /**
     * Returns ['label' => string, 'confidence' => float, 'rationale' => string, 'source' => 'ai'|'heuristic']
     */
    private function aiOrHeuristic(Request $request, Task $task): array
    {
        $system = "You classify a manager's task into the 'Only-You-Principle' matrix. "
            . "Return ONLY JSON with keys: label (one of: keep:strategy, keep:key_decisions, keep:key_people, keep:responsibility, delegate, drop), "
            . "confidence (0.0-1.0), rationale (short, 1 sentence, same language as the task).";

        $user_msg = "TITLE: " . $task->title . "\nDESCRIPTION: " . ($task->description ?? '(none)') . "\n\n"
            . "Classify using: Strategy & direction / Key decisions with scope / Select & develop key people / Take responsibility. "
            . "If someone else can do it at least as well → delegate. If no clear goal → drop.";

        $json = $this->ai->promptJsonFor($request->user(), $system, $user_msg);
        if ($json && isset($json['label'])) {
            return [
                'label' => (string) $json['label'],
                'confidence' => (float) ($json['confidence'] ?? 0.8),
                'rationale' => (string) ($json['rationale'] ?? ''),
                'source' => 'ai',
            ];
        }

        return [
            'label' => $this->heuristicSuggestion($task),
            'confidence' => 0.55,
            'rationale' => 'Heuristic fallback (no AI key configured).',
            'source' => 'heuristic',
        ];
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
