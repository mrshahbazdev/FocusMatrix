<script setup>
import { Link, router, useForm, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import FocusLayout from '@/Layouts/FocusLayout.vue';
import StatCard from '@/Components/StatCard.vue';
import GuidingPrinciple from '@/Components/GuidingPrinciple.vue';
import VoiceCapture from '@/Components/VoiceCapture.vue';
import {
    ArrowRightIcon,
    PlusIcon,
    FlagIcon,
    UsersIcon,
    ShieldCheckIcon,
    SparklesIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    stats: Object,
    recent_tasks: Array,
    upcoming_delegations: Array,
    category_counts: Object,
});

const { t } = useI18n();
const page = usePage();

const form = useForm({
    title: '',
    description: '',
});

function submitCapture() {
    if (!form.title.trim()) return;
    form.post(route('tasks.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
}

function onVoiceTranscribed(payload) {
    if (payload?.task) {
        router.reload({ only: ['recent_tasks', 'stats'] });
    } else if (payload?.text) {
        form.title = payload.text.slice(0, 140);
    }
}

const statusStyle = (status) => {
    switch (status) {
        case 'keep': return 'fm-badge-keep';
        case 'delegate': return 'fm-badge-delegate';
        case 'drop': return 'fm-badge-drop';
        case 'done': return 'fm-badge bg-graphite-200 text-graphite-700';
        default: return 'fm-badge bg-navy-100 text-navy-800';
    }
};

</script>

<template>
    <FocusLayout>
        <template #breadcrumbs>FocusMatrix</template>
        <template #title>{{ t('dashboard.greeting') }} {{ page.props.auth.user.name }}</template>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <!-- Stats row -->
            <div class="xl:col-span-3 grid grid-cols-2 md:grid-cols-5 gap-4">
                <StatCard tone="accent" :label="t('dashboard.focus_score')" :value="`${stats.focus_score}%`" :sub="t('dashboard.focus_score_sub')" />
                <StatCard tone="navy" :label="t('dashboard.kept')" :value="stats.kept" />
                <StatCard tone="navy" :label="t('dashboard.delegated')" :value="stats.delegated" />
                <StatCard tone="navy" :label="t('dashboard.dropped')" :value="stats.dropped" />
                <StatCard tone="neutral" :label="t('dashboard.streak')" :value="stats.streak" :sub="t('dashboard.next_check')" />
            </div>

            <!-- Quick capture -->
            <div class="xl:col-span-2 fm-card">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="fm-section-title">{{ t('dashboard.quick_capture') }}</div>
                        <h3 class="font-display text-lg font-semibold mt-1">{{ t('dashboard.add_task') }}</h3>
                    </div>
                    <Link :href="route('tasks.index', { status: 'inbox' })" class="fm-btn-ghost !py-1 text-xs">
                        {{ t('dashboard.view_all') }} <ArrowRightIcon class="w-3 h-3" />
                    </Link>
                </div>
                <form @submit.prevent="submitCapture" class="mt-4 flex flex-col sm:flex-row gap-2">
                    <input
                        v-model="form.title"
                        type="text"
                        :placeholder="t('dashboard.quick_placeholder')"
                        class="fm-input flex-1"
                    />
                    <button type="submit" class="fm-btn-primary">
                        <PlusIcon class="w-4 h-4" /> {{ t('dashboard.add_task') }}
                    </button>
                </form>
                <div v-if="form.errors.title" class="text-xs text-rose-600 mt-2">{{ form.errors.title }}</div>

                <div class="mt-4">
                    <VoiceCapture @transcribed="onVoiceTranscribed" />
                </div>

                <!-- Recent tasks -->
                <div class="mt-8">
                    <div class="fm-section-title mb-3">{{ t('dashboard.recent_tasks') }}</div>
                    <div v-if="recent_tasks.length === 0" class="text-sm text-graphite-500 italic py-8 text-center">
                        {{ t('common.empty') }}
                    </div>
                    <ul v-else class="divide-y divide-graphite-200">
                        <li v-for="task in recent_tasks" :key="task.id" class="py-3 flex items-center gap-3">
                            <div class="flex-1 min-w-0">
                                <div class="font-medium text-navy-900 truncate">{{ task.title }}</div>
                                <div class="text-xs text-graphite-500 truncate">{{ task.description }}</div>
                            </div>
                            <span :class="statusStyle(task.status)">{{ t(`task.status_${task.status}`) }}</span>
                            <Link v-if="task.status === 'inbox'" :href="route('tasks.triage', task.id)" class="fm-btn-ghost !py-1 text-xs">
                                Triage →
                            </Link>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Right column -->
            <div class="space-y-6">
                <GuidingPrinciple />

                <div class="fm-card">
                    <div class="fm-section-title mb-3">{{ t('dashboard.only_you_categories') }}</div>
                    <ul class="space-y-2 text-sm">
                        <li class="flex items-center justify-between">
                            <span class="flex items-center gap-2 text-navy-800"><FlagIcon class="w-4 h-4 text-accent" /> {{ t('dashboard.cat_strategy') }}</span>
                            <span class="font-semibold text-navy-900">{{ category_counts?.strategy || 0 }}</span>
                        </li>
                        <li class="flex items-center justify-between">
                            <span class="flex items-center gap-2 text-navy-800"><ShieldCheckIcon class="w-4 h-4 text-accent" /> {{ t('dashboard.cat_decisions') }}</span>
                            <span class="font-semibold text-navy-900">{{ category_counts?.key_decisions || 0 }}</span>
                        </li>
                        <li class="flex items-center justify-between">
                            <span class="flex items-center gap-2 text-navy-800"><UsersIcon class="w-4 h-4 text-accent" /> {{ t('dashboard.cat_people') }}</span>
                            <span class="font-semibold text-navy-900">{{ category_counts?.key_people || 0 }}</span>
                        </li>
                        <li class="flex items-center justify-between">
                            <span class="flex items-center gap-2 text-navy-800"><SparklesIcon class="w-4 h-4 text-accent" /> {{ t('dashboard.cat_responsibility') }}</span>
                            <span class="font-semibold text-navy-900">{{ category_counts?.responsibility || 0 }}</span>
                        </li>
                    </ul>
                </div>

                <div class="fm-card">
                    <div class="flex items-center justify-between">
                        <div class="fm-section-title">{{ t('dashboard.upcoming_delegations') }}</div>
                        <Link :href="route('delegations.index')" class="text-xs text-accent hover:underline">{{ t('dashboard.view_all') }}</Link>
                    </div>
                    <div v-if="upcoming_delegations.length === 0" class="text-sm text-graphite-500 italic py-6 text-center">{{ t('common.empty') }}</div>
                    <ul v-else class="mt-3 space-y-3">
                        <li v-for="d in upcoming_delegations" :key="d.id" class="flex items-center gap-3 border-b border-graphite-100 pb-2 last:border-0">
                            <div class="w-8 h-8 rounded-full bg-navy-100 text-navy-800 text-xs font-semibold flex items-center justify-center">
                                {{ d.delegate_user?.name?.[0] || '?' }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-medium text-navy-900 truncate">{{ d.task?.title }}</div>
                                <div class="text-xs text-graphite-500">→ {{ d.delegate_user?.name || d.delegate_name_fallback }}</div>
                            </div>
                            <div class="text-xs text-graphite-500">{{ d.deadline?.slice(0, 10) }}</div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </FocusLayout>
</template>
