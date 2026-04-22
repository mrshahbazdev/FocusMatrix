<script setup>
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import FocusLayout from '@/Layouts/FocusLayout.vue';

const props = defineProps({ task: Object });
const { t } = useI18n();
</script>

<template>
    <FocusLayout>
        <template #breadcrumbs>
            <Link :href="route('tasks.index', { status: task.status })">{{ t('nav.inbox') }}</Link> · {{ task.title }}
        </template>
        <template #title>{{ task.title }}</template>

        <div class="max-w-3xl fm-card">
            <div class="fm-section-title">{{ t('task.description') }}</div>
            <p class="mt-2 text-navy-800">{{ task.description || '—' }}</p>
            <div class="mt-6 grid grid-cols-2 gap-4 text-sm">
                <div>
                    <div class="fm-section-title">Status</div>
                    <div class="mt-1">{{ t(`task.status_${task.status}`) }}</div>
                </div>
                <div>
                    <div class="fm-section-title">{{ t('task.category') }}</div>
                    <div class="mt-1">{{ task.only_you_category || '—' }}</div>
                </div>
            </div>
        </div>
    </FocusLayout>
</template>
