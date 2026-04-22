<script setup>
import { useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import FocusLayout from '@/Layouts/FocusLayout.vue';
import { CalendarDaysIcon, CheckIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    current: Object,
    history: Array,
    year: Number,
    week: Number,
});

const { t } = useI18n();

const form = useForm({
    q1_others_could_do: props.current?.q1_others_could_do || '',
    q2_delegated_late: props.current?.q2_delegated_late || '',
    q3_to_omit_next_week: props.current?.q3_to_omit_next_week || '',
    q4_focused_decisions: props.current?.q4_focused_decisions || '',
});

function submit() {
    form.post(route('self-check.store'), { preserveScroll: true });
}
</script>

<template>
    <FocusLayout>
        <template #breadcrumbs>FocusMatrix · {{ t('nav.selfcheck') }}</template>
        <template #title>{{ t('selfcheck.title') }}</template>

        <div class="grid md:grid-cols-3 gap-6">
            <form @submit.prevent="submit" class="md:col-span-2 fm-card space-y-6">
                <div>
                    <div class="fm-section-title">Week {{ week }} / {{ year }}</div>
                    <p class="text-sm text-graphite-600 mt-1">{{ t('selfcheck.subtitle') }}</p>
                </div>

                <div v-for="q in [
                    { key: 'q1_others_could_do', label: t('selfcheck.q1') },
                    { key: 'q2_delegated_late', label: t('selfcheck.q2') },
                    { key: 'q3_to_omit_next_week', label: t('selfcheck.q3') },
                    { key: 'q4_focused_decisions', label: t('selfcheck.q4') },
                ]" :key="q.key">
                    <label class="fm-label">{{ q.label }}</label>
                    <textarea v-model="form[q.key]" rows="3" class="fm-input"></textarea>
                </div>

                <div class="flex items-center justify-between">
                    <div v-if="current" class="text-xs text-emerald-700 flex items-center gap-1">
                        <CheckIcon class="w-4 h-4" /> {{ t('selfcheck.saved') }}
                    </div>
                    <div v-else></div>
                    <button type="submit" :disabled="form.processing" class="fm-btn-primary disabled:opacity-50">
                        {{ t('selfcheck.save') }}
                    </button>
                </div>
            </form>

            <div class="fm-card">
                <div class="fm-section-title mb-3">History</div>
                <div v-if="history.length === 0" class="text-sm text-graphite-500">{{ t('common.empty') }}</div>
                <ul v-else class="space-y-3">
                    <li v-for="h in history" :key="h.id" class="flex items-center justify-between text-sm">
                        <div class="flex items-center gap-2">
                            <CalendarDaysIcon class="w-4 h-4 text-accent" />
                            <span class="text-navy-900">W{{ h.week }} / {{ h.year }}</span>
                        </div>
                        <span class="font-semibold text-navy-900">{{ h.focus_score ?? '—' }}%</span>
                    </li>
                </ul>
            </div>
        </div>
    </FocusLayout>
</template>
