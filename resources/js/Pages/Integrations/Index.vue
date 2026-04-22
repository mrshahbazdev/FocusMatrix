<script setup>
import { Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import FocusLayout from '@/Layouts/FocusLayout.vue';
import { CalendarDaysIcon, CheckCircleIcon, LinkIcon, ClockIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    google: Object,
    google_configured: Boolean,
});

const { t } = useI18n();

function disconnect() {
    if (!confirm('Disconnect?')) return;
    router.delete(route('integrations.google.disconnect'));
}

const comingSoon = [
    { key: 'outlook' },
    { key: 'slack' },
    { key: 'teams' },
];
</script>

<template>
    <FocusLayout>
        <template #breadcrumbs>FocusMatrix · {{ t('nav.integrations') }}</template>
        <template #title>{{ t('integrations.title') }}</template>

        <p class="text-sm text-graphite-600 max-w-xl mb-6">{{ t('integrations.subtitle') }}</p>

        <div class="grid md:grid-cols-2 gap-6">
            <!-- Google Calendar -->
            <div class="fm-card">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-xl bg-accent/10 text-accent flex items-center justify-center">
                        <CalendarDaysIcon class="w-6 h-6" />
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <h3 class="font-display text-lg font-semibold text-navy-900">{{ t('integrations.google_name') }}</h3>
                            <span v-if="google.connected" class="fm-badge bg-emerald-100 text-emerald-800 flex items-center gap-1">
                                <CheckCircleIcon class="w-3 h-3" /> Connected
                            </span>
                        </div>
                        <p class="text-sm text-graphite-600 mt-1">{{ t('integrations.google_desc') }}</p>

                        <div v-if="google.connected" class="mt-4 text-sm text-navy-800 space-y-1">
                            <div class="flex items-center gap-2">
                                <LinkIcon class="w-4 h-4 text-graphite-400" />
                                {{ t('integrations.connected_as') }}: <span class="font-medium">{{ google.account_email || '—' }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <ClockIcon class="w-4 h-4 text-graphite-400" />
                                {{ t('integrations.last_synced') }}: <span class="font-medium">
                                    {{ google.last_synced_at ? new Date(google.last_synced_at).toLocaleString() : t('integrations.never_synced') }}
                                </span>
                            </div>
                        </div>

                        <div v-if="!google_configured" class="mt-4 p-3 rounded-lg bg-amber-50 border border-amber-200 text-xs text-amber-800">
                            {{ t('integrations.not_configured') }}
                        </div>

                        <div class="mt-5 flex items-center gap-3">
                            <a v-if="!google.connected" :href="route('integrations.google.connect')"
                                :class="['fm-btn-primary', !google_configured ? 'pointer-events-none opacity-50' : '']">
                                {{ t('integrations.connect') }}
                            </a>
                            <Link v-if="google.connected" :href="route('calendar.index')" class="fm-btn-secondary">
                                {{ t('nav.calendar') }}
                            </Link>
                            <button v-if="google.connected" @click="disconnect" class="fm-btn-ghost text-rose-600">
                                {{ t('integrations.disconnect') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Coming soon cards -->
            <div v-for="item in comingSoon" :key="item.key" class="fm-card opacity-75">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-xl bg-graphite-100 text-graphite-500 flex items-center justify-center">
                        <CalendarDaysIcon class="w-6 h-6" />
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <h3 class="font-display text-lg font-semibold text-navy-900">{{ t(`integrations.${item.key}`) }}</h3>
                            <span class="fm-badge bg-graphite-100 text-graphite-700">{{ t('integrations.coming_soon') }}</span>
                        </div>
                        <p class="text-sm text-graphite-600 mt-1">
                            {{ t('integrations.google_desc') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </FocusLayout>
</template>
