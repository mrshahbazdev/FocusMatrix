<script setup>
import { Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import FocusLayout from '@/Layouts/FocusLayout.vue';
import { UserGroupIcon, PlusIcon, TrashIcon, ClockIcon } from '@heroicons/vue/24/outline';

const props = defineProps({ delegations: Array });
const { t } = useI18n();

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

function remove(d) {
    if (!confirm('Remove delegation?')) return;
    router.delete(route('delegations.destroy', d.id), { preserveScroll: true });
}
</script>

<template>
    <FocusLayout>
        <template #breadcrumbs>FocusMatrix · {{ t('nav.delegate') }}</template>
        <template #title>{{ t('nav.delegate') }}</template>

        <div class="flex items-center justify-between mb-6">
            <p class="text-sm text-graphite-600 max-w-xl">{{ t('delegate.remember') }}</p>
            <Link :href="route('delegations.create')" class="fm-btn-primary">
                <PlusIcon class="w-4 h-4" /> {{ t('delegate.title') }}
            </Link>
        </div>

        <div class="fm-card">
            <div v-if="delegations.length === 0" class="text-center py-16 text-graphite-500">
                <UserGroupIcon class="w-10 h-10 mx-auto text-graphite-300" />
                <p class="mt-3 text-sm">{{ t('common.empty') }}</p>
            </div>
            <table v-else class="w-full text-sm">
                <thead class="text-left text-graphite-500">
                    <tr>
                        <th class="pb-3 font-medium">{{ t('task.title') }}</th>
                        <th class="pb-3 font-medium">{{ t('delegate.to') }}</th>
                        <th class="pb-3 font-medium">{{ t('delegate.deadline') }}</th>
                        <th class="pb-3 font-medium">{{ t('delegate.decision_scope') }}</th>
                        <th class="pb-3 font-medium">Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-graphite-100">
                    <tr v-for="d in delegations" :key="d.id" class="group">
                        <td class="py-3">
                            <Link :href="route('delegations.show', d.id)" class="font-medium text-navy-900 hover:underline">
                                {{ d.task?.title }}
                            </Link>
                            <div class="text-xs text-graphite-500 mt-0.5 line-clamp-1">{{ d.goal }}</div>
                        </td>
                        <td class="py-3 text-navy-800">{{ d.delegate_user?.name || d.delegate_name_fallback || '—' }}</td>
                        <td class="py-3 text-graphite-600">
                            <span class="inline-flex items-center gap-1"><ClockIcon class="w-3.5 h-3.5" />{{ d.deadline?.slice(0, 10) || '—' }}</span>
                        </td>
                        <td class="py-3 text-navy-800 capitalize">{{ d.decision_scope }}</td>
                        <td class="py-3">
                            <span :class="['fm-badge', statusTones[d.status]]">{{ d.status }}</span>
                        </td>
                        <td class="py-3 text-right">
                            <button @click="remove(d)" class="p-2 text-graphite-400 hover:text-rose-600">
                                <TrashIcon class="w-4 h-4" />
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </FocusLayout>
</template>
