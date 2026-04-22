<script setup>
import { useI18n } from 'vue-i18n';
import MarketingLayout from '@/Layouts/MarketingLayout.vue';
import { Head } from '@inertiajs/vue3';

const { t } = useI18n();
</script>

<template>
    <MarketingLayout>
        <Head :title="t('legal.privacy')" />
        <div class="max-w-3xl mx-auto px-6 py-16 prose prose-navy">
            <h1 class="font-display">{{ t('legal.privacy') }}</h1>
            <p class="lead text-graphite-600">{{ t('legal.privacy_subtitle') }}</p>

            <h2>{{ t('legal.priv_h_controller') }}</h2>
            <p>
                FocusMatrix · Muhammad Shahbaz<br />
                c/o allocore.de · Germany<br />
                E‑Mail: <a href="mailto:privacy@focusmatrix.allocore.de">privacy@focusmatrix.allocore.de</a>
            </p>

            <h2>{{ t('legal.priv_h_data_categories') }}</h2>
            <ul>
                <li><strong>{{ t('legal.priv_cat_account') }}:</strong> {{ t('legal.priv_cat_account_body') }}</li>
                <li><strong>{{ t('legal.priv_cat_usage') }}:</strong> {{ t('legal.priv_cat_usage_body') }}</li>
                <li><strong>{{ t('legal.priv_cat_content') }}:</strong> {{ t('legal.priv_cat_content_body') }}</li>
                <li><strong>{{ t('legal.priv_cat_integrations') }}:</strong> {{ t('legal.priv_cat_integrations_body') }}</li>
                <li><strong>{{ t('legal.priv_cat_billing') }}:</strong> {{ t('legal.priv_cat_billing_body') }}</li>
            </ul>

            <h2>{{ t('legal.priv_h_purpose') }}</h2>
            <p>{{ t('legal.priv_purpose_body') }}</p>

            <h2>{{ t('legal.priv_h_legal_basis') }}</h2>
            <ul>
                <li>Art. 6 (1) (b) DSGVO — {{ t('legal.priv_basis_contract') }}</li>
                <li>Art. 6 (1) (a) DSGVO — {{ t('legal.priv_basis_consent') }}</li>
                <li>Art. 6 (1) (f) DSGVO — {{ t('legal.priv_basis_legitimate') }}</li>
            </ul>

            <h2>{{ t('legal.priv_h_processors') }}</h2>
            <p>{{ t('legal.priv_processors_intro') }}</p>
            <ul>
                <li><strong>Stripe Payments Europe Ltd.</strong> (Ireland) — {{ t('legal.priv_processor_stripe') }}</li>
                <li><strong>Google Ireland Ltd.</strong> — {{ t('legal.priv_processor_google') }}</li>
                <li><strong>Google Gemini / OpenAI / Anthropic</strong> — {{ t('legal.priv_processor_ai') }} <em>({{ t('legal.priv_processor_ai_note') }})</em></li>
                <li><strong>allocore.de</strong> ({{ t('legal.priv_processor_hoster_loc') }}) — {{ t('legal.priv_processor_hoster') }}</li>
            </ul>

            <h2>{{ t('legal.priv_h_retention') }}</h2>
            <p>{{ t('legal.priv_retention_body') }}</p>

            <h2>{{ t('legal.priv_h_rights') }}</h2>
            <p>{{ t('legal.priv_rights_intro') }}</p>
            <ul>
                <li>{{ t('legal.priv_right_access') }}</li>
                <li>{{ t('legal.priv_right_rectify') }}</li>
                <li>{{ t('legal.priv_right_erase') }}</li>
                <li>{{ t('legal.priv_right_portability') }}</li>
                <li>{{ t('legal.priv_right_object') }}</li>
                <li>{{ t('legal.priv_right_complaint') }}</li>
            </ul>
            <p><a href="mailto:privacy@focusmatrix.allocore.de" class="text-accent">privacy@focusmatrix.allocore.de</a></p>

            <h2>{{ t('legal.priv_h_cookies') }}</h2>
            <p>{{ t('legal.priv_cookies_body') }}</p>

            <h2>{{ t('legal.priv_h_security') }}</h2>
            <p>{{ t('legal.priv_security_body') }}</p>

            <p class="text-sm text-graphite-500 mt-12">{{ t('legal.last_updated') }}: {{ new Date().toISOString().slice(0,10) }}</p>
        </div>
    </MarketingLayout>
</template>
