<script setup>
import { useI18n } from 'vue-i18n';
const { t } = useI18n();
</script>

<template>
    <div class="fm-card bg-navy-900 text-white border-0">
        <div class="text-xs uppercase tracking-wider text-navy-200 mb-2">{{ t('dashboard.guiding') }}</div>
        <p class="font-display text-lg leading-snug">
            {{ t('landing.footer_principle') }}
        </p>
    </div>
</template>
