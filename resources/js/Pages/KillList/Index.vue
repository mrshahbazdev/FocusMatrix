<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import FocusLayout from '@/Layouts/FocusLayout.vue';
import { XCircleIcon, TrashIcon } from '@heroicons/vue/24/outline';

const props = defineProps({ items: Array });
const { t } = useI18n();

const showForm = ref(false);
const form = useForm({
    title: '',
    item_type: 'meeting',
    reason: '',
    was_necessary: null,
    served_clear_goal: null,
    anything_missing: null,
});

const types = ['task', 'meeting', 'report', 'process', 'other'];

function submit() {
    form.post(route('kill-list.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            showForm.value = false;
        },
    });
}

function remove(id) {
    if (!confirm('Remove?')) return;
    router.delete(route('kill-list.destroy', id), { preserveScroll: true });
}

function toggle(field, value) {
    form[field] = form[field] === value ? null : value;
}
</script>

<template>
    <FocusLayout>
        <template #breadcrumbs>FocusMatrix · {{ t('drop.kill_list') }}</template>
        <template #title>{{ t('drop.kill_list') }}</template>

        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-6">
            <p class="text-sm text-graphite-600 max-w-xl">{{ t('drop.kill_list_sub') }}</p>
            <button @click="showForm = !showForm" class="fm-btn-primary shrink-0">
                <XCircleIcon class="w-4 h-4" /> {{ t('drop.confirm') }}
            </button>
        </div>

        <div v-if="showForm" class="fm-card mb-6 space-y-4">
            <div>
                <label class="fm-label">{{ t('task.title') }}</label>
                <input v-model="form.title" type="text" class="fm-input" />
            </div>
            <div>
                <label class="fm-label">Type</label>
                <div class="flex flex-wrap gap-2">
                    <button v-for="type in types" :key="type" type="button"
                        @click="form.item_type = type"
                        :class="[
                            'px-3 py-1.5 rounded-full text-xs border capitalize',
                            form.item_type === type
                                ? 'bg-navy-900 text-white border-navy-900'
                                : 'bg-white text-navy-800 border-graphite-200 hover:border-navy-400'
                        ]"
                    >{{ type }}</button>
                </div>
            </div>

            <div class="grid md:grid-cols-3 gap-3">
                <div v-for="q in [
                    { field: 'was_necessary', label: t('drop.necessary') },
                    { field: 'served_clear_goal', label: t('drop.serves_goal') },
                    { field: 'anything_missing', label: t('drop.missing') },
                ]" :key="q.field" class="p-3 rounded-lg border border-graphite-200">
                    <div class="text-xs text-graphite-600">{{ q.label }}</div>
                    <div class="mt-2 flex gap-2">
                        <button type="button" @click="toggle(q.field, true)" :class="[
                            'px-3 py-1 text-xs rounded-md border',
                            form[q.field] === true ? 'bg-emerald-100 border-emerald-300 text-emerald-800' : 'border-graphite-200'
                        ]">Yes</button>
                        <button type="button" @click="toggle(q.field, false)" :class="[
                            'px-3 py-1 text-xs rounded-md border',
                            form[q.field] === false ? 'bg-rose-100 border-rose-300 text-rose-800' : 'border-graphite-200'
                        ]">No</button>
                    </div>
                </div>
            </div>

            <div>
                <label class="fm-label">{{ t('drop.reason') }}</label>
                <textarea v-model="form.reason" rows="2" class="fm-input"></textarea>
            </div>

            <div class="flex justify-end gap-2">
                <button type="button" @click="showForm = false" class="fm-btn-ghost">{{ t('common.cancel') }}</button>
                <button @click="submit" class="fm-btn-primary">{{ t('drop.confirm') }}</button>
            </div>
        </div>

        <div class="fm-card">
            <div v-if="items.length === 0" class="text-center py-16 text-graphite-500">
                <XCircleIcon class="w-10 h-10 mx-auto text-graphite-300" />
                <p class="mt-3 text-sm">{{ t('common.empty') }}</p>
            </div>
            <ul v-else class="divide-y divide-graphite-200">
                <li v-for="item in items" :key="item.id" class="py-4 flex items-start gap-4">
                    <div class="w-10 h-10 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                        <XCircleIcon class="w-5 h-5" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="font-medium text-navy-900">{{ item.title }}</span>
                            <span class="fm-badge bg-graphite-100 text-graphite-700 capitalize">{{ item.item_type }}</span>
                        </div>
                        <div v-if="item.reason" class="text-sm text-graphite-600 mt-1">{{ item.reason }}</div>
                        <div class="text-xs text-graphite-500 mt-1">{{ new Date(item.killed_at).toLocaleString() }}</div>
                    </div>
                    <button @click="remove(item.id)" class="p-2 text-graphite-400 hover:text-rose-600">
                        <TrashIcon class="w-4 h-4" />
                    </button>
                </li>
            </ul>
        </div>
    </FocusLayout>
</template>
