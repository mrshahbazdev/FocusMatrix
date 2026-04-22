<?php

namespace App\Http\Controllers;

use App\Models\Delegation;
use App\Models\SelfCheck;
use App\Models\Task;
use App\Support\Plans;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AnalyticsController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $team = $user->currentTeam;

        $plan = Plans::resolveForUser($user);
        $allowed = in_array($plan, [Plans::TEAM, Plans::ENTERPRISE], true);

        if (! $team || ! $allowed) {
            return Inertia::render('Analytics/Locked', [
                'plan' => $plan,
                'has_team' => (bool) $team,
            ]);
        }

        $memberIds = $team->allUsers()->pluck('id')->toArray();

        // Tasks by status across team
        $taskStatusCounts = Task::whereIn('user_id', $memberIds)
            ->selectRaw('status, COUNT(*) as n')
            ->groupBy('status')
            ->pluck('n', 'status')
            ->toArray();

        $keep = (int) ($taskStatusCounts[Task::STATUS_KEEP] ?? 0);
        $delegate = (int) ($taskStatusCounts[Task::STATUS_DELEGATE] ?? 0);
        $drop = (int) ($taskStatusCounts[Task::STATUS_DROP] ?? 0);
        $done = (int) ($taskStatusCounts[Task::STATUS_DONE] ?? 0);
        $inbox = (int) ($taskStatusCounts[Task::STATUS_INBOX] ?? 0);
        $totalRouted = $keep + $delegate + $drop + $done;

        // Team focus score — avg of most recent self-check per member
        $latestPerMember = SelfCheck::whereIn('user_id', $memberIds)
            ->orderBy('user_id')
            ->orderByDesc('year')
            ->orderByDesc('week')
            ->get()
            ->unique('user_id')
            ->values();
        $teamFocusScore = $latestPerMember->count()
            ? (int) round($latestPerMember->avg('focus_score'))
            : 0;

        // Weekly trend (last 12 weeks): avg focus score per iso-week
        $twelveWeeksAgo = now()->subWeeks(12)->startOfWeek();
        $weeklyRaw = SelfCheck::whereIn('user_id', $memberIds)
            ->where('created_at', '>=', $twelveWeeksAgo)
            ->selectRaw('year, week, AVG(focus_score) as avg_score, COUNT(*) as n')
            ->groupBy('year', 'week')
            ->orderBy('year')
            ->orderBy('week')
            ->get();

        $weekly = $weeklyRaw->map(fn ($r) => [
            'label' => $r->year . '-W' . str_pad($r->week, 2, '0', STR_PAD_LEFT),
            'score' => (int) round($r->avg_score),
            'n' => (int) $r->n,
        ])->values();

        // Delegation stats
        $delegationsOpen = Delegation::whereIn('delegator_id', $memberIds)
            ->whereIn('status', ['open', 'in_progress'])->count();
        $delegationsDone = Delegation::whereIn('delegator_id', $memberIds)
            ->where('status', 'done')->count();
        $delegationsOverdue = Delegation::whereIn('delegator_id', $memberIds)
            ->where('status', 'overdue')->count();

        // Top delegators
        $topDelegators = Delegation::whereIn('delegator_id', $memberIds)
            ->selectRaw('delegator_id, COUNT(*) as n')
            ->groupBy('delegator_id')
            ->orderByDesc('n')
            ->limit(5)
            ->with(['delegator:id,name'])
            ->get()
            ->map(fn ($d) => [
                'user' => $d->delegator?->name ?? 'Unknown',
                'count' => (int) $d->n,
            ]);

        // Only-You category distribution (Keep tasks)
        $categoryDist = Task::whereIn('user_id', $memberIds)
            ->where('status', Task::STATUS_KEEP)
            ->whereNotNull('only_you_category')
            ->selectRaw('only_you_category, COUNT(*) as n')
            ->groupBy('only_you_category')
            ->pluck('n', 'only_you_category')
            ->toArray();

        // Per-member leaderboard
        $memberStats = collect();
        foreach ($team->allUsers() as $m) {
            $taskCount = Task::where('user_id', $m->id)->count();
            $keepCount = Task::where('user_id', $m->id)->where('status', Task::STATUS_KEEP)->count();
            $delegCount = Task::where('user_id', $m->id)->where('status', Task::STATUS_DELEGATE)->count();
            $lastCheck = SelfCheck::where('user_id', $m->id)
                ->orderByDesc('year')->orderByDesc('week')->first();
            $memberStats->push([
                'name' => $m->name,
                'email' => $m->email,
                'tasks_total' => $taskCount,
                'keep' => $keepCount,
                'delegate' => $delegCount,
                'focus_score' => $lastCheck?->focus_score ?? null,
                'last_check_at' => $lastCheck?->created_at,
            ]);
        }

        return Inertia::render('Analytics/Index', [
            'team_name' => $team->name,
            'team_size' => count($memberIds),
            'plan' => $plan,
            'team_focus_score' => $teamFocusScore,
            'task_distribution' => [
                'inbox' => $inbox,
                'keep' => $keep,
                'delegate' => $delegate,
                'drop' => $drop,
                'done' => $done,
                'total_routed' => $totalRouted,
            ],
            'category_distribution' => $categoryDist,
            'weekly_trend' => $weekly,
            'delegations' => [
                'open' => $delegationsOpen,
                'done' => $delegationsDone,
                'overdue' => $delegationsOverdue,
            ],
            'top_delegators' => $topDelegators,
            'member_stats' => $memberStats->sortByDesc('focus_score')->values(),
        ]);
    }
}
