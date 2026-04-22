<script setup>
import { computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import FocusLayout from '@/Layouts/FocusLayout.vue';
import {
    CreditCardIcon, CheckCircleIcon, ExclamationTriangleIcon, ArrowRightIcon, ArrowPathIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    plans: Object,
    current_plan: String,
    configured: Boolean,
    subscription: Object,
});

const { t } = useI18n();
const page = usePage();
const flash = computed(() => page.props.flash || {});

function choose(slug) {
    if (slug === 'free') return;
    if (slug === 'enterprise') {
        window.location = 'mailto:sales@focusmatrix.app?subject=Enterprise%20plan';
        return;
    }
    window.location = route('billing.checkout', slug);
}

function openPortal() {
    window.location = route('billing.portal');
}

function cancel() {
    if (!confirm(t('billing.confirm_cancel'))) return;
    router.post(route('billing.cancel'));
}

function resume() {
    router.post(route('billing.resume'));
}

const planOrder = ['free', 'pro', 'team', 'enterprise'];
</script>

<template>
    <FocusLayout>
        <template #breadcrumbs>FocusMatrix · {{ t('billing.title') }}</template>
        <template #title>
            <span class="inline-flex items-center gap-2"><CreditCardIcon class="w-6 h-6 text-accent" /> {{ t('billing.title') }}</span>
        </template>

        <div class="max-w-6xl space-y-6">
            <div v-if="!configured" class="fm-card bg-amber-50 border-amber-200">
                <div class="flex items-start gap-3">
                    <ExclamationTriangleIcon class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" />
                    <div class="text-sm text-amber-800">
                        <div class="font-semibold">{{ t('billing.not_configured_title') }}</div>
                        <div class="mt-1">{{ t('billing.not_configured_desc') }}</div>
                    </div>
                </div>
            </div>

            <div v-if="subscription" class="fm-card">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <div class="fm-section-title">{{ t('billing.current_subscription') }}</div>
                        <div class="mt-2 flex items-center gap-3">
                            <span class="fm-badge bg-accent/10 text-accent uppercase">{{ subscription.name }}</span>
                            <span class="fm-badge"
                                :class="{
                                    'bg-success/10 text-success': subscription.stripe_status === 'active',
                                    'bg-amber-100 text-amber-700': subscription.stripe_status === 'trialing',
                                    'bg-red-100 text-red-700': ['canceled', 'past_due', 'unpaid'].includes(subscription.stripe_status),
                                }">
                                {{ subscription.stripe_status }}
                            </span>
                        </div>
                        <div v-if="subscription.on_trial" class="text-sm text-graphite-600 mt-2">
                            {{ t('billing.trial_until') }}: {{ new Date(subscription.trial_ends_at).toLocaleDateString() }}
                        </div>
                        <div v-if="subscription.on_grace_period" class="text-sm text-amber-700 mt-2">
                            {{ t('billing.grace_until') }}: {{ new Date(subscription.ends_at).toLocaleDateString() }}
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button @click="openPortal" class="fm-btn-secondary">
                            <ArrowRightIcon class="w-4 h-4" /> {{ t('billing.open_portal') }}
                        </button>
                        <button v-if="!subscription.canceled" @click="cancel" class="fm-btn-secondary text-red-600">
                            {{ t('billing.cancel') }}
                        </button>
                        <button v-if="subscription.on_grace_period" @click="resume" class="fm-btn-primary">
                            <ArrowPathIcon class="w-4 h-4" /> {{ t('billing.resume') }}
                        </button>
                    </div>
                </div>
            </div>

            <div v-if="flash.success" class="fm-card bg-success/5 border-success/30 text-success text-sm">
                <CheckCircleIcon class="w-5 h-5 inline-block mr-2" /> {{ flash.success }}
            </div>
            <div v-if="flash.error" class="fm-card bg-red-50 border-red-200 text-red-700 text-sm">
                <ExclamationTriangleIcon class="w-5 h-5 inline-block mr-2" /> {{ flash.error }}
            </div>

            <!-- Plan tiles -->
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                <div v-for="slug in planOrder" :key="slug"
                    class="fm-card relative flex flex-col"
                    :class="{
                        'ring-2 ring-accent border-accent bg-accent/5': slug === current_plan,
                        'border-graphite-200': slug !== current_plan,
                    }">
                    <div v-if="slug === current_plan" class="absolute top-3 right-3 fm-badge bg-accent text-white text-xs">
                        {{ t('billing.your_plan') }}
                    </div>
                    <div class="font-display text-xl font-semibold text-navy-900">{{ plans[slug].name }}</div>
                    <div class="mt-2">
                        <template v-if="plans[slug].price_eur === 0">
                            <span class="text-3xl font-bold">€0</span>
                            <span class="text-sm text-graphite-500"> / {{ t('billing.forever') }}</span>
                        </template>
                        <template v-else-if="plans[slug].price_eur === null">
                            <span class="text-2xl font-semibold text-navy-800">{{ t('billing.custom') }}</span>
                        </template>
                        <template v-else>
                            <span class="text-3xl font-bold">€{{ plans[slug].price_eur }}</span>
                            <span class="text-sm text-graphite-500"> / {{ t('billing.per_user_month') }}</span>
                        </template>
                    </div>
                    <ul class="text-sm text-graphite-700 space-y-2 mt-4 flex-1">
                        <li v-for="f in plans[slug].features" :key="f" class="flex items-start gap-2">
                            <CheckCircleIcon class="w-4 h-4 text-accent shrink-0 mt-0.5" />
                            <span>{{ t(`billing.feat.${f}`) }}</span>
                        </li>
                    </ul>
                    <div class="mt-4 pt-4 border-t border-graphite-100 text-xs text-graphite-500">
                        {{ t('billing.team_members') }}:
                        {{ plans[slug].max_team_members ?? t('billing.unlimited') }}
                        <br />
                        {{ t('billing.ai_calls') }}:
                        {{ plans[slug].ai_calls_per_month ?? t('billing.unlimited') }}
                    </div>
                    <button @click="choose(slug)"
                        :disabled="slug === current_plan"
                        class="mt-4 w-full"
                        :class="slug === current_plan ? 'fm-btn-secondary opacity-60 cursor-default' : 'fm-btn-primary'">
                        {{ slug === current_plan ? t('billing.current') :
                           slug === 'free' ? t('billing.downgrade_contact') :
                           slug === 'enterprise' ? t('billing.contact_sales') :
                           t('billing.start_trial') }}
                    </button>
                </div>
            </div>

            <div class="text-xs text-graphite-500 text-center">
                {{ t('billing.secure_stripe') }} · {{ t('billing.vat_note') }}
            </div>
        </div>
    </FocusLayout>
</template>
