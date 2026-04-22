<script setup>
import { ref, computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import axios from 'axios';
import FocusLayout from '@/Layouts/FocusLayout.vue';
import {
    SparklesIcon, KeyIcon, BoltIcon, CheckCircleIcon, ExclamationTriangleIcon, TrashIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    setting: Object,
    providers: Object,
    models: Object,
    default_models: Object,
});

const { t } = useI18n();

const form = useForm({
    provider: props.setting?.provider ?? 'gemini',
    api_key: '',
    model: props.setting?.model ?? props.default_models?.gemini ?? 'gemini-2.0-flash',
    enabled: props.setting?.enabled ?? true,
    monthly_limit: props.setting?.monthly_limit ?? 200,
});

const testResult = ref(null); // { ok: bool, message: string }
const testing = ref(false);

const providerKeys = computed(() => Object.keys(props.providers));
const currentModels = computed(() => props.models?.[form.provider] ?? []);

function onProviderChange() {
    form.model = props.default_models?.[form.provider] ?? currentModels.value[0];
    testResult.value = null;
}

async function testConnection() {
    if (!form.api_key || form.api_key.length < 20) {
        testResult.value = { ok: false, message: t('ai.enter_key_first') };
        return;
    }
    testing.value = true;
    testResult.value = null;
    try {
        const { data } = await axios.post(route('ai.test'), {
            provider: form.provider,
            api_key: form.api_key,
            model: form.model,
        });
        testResult.value = data;
    } catch (e) {
        testResult.value = { ok: false, message: e?.response?.data?.message ?? e.message };
    } finally {
        testing.value = false;
    }
}

function save() {
    form.put(route('ai.update'), { preserveScroll: true });
}

function removeKey() {
    if (!confirm(t('ai.confirm_remove'))) return;
    router.delete(route('ai.destroy'), { preserveScroll: true });
}

const providerLinks = {
    gemini: 'https://aistudio.google.com/app/apikey',
    openai: 'https://platform.openai.com/api-keys',
    anthropic: 'https://console.anthropic.com/settings/keys',
};
</script>

<template>
    <FocusLayout>
        <template #breadcrumbs>FocusMatrix · {{ t('ai.title') }}</template>
        <template #title>
            <span class="inline-flex items-center gap-2"><SparklesIcon class="w-6 h-6 text-accent" /> {{ t('ai.title') }}</span>
        </template>

        <div class="max-w-3xl space-y-6">
            <!-- Intro card -->
            <div class="fm-card">
                <div class="fm-section-title">{{ t('ai.byok_title') }}</div>
                <p class="text-sm text-graphite-600 mt-2">{{ t('ai.byok_desc') }}</p>
                <ul class="mt-3 text-sm text-graphite-700 space-y-1 list-disc ml-5">
                    <li>{{ t('ai.feature_triage') }}</li>
                    <li>{{ t('ai.feature_delegation') }}</li>
                    <li>{{ t('ai.feature_insights') }}</li>
                </ul>
            </div>

            <!-- Status -->
            <div v-if="setting?.has_key" class="fm-card bg-success/5 border-success/30">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2 text-success font-semibold">
                            <CheckCircleIcon class="w-5 h-5" /> {{ t('ai.connected') }}
                        </div>
                        <div class="text-sm text-graphite-700 mt-1">
                            {{ providers[setting.provider] }} · {{ setting.model }}<br />
                            <span class="font-mono text-xs">{{ setting.masked_key }}</span>
                        </div>
                        <div class="text-xs text-graphite-500 mt-2">
                            {{ t('ai.used_this_month', { used: setting.calls_this_month, limit: setting.monthly_limit }) }}
                            · {{ t('ai.remaining', { n: setting.remaining }) }}
                        </div>
                    </div>
                    <button @click="removeKey" class="fm-btn-secondary !py-1.5 text-xs text-red-600 hover:bg-red-50">
                        <TrashIcon class="w-4 h-4" /> {{ t('ai.remove_key') }}
                    </button>
                </div>
            </div>

            <!-- Config form -->
            <form @submit.prevent="save" class="fm-card space-y-4">
                <div class="fm-section-title">{{ t('ai.configure') }}</div>

                <div>
                    <label class="fm-label">{{ t('ai.provider') }}</label>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mt-2">
                        <label v-for="key in providerKeys" :key="key"
                            :class="[
                                'fm-card cursor-pointer transition border',
                                form.provider === key ? 'border-accent bg-accent/5 ring-1 ring-accent' : 'border-graphite-200 hover:border-accent/50'
                            ]">
                            <input type="radio" v-model="form.provider" :value="key" class="sr-only" @change="onProviderChange" />
                            <div class="font-semibold text-navy-900">{{ providers[key] }}</div>
                            <div class="text-xs text-graphite-500 mt-1">
                                {{ default_models[key] }}
                            </div>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="fm-label">{{ t('ai.api_key') }}</label>
                    <input v-model="form.api_key" type="password" autocomplete="off"
                        :placeholder="setting?.has_key ? t('ai.key_placeholder_set') : t('ai.key_placeholder')"
                        class="fm-input w-full font-mono" />
                    <p class="text-xs text-graphite-500 mt-1">
                        {{ t('ai.get_key') }}:
                        <a :href="providerLinks[form.provider]" target="_blank" class="text-accent hover:underline">
                            {{ providerLinks[form.provider] }}
                        </a>
                    </p>
                    <div v-if="form.api_key" class="text-xs text-graphite-600 mt-1">
                        🔒 {{ t('ai.key_encrypted_notice') }}
                    </div>
                </div>

                <div>
                    <label class="fm-label">{{ t('ai.model') }}</label>
                    <select v-model="form.model" class="fm-input w-full">
                        <option v-for="m in currentModels" :key="m" :value="m">{{ m }}</option>
                    </select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" v-model="form.enabled" class="rounded" />
                        {{ t('ai.enabled') }}
                    </label>
                    <div>
                        <label class="fm-label">{{ t('ai.monthly_limit') }}</label>
                        <input v-model.number="form.monthly_limit" type="number" min="10" max="10000" class="fm-input w-full" />
                    </div>
                </div>

                <!-- Test result -->
                <div v-if="testResult" :class="[
                    'text-sm rounded-lg px-3 py-2 flex items-start gap-2',
                    testResult.ok ? 'bg-success/10 text-success' : 'bg-red-50 text-red-700'
                ]">
                    <component :is="testResult.ok ? CheckCircleIcon : ExclamationTriangleIcon" class="w-5 h-5 shrink-0 mt-0.5" />
                    <span>{{ testResult.message }}</span>
                </div>

                <div class="flex gap-2 pt-2 border-t border-graphite-100">
                    <button type="button" @click="testConnection" :disabled="testing" class="fm-btn-secondary">
                        <BoltIcon class="w-4 h-4" />
                        {{ testing ? t('ai.testing') : t('ai.test_connection') }}
                    </button>
                    <button type="submit" :disabled="form.processing" class="fm-btn-primary">
                        <KeyIcon class="w-4 h-4" /> {{ t('common.save') }}
                    </button>
                </div>

                <div v-if="form.errors.api_key" class="text-xs text-red-600">{{ form.errors.api_key }}</div>
                <div v-if="form.errors.provider" class="text-xs text-red-600">{{ form.errors.provider }}</div>
            </form>
        </div>
    </FocusLayout>
</template>
