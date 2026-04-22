<script setup>
import { ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import FocusLayout from '@/Layouts/FocusLayout.vue';
import {
    FlagIcon, ShieldCheckIcon, UsersIcon, SparklesIcon, CheckIcon, XMarkIcon, ArrowUturnLeftIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({ task: Object });
const { t } = useI18n();

const answer = ref(null);
const category = ref('key_decisions');

const form = useForm({
    answer: null,
    only_you_category: 'key_decisions',
});

const categories = [
    { key: 'strategy', icon: FlagIcon },
    { key: 'key_decisions', icon: ShieldCheckIcon },
    { key: 'key_people', icon: UsersIcon },
    { key: 'responsibility', icon: SparklesIcon },
];

function submit(choice) {
    form.answer = choice;
    form.only_you_category = category.value;
    form.post(route('tasks.triage.decide', props.task.id));
}
</script>

<template>
    <FocusLayout>
        <template #breadcrumbs>
            <Link :href="route('tasks.index', { status: 'inbox' })" class="hover:text-navy-900">{{ t('nav.inbox') }}</Link> · Triage
        </template>
        <template #title>{{ t('task.the_question') }}</template>

        <div class="max-w-3xl mx-auto">
            <div class="fm-card bg-gradient-to-br from-navy-900 to-navy-700 text-white border-0">
                <div class="text-xs uppercase tracking-wider text-navy-200">{{ t('task.title') }}</div>
                <h2 class="mt-2 font-display text-2xl font-semibold">{{ task.title }}</h2>
                <p v-if="task.description" class="mt-3 text-navy-100 text-sm">{{ task.description }}</p>
            </div>

            <div class="mt-8">
                <p class="text-center text-graphite-600 max-w-xl mx-auto">{{ t('task.question_sub') }}</p>
            </div>

            <div class="mt-6 grid md:grid-cols-3 gap-4">
                <button
                    @click="answer = 'yes'"
                    :class="[
                        'fm-card-hover text-left transition',
                        answer === 'yes' ? 'ring-2 ring-emerald-500 bg-emerald-50' : ''
                    ]"
                >
                    <CheckIcon class="w-7 h-7 text-emerald-600" />
                    <div class="font-display text-lg font-semibold mt-3 text-navy-900">{{ t('task.answer_yes') }}</div>
                    <p class="text-xs text-graphite-600 mt-1">{{ t('task.yes_hint') }}</p>
                </button>
                <button
                    @click="answer = 'no'"
                    :class="[
                        'fm-card-hover text-left transition',
                        answer === 'no' ? 'ring-2 ring-amber-500 bg-amber-50' : ''
                    ]"
                >
                    <XMarkIcon class="w-7 h-7 text-amber-600" />
                    <div class="font-display text-lg font-semibold mt-3 text-navy-900">{{ t('task.answer_no') }}</div>
                    <p class="text-xs text-graphite-600 mt-1">{{ t('task.no_hint') }}</p>
                </button>
                <button
                    @click="answer = 'maybe'"
                    :class="[
                        'fm-card-hover text-left transition',
                        answer === 'maybe' ? 'ring-2 ring-accent bg-accent/5' : ''
                    ]"
                >
                    <SparklesIcon class="w-7 h-7 text-accent" />
                    <div class="font-display text-lg font-semibold mt-3 text-navy-900">{{ t('task.answer_maybe') }}</div>
                    <p class="text-xs text-graphite-600 mt-1">{{ t('task.maybe_hint') }}</p>
                </button>
            </div>

            <div v-if="answer === 'yes'" class="mt-8 fm-card">
                <div class="fm-section-title mb-3">{{ t('dashboard.only_you_categories') }}</div>
                <div class="grid grid-cols-2 gap-3">
                    <button
                        v-for="c in categories"
                        :key="c.key"
                        @click="category = c.key"
                        :class="[
                            'flex items-center gap-3 p-3 rounded-lg border text-left transition',
                            category === c.key ? 'border-accent bg-accent/5' : 'border-graphite-200 hover:border-accent/50'
                        ]"
                    >
                        <component :is="c.icon" class="w-5 h-5 text-accent" />
                        <span class="text-sm font-medium text-navy-800">{{ t(`dashboard.cat_${c.key === 'key_decisions' ? 'decisions' : c.key === 'key_people' ? 'people' : c.key}`) }}</span>
                    </button>
                </div>
            </div>

            <div class="mt-8 flex items-center justify-between">
                <Link :href="route('tasks.index', { status: 'inbox' })" class="fm-btn-ghost">
                    <ArrowUturnLeftIcon class="w-4 h-4" /> {{ t('common.back') }}
                </Link>
                <button
                    :disabled="!answer || form.processing"
                    @click="submit(answer)"
                    class="fm-btn-primary disabled:opacity-50"
                >
                    {{ t('common.save') }}
                </button>
            </div>
        </div>
    </FocusLayout>
</template>
