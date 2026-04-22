<script setup>
import { useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import FocusLayout from '@/Layouts/FocusLayout.vue';
import { BuildingOffice2Icon, UsersIcon, DocumentTextIcon, CheckBadgeIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    team: Object,
    current: Object,
    history: Array,
    year: Number,
    week: Number,
});

const { t } = useI18n();

const form = useForm({
    decides_what_clear: props.current?.decides_what_clear ?? null,
    responsibilities_clear: props.current?.responsibilities_clear ?? null,
    reports_short: props.current?.reports_short ?? null,
    teams_small: props.current?.teams_small ?? null,
    notes: props.current?.notes || '',
});

const checks = [
    { key: 'decides_what_clear', label: t('orgcheck.decides_what'), icon: CheckBadgeIcon },
    { key: 'responsibilities_clear', label: t('orgcheck.responsibilities'), icon: UsersIcon },
    { key: 'reports_short', label: t('orgcheck.reports'), icon: DocumentTextIcon },
    { key: 'teams_small', label: t('orgcheck.teams'), icon: BuildingOffice2Icon },
];

function toggle(key) {
    form[key] = form[key] === true ? false : form[key] === false ? null : true;
}

function submit() {
    form.post(route('org-check.store'), { preserveScroll: true });
}
</script>

<template>
    <FocusLayout>
        <template #breadcrumbs>FocusMatrix · {{ t('nav.orgcheck') }}</template>
        <template #title>{{ t('orgcheck.title') }}</template>

        <div v-if="!team" class="fm-card text-center py-12">
            <BuildingOffice2Icon class="w-10 h-10 mx-auto text-graphite-300" />
            <p class="mt-3 text-sm text-graphite-600">Create or switch to a team to run an organisation check.</p>
        </div>

        <div v-else class="grid md:grid-cols-3 gap-6">
            <form @submit.prevent="submit" class="md:col-span-2 fm-card space-y-6">
                <div>
                    <div class="fm-section-title">{{ team.name }} · W{{ week }} / {{ year }}</div>
                    <p class="text-sm text-graphite-600 mt-1">{{ t('orgcheck.subtitle') }}</p>
                </div>

                <div class="space-y-3">
                    <div v-for="c in checks" :key="c.key" class="flex items-center gap-4 p-4 border border-graphite-200 rounded-lg">
                        <component :is="c.icon" class="w-6 h-6 text-accent" />
                        <div class="flex-1 text-sm text-navy-800">{{ c.label }}</div>
                        <button type="button" @click="toggle(c.key)" :class="[
                            'px-3 py-1 text-xs rounded-md border',
                            form[c.key] === true ? 'bg-emerald-100 border-emerald-300 text-emerald-800'
                            : form[c.key] === false ? 'bg-rose-100 border-rose-300 text-rose-800'
                            : 'border-graphite-200 text-graphite-600'
                        ]">
                            {{ form[c.key] === true ? 'Yes' : form[c.key] === false ? 'No' : '?' }}
                        </button>
                    </div>
                </div>

                <div>
                    <label class="fm-label">Notes</label>
                    <textarea v-model="form.notes" rows="3" class="fm-input"></textarea>
                </div>

                <div class="flex justify-end">
                    <button type="submit" :disabled="form.processing" class="fm-btn-primary disabled:opacity-50">
                        {{ t('common.save') }}
                    </button>
                </div>
            </form>

            <div class="space-y-6">
                <div class="fm-card">
                    <div class="fm-section-title mb-2">{{ t('orgcheck.health') }}</div>
                    <div class="text-4xl font-display font-semibold text-navy-900">{{ current?.health_score ?? '—' }}<span class="text-lg text-graphite-500">%</span></div>
                    <div class="text-xs text-graphite-500 mt-1">{{ team.member_count }} members</div>
                </div>
                <div class="fm-card">
                    <div class="fm-section-title mb-3">History</div>
                    <div v-if="history.length === 0" class="text-sm text-graphite-500">{{ t('common.empty') }}</div>
                    <ul v-else class="space-y-2 text-sm">
                        <li v-for="h in history" :key="h.id" class="flex items-center justify-between">
                            <span>W{{ h.week }} / {{ h.year }}</span>
                            <span class="font-semibold">{{ h.health_score ?? '—' }}%</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </FocusLayout>
</template>
