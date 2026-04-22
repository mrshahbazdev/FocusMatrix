<?php

namespace Database\Seeders;

use App\Models\Delegation;
use App\Models\KillListItem;
use App\Models\SelfCheck;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Laravel\Jetstream\Jetstream;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'demo@focusmatrix.app'],
            [
                'name' => 'Alex Manager',
                'password' => Hash::make('password'),
                'locale' => 'en',
                'job_title' => 'VP of Engineering',
            ]
        );

        $user->ownedTeams()->firstOrCreate(
            ['personal_team' => true],
            ['name' => "Alex's Team"]
        );
        $user->refresh();
        $team = $user->currentTeam ?? $user->ownedTeams->first();

        $report = User::firstOrCreate(
            ['email' => 'maria@focusmatrix.app'],
            [
                'name' => 'Maria Schmidt',
                'password' => Hash::make('password'),
                'locale' => 'de',
                'job_title' => 'Product Lead',
            ]
        );
        if ($team && ! $team->users()->where('users.id', $report->id)->exists()) {
            $team->users()->attach($report->id, ['role' => 'editor']);
        }

        $samples = [
            ['Define 2026 product strategy', 'Bring the exec team to a sharp, written three-year product bet.', 'keep', 'strategy'],
            ['Approve Q2 marketing budget', 'Sign off on the revised plan. 2M€ scope.', 'keep', 'key_decisions'],
            ['Hire VP of Engineering', 'Interview final 3 candidates and decide.', 'keep', 'key_people'],
            ['Own outcome of the logistics partner migration', 'Take end-to-end responsibility for go-live.', 'keep', 'responsibility'],
            ['Draft weekly status report', 'Compile numbers from last week.', 'delegate', null],
            ['Prepare slides for board meeting', 'Pull financials and update template.', 'delegate', null],
            ['Respond to vendor NDA questions', null, 'delegate', null],
            ['Standup replacement meeting', 'Meeting without decisions. Kill it.', 'drop', null],
            ['Monthly “status update” email nobody reads', null, 'drop', null],
            ['Follow up on printer contract', null, 'inbox', null],
            ['Read industry newsletter backlog', null, 'inbox', null],
        ];

        foreach ($samples as [$title, $desc, $status, $category]) {
            Task::updateOrCreate(
                ['user_id' => $user->id, 'title' => $title],
                [
                    'team_id' => $team?->id,
                    'description' => $desc,
                    'status' => $status,
                    'only_you_category' => $category,
                    'source' => 'manual',
                ]
            );
        }

        // Delegation example
        $delegateTask = Task::where('user_id', $user->id)
            ->where('title', 'Draft weekly status report')->first();
        if ($delegateTask) {
            Delegation::updateOrCreate(
                ['task_id' => $delegateTask->id],
                [
                    'delegator_id' => $user->id,
                    'delegate_user_id' => $report->id,
                    'goal' => 'Two-page status with KPIs vs. plan and three key risks, by Friday 4pm.',
                    'decision_scope' => 'consult',
                    'deadline' => Carbon::now()->addDays(3),
                    'resources' => 'Looker dashboard, last week\'s template',
                    'no_micromanagement' => true,
                    'status' => 'in_progress',
                    'health_score' => 85,
                ]
            );
        }

        // Kill list
        KillListItem::updateOrCreate(
            ['user_id' => $user->id, 'title' => 'Monthly all-hands “status update” email'],
            [
                'item_type' => 'report',
                'reason' => 'Nobody reads it. Nothing would be missing.',
                'was_necessary' => false,
                'served_clear_goal' => false,
                'anything_missing' => false,
                'killed_at' => Carbon::now()->subWeek(),
            ]
        );

        // Self-check for current and past week
        $now = Carbon::now();
        SelfCheck::updateOrCreate(
            ['user_id' => $user->id, 'year' => $now->year, 'week' => $now->weekOfYear],
            [
                'q1_others_could_do' => 'I drafted the status report myself again.',
                'q2_delegated_late' => 'Board slides — gave them to team only 24h before.',
                'q3_to_omit_next_week' => 'The 30-min “pipeline review” nobody decides in.',
                'q4_focused_decisions' => 'Yes. Hiring + strategy got dedicated focus.',
                'focus_score' => 72,
            ]
        );
    }
}
