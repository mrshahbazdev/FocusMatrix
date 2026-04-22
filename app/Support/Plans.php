<?php

namespace App\Support;

class Plans
{
    public const FREE = 'free';
    public const PRO = 'pro';
    public const TEAM = 'team';
    public const ENTERPRISE = 'enterprise';

    /**
     * @return array<string, array>
     */
    public static function all(): array
    {
        return [
            self::FREE => [
                'slug' => self::FREE,
                'name' => 'Free',
                'price_eur' => 0,
                'stripe_price' => null,
                'max_team_members' => 1,
                'ai_calls_per_month' => 50,
                'features' => [
                    'principle_dashboard', 'triage_inbox', 'decision_matrix',
                    'weekly_self_check', 'guiding_principle_widget',
                    'drop_kill_list',
                ],
            ],
            self::PRO => [
                'slug' => self::PRO,
                'name' => 'Pro',
                'price_eur' => 12,
                'stripe_price' => config('services.stripe.prices.pro'),
                'max_team_members' => 1,
                'ai_calls_per_month' => 500,
                'features' => [
                    'principle_dashboard', 'triage_inbox', 'decision_matrix',
                    'delegation_cockpit', 'weekly_self_check', 'drop_kill_list',
                    'guiding_principle_widget', 'calendar_integration', 'ai_copilot',
                ],
            ],
            self::TEAM => [
                'slug' => self::TEAM,
                'name' => 'Team',
                'price_eur' => 29,
                'stripe_price' => config('services.stripe.prices.team'),
                'max_team_members' => 10,
                'ai_calls_per_month' => 2000,
                'features' => [
                    'principle_dashboard', 'triage_inbox', 'decision_matrix',
                    'delegation_cockpit', 'weekly_self_check', 'drop_kill_list',
                    'guiding_principle_widget', 'calendar_integration', 'ai_copilot',
                    'organisation_check', 'team_analytics',
                ],
            ],
            self::ENTERPRISE => [
                'slug' => self::ENTERPRISE,
                'name' => 'Enterprise',
                'price_eur' => null, // contact sales
                'stripe_price' => null,
                'max_team_members' => null, // unlimited
                'ai_calls_per_month' => null, // unlimited
                'features' => [
                    'principle_dashboard', 'triage_inbox', 'decision_matrix',
                    'delegation_cockpit', 'weekly_self_check', 'drop_kill_list',
                    'guiding_principle_widget', 'calendar_integration', 'ai_copilot',
                    'organisation_check', 'team_analytics', 'sso_scim',
                    'audit_log', 'gdpr_export', 'priority_support',
                ],
            ],
        ];
    }

    public static function get(string $slug): ?array
    {
        return self::all()[$slug] ?? null;
    }

    public static function hasFeature(string $slug, string $feature): bool
    {
        return in_array($feature, self::get($slug)['features'] ?? [], true);
    }

    public static function resolveForUser($user): string
    {
        if (! $user) return self::FREE;
        if ($user->subscribed('default')) {
            $name = $user->subscription('default')->name ?? self::PRO;
            return in_array($name, [self::PRO, self::TEAM, self::ENTERPRISE], true) ? $name : self::PRO;
        }
        return self::FREE;
    }
}
