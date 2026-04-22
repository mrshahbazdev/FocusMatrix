<script setup>
import { useI18n } from 'vue-i18n';
import MarketingLayout from '@/Layouts/MarketingLayout.vue';
import { Head } from '@inertiajs/vue3';

const { t } = useI18n();
</script>

<template>
    <MarketingLayout>
        <Head :title="t('legal.impressum')" />
        <div class="max-w-3xl mx-auto px-6 py-16 prose prose-navy">
            <h1 class="font-display">{{ t('legal.impressum') }}</h1>
            <p class="lead text-graphite-600">{{ t('legal.impressum_subtitle') }}</p>

            <h2>{{ t('legal.imp_h_provider') }}</h2>
            <p>
                FocusMatrix<br />
                {{ t('legal.imp_owner_label') }}: Muhammad Shahbaz<br />
                c/o allocore.de<br />
                Germany
            </p>

            <h2>{{ t('legal.imp_h_contact') }}</h2>
            <p>
                E‑Mail: <a href="mailto:support@focusmatrix.allocore.de">support@focusmatrix.allocore.de</a><br />
                Web: <a href="https://focusmatrix.allocore.de">https://focusmatrix.allocore.de</a>
            </p>

            <h2>{{ t('legal.imp_h_responsible') }}</h2>
            <p>{{ t('legal.imp_responsible_body') }}</p>

            <h2>{{ t('legal.imp_h_disclaimer') }}</h2>
            <p>{{ t('legal.imp_disclaimer_body') }}</p>

            <h2>{{ t('legal.imp_h_copyright') }}</h2>
            <p>{{ t('legal.imp_copyright_body') }}</p>

            <h2>{{ t('legal.imp_h_dispute') }}</h2>
            <p>
                {{ t('legal.imp_dispute_body_1') }}
                <a href="https://ec.europa.eu/consumers/odr/" target="_blank" rel="noopener">https://ec.europa.eu/consumers/odr/</a>.
                {{ t('legal.imp_dispute_body_2') }}
            </p>

            <p class="text-sm text-graphite-500 mt-12">{{ t('legal.last_updated') }}: {{ new Date().toISOString().slice(0,10) }}</p>
        </div>
    </MarketingLayout>
</template>
