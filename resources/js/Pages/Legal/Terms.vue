<script setup>
import { useI18n } from 'vue-i18n';
import MarketingLayout from '@/Layouts/MarketingLayout.vue';
import { Head } from '@inertiajs/vue3';

const { t } = useI18n();
</script>

<template>
    <MarketingLayout>
        <Head :title="t('legal.terms')" />
        <div class="max-w-3xl mx-auto px-6 py-16 prose prose-navy">
            <h1 class="font-display">{{ t('legal.terms') }}</h1>
            <p class="lead text-graphite-600">{{ t('legal.terms_subtitle') }}</p>

            <h2>{{ t('legal.terms_h_service') }}</h2>
            <p>{{ t('legal.terms_service_body') }}</p>

            <h2>{{ t('legal.terms_h_account') }}</h2>
            <p>{{ t('legal.terms_account_body') }}</p>

            <h2>{{ t('legal.terms_h_acceptable') }}</h2>
            <ul>
                <li>{{ t('legal.terms_acc_1') }}</li>
                <li>{{ t('legal.terms_acc_2') }}</li>
                <li>{{ t('legal.terms_acc_3') }}</li>
                <li>{{ t('legal.terms_acc_4') }}</li>
            </ul>

            <h2>{{ t('legal.terms_h_billing') }}</h2>
            <p>{{ t('legal.terms_billing_body') }}</p>

            <h2>{{ t('legal.terms_h_cancellation') }}</h2>
            <p>{{ t('legal.terms_cancellation_body') }}</p>

            <h2>{{ t('legal.terms_h_ip') }}</h2>
            <p>{{ t('legal.terms_ip_body') }}</p>

            <h2>{{ t('legal.terms_h_warranty') }}</h2>
            <p>{{ t('legal.terms_warranty_body') }}</p>

            <h2>{{ t('legal.terms_h_liability') }}</h2>
            <p>{{ t('legal.terms_liability_body') }}</p>

            <h2>{{ t('legal.terms_h_law') }}</h2>
            <p>{{ t('legal.terms_law_body') }}</p>

            <p class="text-sm text-graphite-500 mt-12">{{ t('legal.last_updated') }}: {{ new Date().toISOString().slice(0,10) }}</p>
        </div>
    </MarketingLayout>
</template>
