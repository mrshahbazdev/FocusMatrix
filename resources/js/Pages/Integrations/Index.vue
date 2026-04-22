<script setup>
import { ref } from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import axios from 'axios';
import FocusLayout from '@/Layouts/FocusLayout.vue';
import {
    CalendarDaysIcon, CheckCircleIcon, LinkIcon, ClockIcon,
    ChatBubbleLeftRightIcon, UserGroupIcon, ArrowPathIcon, ClipboardDocumentIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    google: Object,
    google_configured: Boolean,
    slack: Object,
    teams: Object,
    ics: Object,
});

const { t } = useI18n();

function disconnect(provider) {
    if (!confirm(t('integrations.confirm_disconnect'))) return;
    if (provider === 'google') {
        router.delete(route('integrations.google.disconnect'));
    } else {
        router.delete(route('integrations.webhook.disconnect', { provider }));
    }
}

const slackForm = useForm({ webhook_url: '', label: '' });
const teamsForm = useForm({ webhook_url: '', label: '' });
const slackTest = ref(null);
const teamsTest = ref(null);
const slackTesting = ref(false);
const teamsTesting = ref(false);

async function testWebhook(provider) {
    const form = provider === 'slack' ? slackForm : teamsForm;
    const testingRef = provider === 'slack' ? slackTesting : teamsTesting;
    const resultRef = provider === 'slack' ? slackTest : teamsTest;

    if (!form.webhook_url || form.webhook_url.length < 20) {
        resultRef.value = { ok: false, message: t('integrations.webhook_enter_first') };
        return;
    }

    testingRef.value = true;
    resultRef.value = null;
    try {
        const { data } = await axios.post(
            route('integrations.webhook.test', { provider }),
            { webhook_url: form.webhook_url }
        );
        resultRef.value = data;
    } catch (e) {
        resultRef.value = { ok: false, message: e.message };
    } finally {
        testingRef.value = false;
    }
}

function saveWebhook(provider) {
    const form = provider === 'slack' ? slackForm : teamsForm;
    form.post(route('integrations.webhook.connect', { provider }), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
}

function regenerateIcs() {
    if (!confirm(t('integrations.confirm_regenerate_ics'))) return;
    router.post(route('integrations.ics.regenerate'));
}

const copied = ref('');
function copy(text, key) {
    navigator.clipboard?.writeText(text);
    copied.value = key;
    setTimeout(() => (copied.value = ''), 2000);
}
</script>

<template>
    <FocusLayout>
        <template #breadcrumbs>FocusMatrix · {{ t('nav.integrations') }}</template>
        <template #title>{{ t('integrations.title') }}</template>

        <p class="text-sm text-graphite-600 max-w-xl mb-6">{{ t('integrations.subtitle') }}</p>

        <div class="grid md:grid-cols-2 gap-6">
            <!-- Google Calendar -->
            <div class="fm-card">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-xl bg-accent/10 text-accent flex items-center justify-center">
                        <CalendarDaysIcon class="w-6 h-6" />
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <h3 class="font-display text-lg font-semibold text-navy-900">{{ t('integrations.google_name') }}</h3>
                            <span v-if="google.connected" class="fm-badge bg-emerald-100 text-emerald-800 flex items-center gap-1">
                                <CheckCircleIcon class="w-3 h-3" /> {{ t('integrations.connected') }}
                            </span>
                        </div>
                        <p class="text-sm text-graphite-600 mt-1">{{ t('integrations.google_desc') }}</p>

                        <div v-if="google.connected" class="mt-4 text-sm text-navy-800 space-y-1">
                            <div class="flex items-center gap-2">
                                <LinkIcon class="w-4 h-4 text-graphite-400" />
                                {{ t('integrations.connected_as') }}: <span class="font-medium">{{ google.account_email || '—' }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <ClockIcon class="w-4 h-4 text-graphite-400" />
                                {{ t('integrations.last_synced') }}: <span class="font-medium">
                                    {{ google.last_synced_at ? new Date(google.last_synced_at).toLocaleString() : t('integrations.never_synced') }}
                                </span>
                            </div>
                        </div>

                        <div v-if="!google_configured" class="mt-4 p-3 rounded-lg bg-amber-50 border border-amber-200 text-xs text-amber-800">
                            {{ t('integrations.not_configured') }}
                        </div>

                        <div class="mt-5 flex items-center gap-3">
                            <a v-if="!google.connected" :href="route('integrations.google.connect')"
                                :class="['fm-btn-primary', !google_configured ? 'pointer-events-none opacity-50' : '']">
                                {{ t('integrations.connect') }}
                            </a>
                            <Link v-if="google.connected" :href="route('calendar.index')" class="fm-btn-secondary">
                                {{ t('nav.calendar') }}
                            </Link>
                            <button v-if="google.connected" @click="disconnect('google')" class="fm-btn-ghost text-rose-600">
                                {{ t('integrations.disconnect') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Slack -->
            <div class="fm-card">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center">
                        <ChatBubbleLeftRightIcon class="w-6 h-6" />
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <h3 class="font-display text-lg font-semibold text-navy-900">Slack</h3>
                            <span v-if="slack.connected" class="fm-badge bg-emerald-100 text-emerald-800 flex items-center gap-1">
                                <CheckCircleIcon class="w-3 h-3" /> {{ t('integrations.connected') }}
                            </span>
                        </div>
                        <p class="text-sm text-graphite-600 mt-1">{{ t('integrations.slack_desc') }}</p>

                        <div v-if="slack.connected" class="mt-4 text-xs text-graphite-600">
                            <div class="font-mono break-all">{{ slack.webhook_preview }}</div>
                            <div class="mt-1">{{ t('integrations.last_synced') }}:
                                {{ slack.last_synced_at ? new Date(slack.last_synced_at).toLocaleString() : t('integrations.never_synced') }}
                            </div>
                        </div>

                        <form v-else @submit.prevent="saveWebhook('slack')" class="mt-4 space-y-2">
                            <input v-model="slackForm.webhook_url" type="url" class="fm-input w-full font-mono text-xs"
                                placeholder="https://hooks.slack.com/services/T.../B.../..." />
                            <input v-model="slackForm.label" type="text" class="fm-input w-full text-sm"
                                :placeholder="t('integrations.webhook_label_placeholder')" />
                            <a href="https://api.slack.com/messaging/webhooks" target="_blank" rel="noopener"
                                class="text-xs text-accent hover:underline">{{ t('integrations.slack_how') }}</a>
                        </form>

                        <div v-if="slackTest" class="mt-3 text-xs p-2 rounded-lg"
                            :class="slackTest.ok ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-rose-50 text-rose-800 border border-rose-200'">
                            {{ slackTest.ok ? t('integrations.webhook_test_ok') : (slackTest.body || slackTest.message) }}
                        </div>

                        <div class="mt-5 flex flex-wrap items-center gap-2">
                            <button v-if="!slack.connected" :disabled="slackTesting || !slackForm.webhook_url"
                                @click="testWebhook('slack')" class="fm-btn-ghost text-xs">
                                {{ slackTesting ? t('integrations.testing') : t('integrations.test_connection') }}
                            </button>
                            <button v-if="!slack.connected" @click="saveWebhook('slack')"
                                :disabled="slackForm.processing || !slackForm.webhook_url" class="fm-btn-primary">
                                {{ t('integrations.save') }}
                            </button>
                            <button v-if="slack.connected" @click="disconnect('slack')"
                                class="fm-btn-ghost text-rose-600">{{ t('integrations.disconnect') }}</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Microsoft Teams -->
            <div class="fm-card">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center">
                        <UserGroupIcon class="w-6 h-6" />
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <h3 class="font-display text-lg font-semibold text-navy-900">Microsoft Teams</h3>
                            <span v-if="teams.connected" class="fm-badge bg-emerald-100 text-emerald-800 flex items-center gap-1">
                                <CheckCircleIcon class="w-3 h-3" /> {{ t('integrations.connected') }}
                            </span>
                        </div>
                        <p class="text-sm text-graphite-600 mt-1">{{ t('integrations.teams_desc') }}</p>

                        <div v-if="teams.connected" class="mt-4 text-xs text-graphite-600">
                            <div class="font-mono break-all">{{ teams.webhook_preview }}</div>
                            <div class="mt-1">{{ t('integrations.last_synced') }}:
                                {{ teams.last_synced_at ? new Date(teams.last_synced_at).toLocaleString() : t('integrations.never_synced') }}
                            </div>
                        </div>

                        <form v-else @submit.prevent="saveWebhook('teams')" class="mt-4 space-y-2">
                            <input v-model="teamsForm.webhook_url" type="url" class="fm-input w-full font-mono text-xs"
                                placeholder="https://[org].webhook.office.com/webhookb2/..." />
                            <input v-model="teamsForm.label" type="text" class="fm-input w-full text-sm"
                                :placeholder="t('integrations.webhook_label_placeholder')" />
                            <a href="https://learn.microsoft.com/en-us/microsoftteams/platform/webhooks-and-connectors/how-to/add-incoming-webhook"
                                target="_blank" rel="noopener" class="text-xs text-accent hover:underline">
                                {{ t('integrations.teams_how') }}
                            </a>
                        </form>

                        <div v-if="teamsTest" class="mt-3 text-xs p-2 rounded-lg"
                            :class="teamsTest.ok ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-rose-50 text-rose-800 border border-rose-200'">
                            {{ teamsTest.ok ? t('integrations.webhook_test_ok') : (teamsTest.body || teamsTest.message) }}
                        </div>

                        <div class="mt-5 flex flex-wrap items-center gap-2">
                            <button v-if="!teams.connected" :disabled="teamsTesting || !teamsForm.webhook_url"
                                @click="testWebhook('teams')" class="fm-btn-ghost text-xs">
                                {{ teamsTesting ? t('integrations.testing') : t('integrations.test_connection') }}
                            </button>
                            <button v-if="!teams.connected" @click="saveWebhook('teams')"
                                :disabled="teamsForm.processing || !teamsForm.webhook_url" class="fm-btn-primary">
                                {{ t('integrations.save') }}
                            </button>
                            <button v-if="teams.connected" @click="disconnect('teams')"
                                class="fm-btn-ghost text-rose-600">{{ t('integrations.disconnect') }}</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ICS calendar feed -->
            <div class="fm-card">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-xl bg-accent/10 text-accent flex items-center justify-center">
                        <CalendarDaysIcon class="w-6 h-6" />
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <h3 class="font-display text-lg font-semibold text-navy-900">{{ t('integrations.ics_name') }}</h3>
                            <span class="fm-badge bg-emerald-100 text-emerald-800">{{ t('integrations.always_on') }}</span>
                        </div>
                        <p class="text-sm text-graphite-600 mt-1">{{ t('integrations.ics_desc') }}</p>

                        <div class="mt-4 space-y-2 text-xs">
                            <div>
                                <div class="text-graphite-500 mb-1">{{ t('integrations.ics_http_url') }}</div>
                                <div class="flex items-center gap-2">
                                    <input readonly :value="ics.url" class="fm-input flex-1 font-mono text-xs bg-graphite-50" />
                                    <button @click="copy(ics.url, 'http')" class="fm-btn-ghost px-2 py-1">
                                        <ClipboardDocumentIcon class="w-4 h-4" />
                                    </button>
                                </div>
                                <div v-if="copied === 'http'" class="text-xs text-emerald-700 mt-1">{{ t('integrations.copied') }}</div>
                            </div>
                            <div>
                                <div class="text-graphite-500 mb-1">{{ t('integrations.ics_webcal_url') }}</div>
                                <div class="flex items-center gap-2">
                                    <input readonly :value="ics.webcal_url" class="fm-input flex-1 font-mono text-xs bg-graphite-50" />
                                    <button @click="copy(ics.webcal_url, 'webcal')" class="fm-btn-ghost px-2 py-1">
                                        <ClipboardDocumentIcon class="w-4 h-4" />
                                    </button>
                                </div>
                                <div v-if="copied === 'webcal'" class="text-xs text-emerald-700 mt-1">{{ t('integrations.copied') }}</div>
                            </div>
                        </div>

                        <div class="mt-4 text-xs text-graphite-600">{{ t('integrations.ics_help') }}</div>

                        <div class="mt-5">
                            <button @click="regenerateIcs" class="fm-btn-ghost text-xs flex items-center gap-1">
                                <ArrowPathIcon class="w-4 h-4" /> {{ t('integrations.regenerate_token') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </FocusLayout>
</template>
