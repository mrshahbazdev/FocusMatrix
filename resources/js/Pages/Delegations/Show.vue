<script setup>
import { Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import FocusLayout from '@/Layouts/FocusLayout.vue';

const props = defineProps({ delegation: Object });
const { t } = useI18n();

function updateStatus(status) {
    router.put(route('delegations.update', props.delegation.id), { status });
}
</script>

<template>
    <FocusLayout>
        <template #breadcrumbs>
            <Link :href="route('delegations.index')">{{ t('nav.delegate') }}</Link> · {{ delegation.task?.title }}
        </template>
        <template #title>{{ delegation.task?.title }}</template>

        <div class="max-w-3xl grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="md:col-span-2 fm-card space-y-4">
                <div>
                    <div class="fm-section-title">{{ t('delegate.goal') }}</div>
                    <p class="mt-1 text-navy-900">{{ delegation.goal }}</p>
                </div>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <div class="fm-section-title">{{ t('delegate.to') }}</div>
                        <div class="mt-1 text-navy-800">{{ delegation.delegate_user?.name || delegation.delegate_name_fallback || '—' }}</div>
                    </div>
                    <div>
                        <div class="fm-section-title">{{ t('delegate.deadline') }}</div>
                        <div class="mt-1 text-navy-800">{{ delegation.deadline?.slice(0, 10) || '—' }}</div>
                    </div>
                    <div>
                        <div class="fm-section-title">{{ t('delegate.decision_scope') }}</div>
                        <div class="mt-1 text-navy-800 capitalize">{{ delegation.decision_scope }}</div>
                    </div>
                    <div>
                        <div class="fm-section-title">Status</div>
                        <div class="mt-1 text-navy-800 capitalize">{{ delegation.status }}</div>
                    </div>
                </div>
                <div v-if="delegation.resources">
                    <div class="fm-section-title">{{ t('delegate.resources') }}</div>
                    <p class="mt-1 text-navy-800">{{ delegation.resources }}</p>
                </div>
            </div>
            <div class="fm-card space-y-3">
                <div class="fm-section-title">Actions</div>
                <button @click="updateStatus('in_progress')" class="w-full fm-btn-secondary">Mark in progress</button>
                <button @click="updateStatus('done')" class="w-full fm-btn-primary">Mark done</button>
                <button @click="updateStatus('cancelled')" class="w-full fm-btn-ghost">Cancel</button>
            </div>
        </div>
    </FocusLayout>
</template>
