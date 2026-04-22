<script setup>
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import FocusLayout from '@/Layouts/FocusLayout.vue';
import { LockClosedIcon, ChartBarIcon } from '@heroicons/vue/24/outline';

defineProps({ plan: String, has_team: Boolean });
const { t } = useI18n();
</script>

<template>
    <FocusLayout>
        <template #breadcrumbs>FocusMatrix · {{ t('nav.analytics') }}</template>
        <template #title>{{ t('analytics.title') }}</template>

        <div class="fm-card max-w-2xl">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center">
                    <LockClosedIcon class="w-6 h-6" />
                </div>
                <div class="flex-1">
                    <h3 class="font-display text-lg font-semibold text-navy-900 mb-1">
                        {{ has_team ? t('analytics.locked_plan_title') : t('analytics.locked_noteam_title') }}
                    </h3>
                    <p class="text-sm text-graphite-600 mb-4">
                        {{ has_team ? t('analytics.locked_plan_desc', { plan }) : t('analytics.locked_noteam_desc') }}
                    </p>
                    <div class="flex items-center gap-3">
                        <Link v-if="has_team" :href="route('billing.index')" class="fm-btn-primary">
                            <ChartBarIcon class="w-4 h-4 mr-1 inline" /> {{ t('analytics.upgrade_cta') }}
                        </Link>
                        <Link v-else :href="route('dashboard')" class="fm-btn-secondary">
                            {{ t('analytics.back_to_dashboard') }}
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </FocusLayout>
</template>
