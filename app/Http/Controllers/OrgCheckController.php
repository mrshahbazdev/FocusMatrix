<?php

namespace App\Http\Controllers;

use App\Models\OrgCheck;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class OrgCheckController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $team = $user->currentTeam;
        $now = Carbon::now();

        $current = $team ? OrgCheck::where('team_id', $team->id)
            ->where('year', $now->year)
            ->where('week', $now->weekOfYear)
            ->latest()
            ->first() : null;

        $history = $team ? OrgCheck::where('team_id', $team->id)
            ->orderByDesc('year')->orderByDesc('week')->limit(10)->get() : collect();

        return Inertia::render('OrgCheck/Index', [
            'team' => $team ? [
                'id' => $team->id,
                'name' => $team->name,
                'member_count' => $team->allUsers()->count(),
            ] : null,
            'current' => $current,
            'history' => $history,
            'year' => $now->year,
            'week' => $now->weekOfYear,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $team = $user->currentTeam;
        abort_unless($team, 400, 'No team selected.');
        $now = Carbon::now();

        $data = $request->validate([
            'decides_what_clear' => ['nullable', 'boolean'],
            'responsibilities_clear' => ['nullable', 'boolean'],
            'reports_short' => ['nullable', 'boolean'],
            'teams_small' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
        ]);

        $answers = collect($data)->only([
            'decides_what_clear', 'responsibilities_clear', 'reports_short', 'teams_small',
        ])->filter()->count();
        $score = (int) round($answers / 4 * 100);

        OrgCheck::updateOrCreate(
            ['team_id' => $team->id, 'user_id' => $user->id, 'year' => $now->year, 'week' => $now->weekOfYear],
            [...$data, 'health_score' => $score]
        );

        return back()->with('success', __('Organisation check saved.'));
    }
}
