<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import FocusLayout from '@/Layouts/FocusLayout.vue';
import { ArrowUturnLeftIcon, CheckIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    task: Object,
    candidates: Array,
});

const { t } = useI18n();

const form = useForm({
    task_id: props.task?.id,
    delegate_user_id: null,
    delegate_name_fallback: '',
    goal: '',
    decision_scope: 'consult',
    deadline: '',
    resources: '',
    inform_list: [],
    no_micromanagement: true,
});

function submit() {
    form.post(route('delegations.store'));
}

const scopes = [
    { key: 'inform', tone: 'graphite' },
    { key: 'consult', tone: 'navy' },
    { key: 'decide', tone: 'accent' },
];
</script>

<template>
    <FocusLayout>
        <template #breadcrumbs>
            <Link :href="route('delegations.index')">{{ t('nav.delegate') }}</Link> · {{ t('delegate.title') }}
        </template>
        <template #title>{{ t('delegate.title') }}</template>

        <form @submit.prevent="submit" class="max-w-3xl">
            <div class="fm-card space-y-6">
                <!-- Task context -->
                <div v-if="task" class="p-4 rounded-lg bg-navy-50 border border-navy-100">
                    <div class="text-xs uppercase tracking-wider text-navy-600">{{ t('task.title') }}</div>
                    <div class="mt-1 font-semibold text-navy-900">{{ task.title }}</div>
                    <div v-if="task.description" class="text-sm text-navy-700 mt-1">{{ task.description }}</div>
                </div>

                <!-- Goal -->
                <div>
                    <label class="fm-label">{{ t('delegate.goal') }}</label>
                    <textarea v-model="form.goal" rows="3" :placeholder="t('delegate.goal_placeholder')" class="fm-input"></textarea>
                    <div v-if="form.errors.goal" class="text-xs text-rose-600 mt-1">{{ form.errors.goal }}</div>
                </div>

                <!-- Person -->
                <div>
                    <label class="fm-label">{{ t('delegate.to') }}</label>
                    <select v-if="candidates.length" v-model="form.delegate_user_id" class="fm-input">
                        <option :value="null">{{ t('delegate.to_placeholder') }}</option>
                        <option v-for="c in candidates" :key="c.id" :value="c.id">{{ c.name }} ({{ c.email }})</option>
                    </select>
                    <input v-else v-model="form.delegate_name_fallback" type="text" :placeholder="t('delegate.to_placeholder')" class="fm-input" />
                </div>

                <!-- Deadline + Scope -->
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="fm-label">{{ t('delegate.deadline') }}</label>
                        <input v-model="form.deadline" type="date" class="fm-input" />
                    </div>
                    <div>
                        <label class="fm-label">{{ t('delegate.decision_scope') }}</label>
                        <div class="flex gap-2">
                            <button
                                v-for="s in scopes"
                                :key="s.key"
                                type="button"
                                @click="form.decision_scope = s.key"
                                :class="[
                                    'flex-1 px-3 py-2 rounded-lg border text-sm transition',
                                    form.decision_scope === s.key
                                        ? 'bg-navy-900 text-white border-navy-900'
                                        : 'bg-white text-navy-800 border-graphite-200 hover:border-navy-400'
                                ]"
                            >
                                {{ t(`delegate.scope_${s.key}`) }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Resources -->
                <div>
                    <label class="fm-label">{{ t('delegate.resources') }}</label>
                    <textarea v-model="form.resources" rows="2" :placeholder="t('delegate.resources_placeholder')" class="fm-input"></textarea>
                </div>

                <!-- No micromanagement -->
                <label class="flex items-start gap-3 cursor-pointer p-4 rounded-lg bg-accent/5 border border-accent/20">
                    <input v-model="form.no_micromanagement" type="checkbox" class="mt-1 rounded border-graphite-300 text-accent focus:ring-accent" />
                    <div>
                        <div class="font-medium text-navy-900">{{ t('delegate.no_micro') }}</div>
                        <div class="text-xs text-graphite-600 mt-1">{{ t('delegate.no_micro_desc') }}</div>
                    </div>
                </label>
            </div>

            <div class="mt-6 p-4 rounded-lg bg-navy-50 border border-navy-100 text-sm text-navy-800 flex items-start gap-2">
                <CheckIcon class="w-5 h-5 text-accent shrink-0 mt-0.5" />
                {{ t('delegate.remember') }}
            </div>

            <div class="mt-6 flex items-center justify-between">
                <Link :href="route('delegations.index')" class="fm-btn-ghost">
                    <ArrowUturnLeftIcon class="w-4 h-4" /> {{ t('delegate.cancel') }}
                </Link>
                <button type="submit" :disabled="form.processing" class="fm-btn-primary disabled:opacity-50">
                    {{ t('delegate.create') }}
                </button>
            </div>
        </form>
    </FocusLayout>
</template>
