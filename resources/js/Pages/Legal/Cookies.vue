<script setup>
import { useI18n } from 'vue-i18n';
import MarketingLayout from '@/Layouts/MarketingLayout.vue';
import { Head } from '@inertiajs/vue3';

const { t } = useI18n();

const cookies = [
    { name: 'focusmatrix_session', purpose: 'session', duration: '2 hours', type: 'essential' },
    { name: 'XSRF-TOKEN', purpose: 'csrf', duration: '2 hours', type: 'essential' },
    { name: 'fm_locale', purpose: 'locale', duration: '1 year', type: 'essential' },
    { name: 'fm_cookie_consent', purpose: 'consent', duration: '1 year', type: 'essential' },
];
</script>

<template>
    <MarketingLayout>
        <Head :title="t('legal.cookies')" />
        <div class="max-w-3xl mx-auto px-6 py-16 prose prose-navy">
            <h1 class="font-display">{{ t('legal.cookies') }}</h1>
            <p class="lead text-graphite-600">{{ t('legal.cookies_subtitle') }}</p>

            <p>{{ t('legal.cookies_intro') }}</p>

            <h2>{{ t('legal.cookies_h_table') }}</h2>
            <table class="w-full text-sm border border-graphite-200 rounded-lg overflow-hidden not-prose">
                <thead class="bg-graphite-100 text-left">
                    <tr>
                        <th class="px-3 py-2">{{ t('legal.cookies_col_name') }}</th>
                        <th class="px-3 py-2">{{ t('legal.cookies_col_purpose') }}</th>
                        <th class="px-3 py-2">{{ t('legal.cookies_col_duration') }}</th>
                        <th class="px-3 py-2">{{ t('legal.cookies_col_type') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-graphite-100">
                    <tr v-for="c in cookies" :key="c.name">
                        <td class="px-3 py-2 font-mono text-xs">{{ c.name }}</td>
                        <td class="px-3 py-2">{{ t(`legal.cookies_purpose_${c.purpose}`) }}</td>
                        <td class="px-3 py-2">{{ c.duration }}</td>
                        <td class="px-3 py-2">
                            <span class="inline-block px-2 py-0.5 rounded-full text-xs bg-accent/10 text-accent">{{ t(`legal.cookies_type_${c.type}`) }}</span>
                        </td>
                    </tr>
                </tbody>
            </table>

            <h2>{{ t('legal.cookies_h_tracking') }}</h2>
            <p>{{ t('legal.cookies_tracking_body') }}</p>

            <h2>{{ t('legal.cookies_h_opt_out') }}</h2>
            <p>{{ t('legal.cookies_opt_out_body') }}</p>

            <p class="text-sm text-graphite-500 mt-12">{{ t('legal.last_updated') }}: {{ new Date().toISOString().slice(0,10) }}</p>
        </div>
    </MarketingLayout>
</template>
