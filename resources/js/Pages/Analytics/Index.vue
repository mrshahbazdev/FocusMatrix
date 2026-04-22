<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import FocusLayout from '@/Layouts/FocusLayout.vue';
import {
    ChartBarIcon, UserGroupIcon, CheckBadgeIcon, ArrowTrendingUpIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    team_name: String,
    team_size: Number,
    plan: String,
    team_focus_score: Number,
    task_distribution: Object,
    category_distribution: Object,
    weekly_trend: Array,
    delegations: Object,
    top_delegators: Array,
    member_stats: Array,
});

const { t } = useI18n();

const routed = computed(() => Math.max(props.task_distribution.total_routed, 1));
const pct = (n) => Math.round((n / routed.value) * 100);

const categoryLabel = (k) => t(`dashboard.cat_${k}`) || k;

const maxWeekly = computed(() => Math.max(...props.weekly_trend.map(w => w.score), 1));

const scoreColor = (s) => {
    if (s === null || s === undefined) return 'text-graphite-400';
    if (s >= 70) return 'text-emerald-600';
    if (s >= 40) return 'text-amber-600';
    return 'text-rose-600';
};
</script>

<template>
    <FocusLayout>
        <template #breadcrumbs>FocusMatrix · {{ t('nav.analytics') }}</template>
        <template #title>{{ t('analytics.title') }}</template>

        <div class="mb-6 flex items-center gap-3 text-sm text-graphite-600">
            <UserGroupIcon class="w-4 h-4" />
            <span>{{ team_name }}</span>
            <span class="fm-badge bg-graphite-100 text-graphite-700">{{ team_size }} {{ t('analytics.members') }}</span>
            <span class="fm-badge bg-accent/10 text-accent capitalize">{{ plan }}</span>
        </div>

        <!-- Headline metrics -->
        <div class="grid md:grid-cols-4 gap-4 mb-6">
            <div class="fm-card">
                <div class="text-xs text-graphite-500 uppercase tracking-wide mb-1">{{ t('analytics.team_focus_score') }}</div>
                <div class="text-4xl font-display font-bold" :class="scoreColor(team_focus_score)">{{ team_focus_score }}%</div>
                <div class="text-xs text-graphite-500 mt-1">{{ t('analytics.focus_score_sub') }}</div>
            </div>
            <div class="fm-card">
                <div class="text-xs text-graphite-500 uppercase tracking-wide mb-1">{{ t('analytics.keep_tasks') }}</div>
                <div class="text-4xl font-display font-bold text-navy-900">{{ task_distribution.keep }}</div>
                <div class="text-xs text-graphite-500 mt-1">{{ pct(task_distribution.keep) }}% {{ t('analytics.of_routed') }}</div>
            </div>
            <div class="fm-card">
                <div class="text-xs text-graphite-500 uppercase tracking-wide mb-1">{{ t('analytics.delegations_open') }}</div>
                <div class="text-4xl font-display font-bold text-navy-900">{{ delegations.open }}</div>
                <div class="text-xs text-graphite-500 mt-1">
                    {{ delegations.done }} {{ t('analytics.done') }} · {{ delegations.overdue }} {{ t('analytics.overdue') }}
                </div>
            </div>
            <div class="fm-card">
                <div class="text-xs text-graphite-500 uppercase tracking-wide mb-1">{{ t('analytics.drop_rate') }}</div>
                <div class="text-4xl font-display font-bold text-navy-900">{{ pct(task_distribution.drop) }}%</div>
                <div class="text-xs text-graphite-500 mt-1">{{ task_distribution.drop }} {{ t('analytics.tasks_dropped') }}</div>
            </div>
        </div>

        <!-- Task status distribution -->
        <div class="fm-card mb-6">
            <div class="flex items-center gap-2 mb-3">
                <ChartBarIcon class="w-5 h-5 text-accent" />
                <h3 class="font-display text-lg font-semibold text-navy-900">{{ t('analytics.task_distribution') }}</h3>
            </div>
            <div class="space-y-2 text-sm">
                <div v-for="key in ['keep', 'delegate', 'drop', 'done']" :key="key" class="flex items-center gap-3">
                    <div class="w-24 text-graphite-600 capitalize">{{ t(`nav.${key}`) || key }}</div>
                    <div class="flex-1 bg-graphite-100 rounded-full h-3 overflow-hidden">
                        <div class="h-3 rounded-full"
                            :class="{
                                'bg-emerald-500': key === 'keep',
                                'bg-accent': key === 'delegate',
                                'bg-rose-500': key === 'drop',
                                'bg-navy-600': key === 'done',
                            }"
                            :style="{ width: pct(task_distribution[key]) + '%' }"></div>
                    </div>
                    <div class="w-16 text-right text-navy-800 font-medium">
                        {{ task_distribution[key] }} <span class="text-graphite-500 text-xs">({{ pct(task_distribution[key]) }}%)</span>
                    </div>
                </div>
                <div v-if="task_distribution.inbox" class="text-xs text-amber-700 mt-3">
                    {{ task_distribution.inbox }} {{ t('analytics.still_in_inbox') }}
                </div>
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-6 mb-6">
            <!-- Weekly trend -->
            <div class="fm-card">
                <div class="flex items-center gap-2 mb-3">
                    <ArrowTrendingUpIcon class="w-5 h-5 text-accent" />
                    <h3 class="font-display text-lg font-semibold text-navy-900">{{ t('analytics.weekly_trend') }}</h3>
                </div>
                <div v-if="weekly_trend.length === 0" class="text-sm text-graphite-500 py-8 text-center">
                    {{ t('analytics.no_checks_yet') }}
                </div>
                <div v-else class="flex items-end gap-1 h-40">
                    <div v-for="w in weekly_trend" :key="w.label" class="flex-1 flex flex-col items-center group">
                        <div class="w-full bg-accent/70 rounded-t hover:bg-accent transition relative"
                            :style="{ height: (w.score / maxWeekly * 100) + '%' }">
                            <div class="absolute -top-6 left-1/2 -translate-x-1/2 text-xs font-mono bg-navy-900 text-white px-1 py-0.5 rounded opacity-0 group-hover:opacity-100 whitespace-nowrap">
                                {{ w.score }}%
                            </div>
                        </div>
                        <div class="text-xs text-graphite-500 mt-1 truncate w-full text-center">{{ w.label.slice(-3) }}</div>
                    </div>
                </div>
            </div>

            <!-- Only-You category distribution -->
            <div class="fm-card">
                <div class="flex items-center gap-2 mb-3">
                    <CheckBadgeIcon class="w-5 h-5 text-accent" />
                    <h3 class="font-display text-lg font-semibold text-navy-900">{{ t('analytics.category_distribution') }}</h3>
                </div>
                <div v-if="Object.keys(category_distribution).length === 0" class="text-sm text-graphite-500 py-8 text-center">
                    {{ t('analytics.no_keep_tasks') }}
                </div>
                <div v-else class="space-y-2 text-sm">
                    <div v-for="(n, key) in category_distribution" :key="key" class="flex items-center gap-3">
                        <div class="flex-1">{{ categoryLabel(key) }}</div>
                        <div class="flex-1 bg-graphite-100 rounded-full h-2 overflow-hidden">
                            <div class="h-2 bg-accent rounded-full"
                                :style="{ width: (n / Math.max(...Object.values(category_distribution)) * 100) + '%' }"></div>
                        </div>
                        <div class="w-12 text-right text-navy-800 font-medium">{{ n }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Member leaderboard -->
        <div class="fm-card mb-6">
            <h3 class="font-display text-lg font-semibold text-navy-900 mb-3">{{ t('analytics.member_leaderboard') }}</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="text-xs text-graphite-500 uppercase text-left">
                        <tr>
                            <th class="py-2 pr-4">{{ t('analytics.col_member') }}</th>
                            <th class="py-2 pr-4">{{ t('analytics.col_focus') }}</th>
                            <th class="py-2 pr-4">{{ t('analytics.col_keep') }}</th>
                            <th class="py-2 pr-4">{{ t('analytics.col_delegate') }}</th>
                            <th class="py-2 pr-4">{{ t('analytics.col_total') }}</th>
                            <th class="py-2 pr-4">{{ t('analytics.col_last_check') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="m in member_stats" :key="m.email" class="border-t border-graphite-100">
                            <td class="py-2 pr-4">
                                <div class="font-medium text-navy-900">{{ m.name }}</div>
                                <div class="text-xs text-graphite-500">{{ m.email }}</div>
                            </td>
                            <td class="py-2 pr-4 font-medium" :class="scoreColor(m.focus_score)">
                                {{ m.focus_score !== null ? m.focus_score + '%' : '—' }}
                            </td>
                            <td class="py-2 pr-4">{{ m.keep }}</td>
                            <td class="py-2 pr-4">{{ m.delegate }}</td>
                            <td class="py-2 pr-4">{{ m.tasks_total }}</td>
                            <td class="py-2 pr-4 text-xs text-graphite-500">
                                {{ m.last_check_at ? new Date(m.last_check_at).toLocaleDateString() : '—' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Top delegators -->
        <div v-if="top_delegators.length" class="fm-card">
            <h3 class="font-display text-lg font-semibold text-navy-900 mb-3">{{ t('analytics.top_delegators') }}</h3>
            <ul class="space-y-2 text-sm">
                <li v-for="(d, i) in top_delegators" :key="i" class="flex items-center justify-between border-b border-graphite-100 pb-2">
                    <span class="text-navy-800">{{ d.user }}</span>
                    <span class="fm-badge bg-accent/10 text-accent">{{ d.count }} {{ t('analytics.delegations') }}</span>
                </li>
            </ul>
        </div>
    </FocusLayout>
</template>
