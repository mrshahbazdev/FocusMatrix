<script setup>
import { ref } from 'vue';
import { Link, useForm, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import FocusLayout from '@/Layouts/FocusLayout.vue';
import FocusBlockModal from '@/Components/FocusBlockModal.vue';
import {
    InboxIcon, CheckBadgeIcon, UserGroupIcon, XCircleIcon, CheckIcon, PlusIcon, TrashIcon, CalendarDaysIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    tasks: Array,
    status: String,
    counts: Object,
});

const { t } = useI18n();

const form = useForm({ title: '', description: '' });

const tabs = [
    { key: 'inbox', icon: InboxIcon },
    { key: 'keep', icon: CheckBadgeIcon },
    { key: 'delegate', icon: UserGroupIcon },
    { key: 'drop', icon: XCircleIcon },
    { key: 'done', icon: CheckIcon },
];

function submit() {
    if (!form.title.trim()) return;
    form.post(route('tasks.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
}

function markDone(task) {
    router.put(route('tasks.update', task.id), { status: 'done' }, { preserveScroll: true });
}

function removeTask(task) {
    if (!confirm('Delete task?')) return;
    router.delete(route('tasks.destroy', task.id), { preserveScroll: true });
}

const focusTask = ref(null);
function openFocusModal(task) { focusTask.value = task; }
function closeFocusModal() { focusTask.value = null; }
</script>

<template>
    <FocusLayout>
        <template #breadcrumbs>FocusMatrix · {{ t('nav.inbox') }}</template>
        <template #title>{{ t(`task.status_${status}`) }}</template>

        <div class="flex flex-wrap items-center gap-2 mb-6">
            <Link
                v-for="tab in tabs"
                :key="tab.key"
                :href="route('tasks.index', { status: tab.key })"
                :class="[
                    'inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm border transition',
                    status === tab.key
                        ? 'bg-navy-900 text-white border-navy-900'
                        : 'bg-white border-graphite-200 text-navy-800 hover:border-navy-400'
                ]"
            >
                <component :is="tab.icon" class="w-4 h-4" />
                {{ t(`task.status_${tab.key}`) }}
                <span class="text-xs opacity-75">{{ counts?.[tab.key] ?? 0 }}</span>
            </Link>
        </div>

        <!-- Quick capture -->
        <div v-if="status === 'inbox'" class="fm-card mb-6">
            <form @submit.prevent="submit" class="flex flex-col md:flex-row gap-3">
                <input v-model="form.title" type="text" :placeholder="t('dashboard.quick_placeholder')" class="fm-input flex-1" />
                <input v-model="form.description" type="text" :placeholder="t('task.description')" class="fm-input flex-1" />
                <button type="submit" class="fm-btn-primary"><PlusIcon class="w-4 h-4" /> {{ t('dashboard.add_task') }}</button>
            </form>
        </div>

        <div class="fm-card">
            <div v-if="tasks.length === 0" class="text-center py-16 text-graphite-500">
                <InboxIcon class="w-10 h-10 mx-auto text-graphite-300" />
                <p class="mt-3 text-sm">{{ t('common.empty') }}</p>
            </div>
            <ul v-else class="divide-y divide-graphite-200">
                <li v-for="task in tasks" :key="task.id" class="py-4 flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-4">
                    <div class="flex-1 min-w-0">
                        <div class="font-medium text-navy-900">{{ task.title }}</div>
                        <div v-if="task.description" class="text-sm text-graphite-600 mt-1">{{ task.description }}</div>
                        <div class="flex flex-wrap items-center gap-2 mt-2 text-xs text-graphite-500">
                            <span>{{ t('task.created') }} {{ new Date(task.created_at).toLocaleDateString() }}</span>
                            <span v-if="task.only_you_category" class="fm-badge bg-accent/10 text-accent">
                                {{ task.only_you_category.replace('_', ' ') }}
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <Link v-if="task.status === 'inbox'" :href="route('tasks.triage', task.id)" class="fm-btn-primary !py-1.5 text-xs">
                            Triage →
                        </Link>
                        <button v-if="task.status === 'keep'" @click="openFocusModal(task)" class="fm-btn-secondary !py-1.5 text-xs">
                            <CalendarDaysIcon class="w-4 h-4" /> {{ t('calendar.block_focus') }}
                        </button>
                        <button v-if="task.status !== 'done'" @click="markDone(task)" class="fm-btn-secondary !py-1.5 text-xs">
                            <CheckIcon class="w-4 h-4" /> {{ t('task.mark_done') }}
                        </button>
                        <button @click="removeTask(task)" class="p-2 text-graphite-500 hover:text-rose-600">
                            <TrashIcon class="w-4 h-4" />
                        </button>
                    </div>
                </li>
            </ul>
        </div>

        <FocusBlockModal :open="!!focusTask" :task="focusTask" @close="closeFocusModal" />
    </FocusLayout>
</template>
