<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import FocusLayout from '@/Layouts/FocusLayout.vue';
import {
    InboxArrowDownIcon, CheckIcon, XMarkIcon, ClockIcon, UserIcon,
    ChatBubbleLeftIcon,
} from '@heroicons/vue/24/outline';

defineProps({ delegations: Array });
const { t } = useI18n();

const declineModal = ref(null);
const declineReason = ref('');

function accept(d) {
    if (!confirm(t('assigned.confirm_accept', { title: d.task?.title }))) return;
    router.post(route('delegations.accept', d.id), {}, { preserveScroll: true });
}

function openDecline(d) {
    declineModal.value = d;
    declineReason.value = '';
}

function submitDecline() {
    if (!declineModal.value) return;
    router.post(route('delegations.decline', declineModal.value.id), {
        reason: declineReason.value,
    }, {
        preserveScroll: true,
        onSuccess: () => { declineModal.value = null; },
    });
}

const statusTones = {
    open: 'bg-navy-100 text-navy-800',
    invited: 'bg-accent/10 text-accent',
    accepted: 'bg-emerald-100 text-emerald-800',
    declined: 'bg-rose-100 text-rose-800',
    in_progress: 'bg-amber-100 text-amber-800',
    done: 'bg-emerald-100 text-emerald-800',
    overdue: 'bg-rose-100 text-rose-800',
    cancelled: 'bg-graphite-200 text-graphite-700',
};

const scopeTones = {
    inform: 'bg-graphite-100 text-graphite-700',
    consult: 'bg-amber-100 text-amber-800',
    decide: 'bg-emerald-100 text-emerald-800',
};
</script>

<template>
    <FocusLayout>
        <template #breadcrumbs>FocusMatrix · {{ t('nav.assigned') }}</template>
        <template #title>{{ t('nav.assigned') }}</template>

        <p class="text-sm text-graphite-600 max-w-2xl mb-6">{{ t('assigned.intro') }}</p>

        <div v-if="delegations.length === 0" class="fm-card text-center py-16">
            <InboxArrowDownIcon class="w-10 h-10 mx-auto text-graphite-300" />
            <p class="mt-3 text-sm text-graphite-500">{{ t('assigned.empty') }}</p>
        </div>

        <div v-else class="space-y-4">
            <div v-for="d in delegations" :key="d.id" class="fm-card !p-0 overflow-hidden">
                <div class="p-5 border-b border-graphite-100">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 class="font-display text-lg font-semibold text-navy-900">
                                    {{ d.task?.title }}
                                </h3>
                                <span :class="['fm-badge text-xs', statusTones[d.status]]">{{ t('assigned.status.' + d.status) }}</span>
                                <span :class="['fm-badge text-xs', scopeTones[d.decision_scope]]">{{ t('assigned.scope.' + d.decision_scope) }}</span>
                            </div>
                            <div class="mt-1 text-xs text-graphite-500 flex items-center gap-3 flex-wrap">
                                <span class="inline-flex items-center gap-1">
                                    <UserIcon class="w-3.5 h-3.5" />
                                    {{ d.delegator?.name }} &lt;{{ d.delegator?.email }}&gt;
                                </span>
                                <span v-if="d.deadline" class="inline-flex items-center gap-1">
                                    <ClockIcon class="w-3.5 h-3.5" />
                                    {{ d.deadline?.slice(0, 10) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-5 grid md:grid-cols-2 gap-5 text-sm">
                    <div>
                        <div class="fm-label">{{ t('assigned.goal') }}</div>
                        <p class="text-graphite-800 whitespace-pre-line">{{ d.goal || '—' }}</p>
                    </div>
                    <div>
                        <div class="fm-label">{{ t('assigned.resources') }}</div>
                        <p class="text-graphite-800 whitespace-pre-line">{{ d.resources || '—' }}</p>
                    </div>
                </div>

                <div v-if="d.status === 'declined' && d.decline_reason" class="px-5 pb-5 -mt-2">
                    <div class="fm-label">{{ t('assigned.decline_reason') }}</div>
                    <p class="text-rose-700 text-sm">{{ d.decline_reason }}</p>
                </div>

                <div class="flex items-center justify-between gap-3 px-5 py-3 bg-graphite-50 border-t border-graphite-100">
                    <Link :href="route('delegations.show', d.id)" class="text-xs text-navy-700 hover:underline inline-flex items-center gap-1">
                        <ChatBubbleLeftIcon class="w-3.5 h-3.5" /> {{ t('assigned.open') }}
                    </Link>
                    <div class="flex items-center gap-2">
                        <template v-if="d.status === 'invited'">
                            <button @click="openDecline(d)" class="fm-btn-ghost text-rose-600">
                                <XMarkIcon class="w-4 h-4" /> {{ t('assigned.decline') }}
                            </button>
                            <button @click="accept(d)" class="fm-btn-primary">
                                <CheckIcon class="w-4 h-4" /> {{ t('assigned.accept') }}
                            </button>
                        </template>
                        <template v-else-if="d.status === 'accepted' || d.status === 'in_progress'">
                            <span class="text-xs text-graphite-600">{{ t('assigned.already_accepted') }}</span>
                        </template>
                        <template v-else-if="d.status === 'declined'">
                            <span class="text-xs text-graphite-600">{{ t('assigned.already_declined') }}</span>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Decline reason modal -->
        <div v-if="declineModal" class="fixed inset-0 z-50 bg-navy-900/50 flex items-center justify-center p-4" @click.self="declineModal = null">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md">
                <div class="p-5 border-b border-graphite-200">
                    <h2 class="font-display text-lg font-semibold text-navy-900">
                        {{ t('assigned.decline_modal_title') }}
                    </h2>
                    <p class="text-xs text-graphite-500 mt-1">
                        "{{ declineModal.task?.title }}"
                    </p>
                </div>
                <div class="p-5 space-y-3">
                    <div>
                        <label class="fm-label">{{ t('assigned.decline_reason_label') }}</label>
                        <textarea v-model="declineReason" rows="4" class="fm-input w-full"
                            :placeholder="t('assigned.decline_reason_ph')"></textarea>
                    </div>
                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button @click="declineModal = null" class="fm-btn-ghost">{{ t('assigned.cancel') }}</button>
                        <button @click="submitDecline" class="fm-btn-primary bg-rose-600 hover:bg-rose-700 border-rose-700">
                            {{ t('assigned.confirm_decline') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </FocusLayout>
</template>
