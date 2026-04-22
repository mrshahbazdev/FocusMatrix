<?php

namespace App\Http\Controllers;

use App\Models\SelfCheck;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class SelfCheckController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $now = Carbon::now();

        $current = SelfCheck::where('user_id', $user->id)
            ->where('year', $now->year)
            ->where('week', $now->weekOfYear)
            ->first();

        $history = SelfCheck::where('user_id', $user->id)
            ->orderByDesc('year')->orderByDesc('week')
            ->limit(10)
            ->get();

        return Inertia::render('SelfCheck/Index', [
            'current' => $current,
            'history' => $history,
            'year' => $now->year,
            'week' => $now->weekOfYear,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $now = Carbon::now();

        $data = $request->validate([
            'q1_others_could_do' => ['nullable', 'string'],
            'q2_delegated_late' => ['nullable', 'string'],
            'q3_to_omit_next_week' => ['nullable', 'string'],
            'q4_focused_decisions' => ['nullable', 'string'],
        ]);

        $weekStart = $now->copy()->startOfWeek();
        $weekTasks = Task::where('user_id', $user->id)
            ->where('updated_at', '>=', $weekStart)->get();
        $kept = $weekTasks->where('status', Task::STATUS_KEEP)->count();
        $delegated = $weekTasks->where('status', Task::STATUS_DELEGATE)->count();
        $dropped = $weekTasks->where('status', Task::STATUS_DROP)->count();
        $total = max(1, $kept + $delegated + $dropped);
        $score = (int) round((($kept + $delegated) / $total) * 100);

        SelfCheck::updateOrCreate(
            ['user_id' => $user->id, 'year' => $now->year, 'week' => $now->weekOfYear],
            [...$data, 'focus_score' => $score]
        );

        return back()->with('success', __('Self-check saved. Have a great weekend.'));
    }
}
