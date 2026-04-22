<script setup>
import { Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import FocusLayout from '@/Layouts/FocusLayout.vue';
import { CalendarDaysIcon, ExclamationTriangleIcon, UsersIcon, ArrowTopRightOnSquareIcon, InboxIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    connected: Boolean,
    account_email: String,
    events: Array,
    weak_meetings: Array,
});

const { t } = useI18n();

function formatTime(value) {
    if (!value) return '';
    const d = new Date(value);
    return d.toLocaleString(undefined, { weekday: 'short', hour: '2-digit', minute: '2-digit', day: 'numeric', month: 'short' });
}

function importWeak() {
    router.post(route('calendar.import-weak'));
}
</script>

<template>
    <FocusLayout>
        <template #breadcrumbs>FocusMatrix · {{ t('nav.calendar') }}</template>
        <template #title>{{ t('calendar.title') }}</template>

        <p class="text-sm text-graphite-600 max-w-2xl mb-6">{{ t('calendar.subtitle') }}</p>

        <!-- Not connected -->
        <div v-if="!connected" class="fm-card text-center py-16">
            <CalendarDaysIcon class="w-12 h-12 mx-auto text-graphite-300" />
            <p class="mt-4 text-navy-800 max-w-md mx-auto">{{ t('calendar.connect_cta') }}</p>
            <Link :href="route('integrations.index')" class="fm-btn-primary mt-6 inline-flex">
                {{ t('integrations.connect') }}
            </Link>
        </div>

        <div v-else class="grid lg:grid-cols-3 gap-6">
            <!-- Upcoming events -->
            <div class="lg:col-span-2 fm-card">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="fm-section-title">{{ t('calendar.upcoming') }}</div>
                        <h3 class="font-display text-lg font-semibold mt-1">{{ account_email }}</h3>
                    </div>
                </div>
                <div v-if="events.length === 0" class="text-center text-sm text-graphite-500 py-12">
                    {{ t('calendar.no_events') }}
                </div>
                <ul v-else class="mt-4 divide-y divide-graphite-200">
                    <li v-for="e in events" :key="e.id" class="py-4 flex items-start gap-4">
                        <div class="w-12 text-center shrink-0">
                            <div class="text-xs uppercase text-graphite-500">{{ new Date(e.start).toLocaleString(undefined, { weekday: 'short' }) }}</div>
                            <div class="font-display text-xl text-navy-900 font-semibold">{{ new Date(e.start).toLocaleString(undefined, { day: '2-digit' }) }}</div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <a :href="e.link" target="_blank" rel="noopener" class="font-medium text-navy-900 hover:underline inline-flex items-center gap-1">
                                {{ e.title }}
                                <ArrowTopRightOnSquareIcon class="w-3.5 h-3.5 text-graphite-400" />
                            </a>
                            <div class="text-xs text-graphite-500 mt-0.5">
                                {{ formatTime(e.start) }}<span v-if="e.end"> — {{ formatTime(e.end) }}</span>
                            </div>
                            <div class="mt-2 flex flex-wrap items-center gap-1.5">
                                <span v-if="e.attendees.length" class="fm-badge bg-navy-50 text-navy-800 flex items-center gap-1">
                                    <UsersIcon class="w-3 h-3" /> {{ e.attendees.length }} {{ t('calendar.attendees') }}
                                </span>
                                <span v-for="f in e.flags" :key="f" class="fm-badge bg-amber-100 text-amber-800 flex items-center gap-1">
                                    <ExclamationTriangleIcon class="w-3 h-3" />
                                    {{ t(`calendar.flag_${f}`) }}
                                </span>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>

            <!-- Weak meetings -->
            <div class="fm-card">
                <div class="flex items-center justify-between">
                    <div class="fm-section-title">{{ t('calendar.weak_meetings') }}</div>
                    <span class="fm-badge bg-rose-100 text-rose-800">{{ weak_meetings.length }}</span>
                </div>
                <p class="text-xs text-graphite-600 mt-1">{{ t('calendar.weak_meetings_sub') }}</p>

                <div v-if="weak_meetings.length === 0" class="text-center text-sm text-graphite-500 py-8">
                    {{ t('calendar.no_weak') }}
                </div>
                <ul v-else class="mt-4 space-y-3">
                    <li v-for="e in weak_meetings" :key="e.id" class="p-3 rounded-lg border border-amber-200 bg-amber-50/70">
                        <div class="font-medium text-navy-900 text-sm">{{ e.title }}</div>
                        <div class="text-xs text-graphite-500 mt-0.5">{{ formatTime(e.start) }}</div>
                        <div class="mt-1 flex flex-wrap gap-1">
                            <span v-for="f in e.flags" :key="f" class="fm-badge bg-white text-amber-800 text-[10px]">
                                {{ t(`calendar.flag_${f}`) }}
                            </span>
                        </div>
                    </li>
                </ul>

                <button v-if="weak_meetings.length" @click="importWeak" class="mt-4 w-full fm-btn-secondary">
                    <InboxIcon class="w-4 h-4" /> {{ t('calendar.import_weak') }}
                </button>
            </div>
        </div>
    </FocusLayout>
</template>
