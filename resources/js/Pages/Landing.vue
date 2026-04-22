<script setup>
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import MarketingLayout from '@/Layouts/MarketingLayout.vue';
import {
    ChartBarSquareIcon,
    InboxIcon,
    CheckBadgeIcon,
    UserGroupIcon,
    XCircleIcon,
    CalendarDaysIcon,
    BuildingOffice2Icon,
    SparklesIcon,
    ArrowRightIcon,
    BoltIcon,
} from '@heroicons/vue/24/outline';

const { t } = useI18n();

const featureItems = [
    { key: 'dashboard', icon: ChartBarSquareIcon },
    { key: 'inbox', icon: InboxIcon },
    { key: 'keep', icon: CheckBadgeIcon },
    { key: 'delegate', icon: UserGroupIcon },
    { key: 'drop', icon: XCircleIcon },
    { key: 'selfcheck', icon: CalendarDaysIcon },
    { key: 'orgcheck', icon: BuildingOffice2Icon },
    { key: 'ai', icon: SparklesIcon },
];

const plans = [
    { key: 'free', price: '0', highlight: false,
        features: ['1 user', 'Task inbox & triage', 'Weekly self-check'] },
    { key: 'pro', price: '12', highlight: true,
        features: ['Unlimited tasks', 'Delegation cockpit', 'AI Co-Pilot', 'Calendar integration'] },
    { key: 'team', price: '29', highlight: false,
        features: ['Everything in Pro', 'Organisation check', 'Team analytics', 'Priority support'] },
];
</script>

<template>
    <MarketingLayout>
        <!-- Hero -->
        <section class="relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-navy-900 via-navy-800 to-navy-950"></div>
            <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(circle at 20% 30%, rgba(47,107,255,0.4), transparent 60%), radial-gradient(circle at 80% 70%, rgba(100,109,125,0.3), transparent 60%);"></div>
            <div class="relative max-w-7xl mx-auto px-6 py-24 md:py-32 grid md:grid-cols-2 gap-12 items-center">
                <div class="text-white">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-xs uppercase tracking-wider mb-6">
                        <BoltIcon class="w-4 h-4 text-accent" />
                        {{ t('brand.tagline') }}
                    </div>
                    <h1 class="font-display text-4xl md:text-6xl font-semibold leading-tight tracking-tight text-white">
                        {{ t('landing.hero_title_1') }}<br />
                        <span class="text-accent">{{ t('landing.hero_title_2') }}</span>
                    </h1>
                    <p class="mt-6 text-lg text-navy-100 max-w-xl">{{ t('landing.hero_sub') }}</p>
                    <div class="mt-8 flex flex-wrap items-center gap-4">
                        <Link :href="route('register')" class="fm-btn bg-accent hover:bg-accent-600 text-white px-6 py-3 rounded-lg font-medium">
                            {{ t('landing.cta_primary') }}
                            <ArrowRightIcon class="w-4 h-4" />
                        </Link>
                        <a href="#principle" class="fm-btn text-white border border-white/30 hover:bg-white/10 px-6 py-3 rounded-lg">
                            {{ t('landing.cta_secondary') }}
                        </a>
                    </div>
                </div>

                <div class="relative">
                    <div class="rounded-2xl bg-white/5 backdrop-blur border border-white/10 p-6 shadow-lifted">
                        <div class="text-xs uppercase tracking-wider text-navy-200 mb-3">{{ t('landing.principle_q') }}</div>
                        <div class="grid grid-cols-3 gap-3">
                            <div class="rounded-xl bg-emerald-500/10 border border-emerald-400/30 p-4">
                                <div class="text-emerald-300 font-semibold text-sm">{{ t('landing.matrix_keep') }}</div>
                                <div class="text-xs text-emerald-100/80 mt-1">{{ t('landing.matrix_keep_sub') }}</div>
                            </div>
                            <div class="rounded-xl bg-amber-500/10 border border-amber-400/30 p-4">
                                <div class="text-amber-300 font-semibold text-sm">{{ t('landing.matrix_delegate') }}</div>
                                <div class="text-xs text-amber-100/80 mt-1">{{ t('landing.matrix_delegate_sub') }}</div>
                            </div>
                            <div class="rounded-xl bg-rose-500/10 border border-rose-400/30 p-4">
                                <div class="text-rose-300 font-semibold text-sm">{{ t('landing.matrix_drop') }}</div>
                                <div class="text-xs text-rose-100/80 mt-1">{{ t('landing.matrix_drop_sub') }}</div>
                            </div>
                        </div>
                        <div class="mt-6 text-sm text-white/90 leading-relaxed border-t border-white/10 pt-4">
                            <span class="text-navy-200">01 →</span> Capture task<br />
                            <span class="text-navy-200">02 →</span> Ask: can only I do this?<br />
                            <span class="text-navy-200">03 →</span> Keep, delegate or drop — decisively.
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Principle -->
        <section id="principle" class="py-24 bg-white">
            <div class="max-w-4xl mx-auto px-6 text-center">
                <div class="fm-section-title text-accent">{{ t('landing.principle_kicker') }}</div>
                <h2 class="mt-3 font-display text-3xl md:text-5xl font-semibold">{{ t('landing.principle_title') }}</h2>
                <div class="mt-10 rounded-2xl bg-gradient-to-br from-navy-900 to-navy-700 p-12 text-white shadow-lifted">
                    <div class="text-4xl md:text-5xl font-display leading-tight">
                        "{{ t('landing.principle_q') }}"
                    </div>
                    <p class="mt-6 text-navy-100 max-w-2xl mx-auto">{{ t('landing.principle_sub') }}</p>
                </div>
            </div>
        </section>

        <!-- Features -->
        <section id="features" class="py-24 bg-graphite-50">
            <div class="max-w-7xl mx-auto px-6">
                <div class="text-center mb-14">
                    <div class="fm-section-title text-accent">{{ t('landing.features_kicker') }}</div>
                    <h2 class="mt-3 font-display text-3xl md:text-4xl font-semibold">{{ t('landing.features_title') }}</h2>
                </div>
                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div v-for="f in featureItems" :key="f.key" class="fm-card-hover">
                        <component :is="f.icon" class="w-8 h-8 text-accent" />
                        <h3 class="mt-4 font-display text-lg font-semibold">{{ t(`landing.feature_${f.key}_t`) }}</h3>
                        <p class="mt-2 text-sm text-graphite-600">{{ t(`landing.feature_${f.key}_d`) }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Pricing -->
        <section id="pricing" class="py-24 bg-white">
            <div class="max-w-6xl mx-auto px-6">
                <div class="text-center mb-14">
                    <div class="fm-section-title text-accent">{{ t('landing.pricing_kicker') }}</div>
                    <h2 class="mt-3 font-display text-3xl md:text-4xl font-semibold">{{ t('landing.pricing_title') }}</h2>
                </div>
                <div class="grid md:grid-cols-3 gap-6">
                    <div v-for="plan in plans" :key="plan.key"
                        :class="[
                            'rounded-2xl p-8 border',
                            plan.highlight
                                ? 'bg-navy-900 text-white border-navy-900 shadow-lifted ring-2 ring-accent'
                                : 'bg-white text-navy-900 border-graphite-200'
                        ]">
                        <div :class="['text-sm font-semibold uppercase tracking-wider', plan.highlight ? 'text-accent' : 'text-graphite-500']">
                            {{ t(`landing.pricing_${plan.key}`) }}
                        </div>
                        <div class="mt-3 flex items-baseline gap-1">
                            <span class="text-4xl font-display font-semibold">€{{ plan.price }}</span>
                            <span :class="['text-sm', plan.highlight ? 'text-navy-200' : 'text-graphite-500']">{{ t('landing.pricing_month') }}</span>
                        </div>
                        <p :class="['mt-2 text-sm', plan.highlight ? 'text-navy-200' : 'text-graphite-600']">
                            {{ t(`landing.pricing_${plan.key}_d`) }}
                        </p>
                        <ul class="mt-6 space-y-2 text-sm">
                            <li v-for="feat in plan.features" :key="feat" class="flex items-start gap-2">
                                <CheckBadgeIcon :class="['w-5 h-5 mt-0.5 shrink-0', plan.highlight ? 'text-accent' : 'text-navy-700']" />
                                <span :class="plan.highlight ? 'text-navy-100' : 'text-navy-800'">{{ feat }}</span>
                            </li>
                        </ul>
                        <Link :href="route('register')"
                            :class="[
                                'mt-8 w-full fm-btn',
                                plan.highlight ? 'bg-accent hover:bg-accent-600 text-white' : 'fm-btn-primary',
                            ]">
                            {{ t('landing.pricing_cta') }}
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA -->
        <section class="py-24 bg-graphite-50">
            <div class="max-w-4xl mx-auto px-6 text-center">
                <h2 class="font-display text-3xl md:text-4xl font-semibold">{{ t('landing.footer_principle') }}</h2>
                <Link :href="route('register')" class="mt-8 inline-flex fm-btn-primary px-6 py-3">
                    {{ t('landing.cta_primary') }} <ArrowRightIcon class="w-4 h-4" />
                </Link>
            </div>
        </section>
    </MarketingLayout>
</template>
