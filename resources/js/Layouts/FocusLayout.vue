<script setup>
import { computed } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import BrandMark from '@/Components/BrandMark.vue';
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue';
import CookieBanner from '@/Components/CookieBanner.vue';
import {
    HomeIcon,
    InboxIcon,
    InboxArrowDownIcon,
    CheckBadgeIcon,
    UserGroupIcon,
    UsersIcon,
    XCircleIcon,
    CalendarDaysIcon,
    BuildingOffice2Icon,
    Squares2X2Icon,
    SparklesIcon,
    ChartBarIcon,
    CreditCardIcon,
    Cog6ToothIcon,
    ArrowLeftOnRectangleIcon,
} from '@heroicons/vue/24/outline';

const { t } = useI18n();
const page = usePage();

const user = computed(() => page.props.auth?.user);
const flash = computed(() => page.props.flash || {});
const assignedPending = computed(() => page.props.assigned_pending_count || 0);
const currentTeam = computed(() => user.value?.current_team);
const teamHref = computed(() => currentTeam.value
    ? route('teams.show', currentTeam.value.id)
    : route('teams.create'));
const teamLabel = computed(() => currentTeam.value
    ? currentTeam.value.name
    : t('nav.team_create'));

const nav = computed(() => [
    { label: t('nav.dashboard'), href: route('dashboard'), icon: HomeIcon, active: route().current('dashboard') },
    { label: t('nav.inbox'), href: route('tasks.index', { status: 'inbox' }), icon: InboxIcon, active: route().current('tasks.index') },
    {
        label: t('nav.assigned'),
        href: route('delegations.assigned'),
        icon: InboxArrowDownIcon,
        active: route().current('delegations.assigned'),
        badge: assignedPending.value,
    },
    { label: t('nav.delegate'), href: route('delegations.index'), icon: UserGroupIcon, active: route().current('delegations.index') || route().current('delegations.create') || route().current('delegations.show') || route().current('delegations.store') },
    { label: t('drop.kill_list'), href: route('kill-list.index'), icon: XCircleIcon, active: route().current('kill-list.*') },
    { label: t('nav.calendar'), href: route('calendar.index'), icon: CalendarDaysIcon, active: route().current('calendar.*') },
    { label: t('nav.selfcheck'), href: route('self-check.index'), icon: CheckBadgeIcon, active: route().current('self-check.*') },
    { label: t('nav.orgcheck'), href: route('org-check.index'), icon: BuildingOffice2Icon, active: route().current('org-check.*') },
    {
        label: teamLabel.value,
        href: teamHref.value,
        icon: UsersIcon,
        active: route().current('teams.*') || route().current('team-members.*') || route().current('current-team.*'),
        sub: currentTeam.value ? t('nav.team_manage') : null,
    },
    { label: t('nav.integrations'), href: route('integrations.index'), icon: Squares2X2Icon, active: route().current('integrations.*') },
    { label: t('nav.ai'), href: route('ai.index'), icon: SparklesIcon, active: route().current('ai.*') },
    { label: t('nav.analytics'), href: route('analytics.index'), icon: ChartBarIcon, active: route().current('analytics.*') },
    { label: t('nav.billing'), href: route('billing.index'), icon: CreditCardIcon, active: route().current('billing.*') },
]);

function logout() {
    router.post(route('logout'));
}
</script>

<template>
    <div class="min-h-screen flex bg-graphite-50">
        <!-- Sidebar -->
        <aside class="w-64 shrink-0 bg-navy-900 text-navy-100 flex flex-col">
            <div class="h-16 px-5 flex items-center border-b border-navy-800">
                <Link href="/" class="text-white">
                    <span class="inline-flex items-center gap-2 font-display text-lg font-semibold">
                        <span class="grid grid-cols-2 grid-rows-2 gap-0.5 w-6 h-6">
                            <span class="bg-white/90"></span>
                            <span class="bg-accent"></span>
                            <span class="bg-navy-400"></span>
                            <span class="bg-white/90"></span>
                        </span>
                        Focus<span class="text-accent">Matrix</span>
                    </span>
                </Link>
            </div>
            <nav class="flex-1 p-3 space-y-1">
                <Link
                    v-for="item in nav"
                    :key="item.label"
                    :href="item.href"
                    :class="[
                        'flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition',
                        item.active
                            ? 'bg-navy-800 text-white shadow-inner'
                            : 'text-navy-200 hover:bg-navy-800/60 hover:text-white',
                    ]"
                >
                    <component :is="item.icon" class="w-5 h-5" />
                    <span class="flex-1">{{ item.label }}</span>
                    <span v-if="item.badge && item.badge > 0" class="inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 rounded-full bg-accent text-white text-[10px] font-semibold">
                        {{ item.badge }}
                    </span>
                </Link>
            </nav>
            <div class="p-3 border-t border-navy-800 space-y-1">
                <Link :href="route('profile.show')" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-navy-200 hover:bg-navy-800/60 hover:text-white">
                    <Cog6ToothIcon class="w-5 h-5" />
                    <span>{{ t('nav.settings') }}</span>
                </Link>
                <button @click="logout" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-navy-200 hover:bg-navy-800/60 hover:text-white">
                    <ArrowLeftOnRectangleIcon class="w-5 h-5" />
                    <span>{{ t('common.logout') }}</span>
                </button>
            </div>
        </aside>

        <!-- Main -->
        <div class="flex-1 flex flex-col min-w-0">
            <header class="h-16 bg-white border-b border-graphite-200 flex items-center justify-between px-8">
                <div>
                    <h1 class="text-sm text-graphite-500">
                        <slot name="breadcrumbs">{{ t('brand.name') }}</slot>
                    </h1>
                    <div class="text-lg font-display font-semibold text-navy-900 -mt-0.5">
                        <slot name="title" />
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <LanguageSwitcher />
                    <div class="hidden sm:flex items-center gap-2 text-sm">
                        <div class="w-8 h-8 rounded-full bg-navy-800 text-white font-semibold flex items-center justify-center">
                            {{ user?.name?.[0] }}
                        </div>
                        <div class="leading-tight">
                            <div class="text-navy-900 font-medium">{{ user?.name }}</div>
                            <div class="text-graphite-500 text-xs">{{ user?.current_team?.name }}</div>
                        </div>
                    </div>
                </div>
            </header>

            <div v-if="flash.success" class="mx-8 mt-4 px-4 py-2 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm">
                {{ flash.success }}
            </div>
            <div v-if="flash.error" class="mx-8 mt-4 px-4 py-2 rounded-lg bg-rose-50 border border-rose-200 text-rose-800 text-sm">
                {{ flash.error }}
            </div>

            <main class="flex-1 overflow-y-auto p-8">
                <slot />
            </main>
        </div>

        <CookieBanner />
    </div>
</template>
