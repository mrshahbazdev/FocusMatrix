<script setup>
import { ref, onBeforeUnmount, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import axios from 'axios';
import { MicrophoneIcon, StopIcon, XMarkIcon, CheckIcon } from '@heroicons/vue/24/solid';

const emit = defineEmits(['transcribed']);
const { t } = useI18n();

const supported = computed(() => typeof navigator !== 'undefined' && !!navigator.mediaDevices?.getUserMedia && typeof MediaRecorder !== 'undefined');

const state = ref('idle'); // idle | recording | uploading | done | error
const errorMessage = ref('');
const transcription = ref('');
const createTask = ref(true);

let mediaRecorder = null;
let chunks = [];
let stream = null;
let timerId = null;
const elapsed = ref(0);

async function start() {
    if (!supported.value) {
        errorMessage.value = t('voice.unsupported');
        state.value = 'error';
        return;
    }
    errorMessage.value = '';
    transcription.value = '';
    try {
        stream = await navigator.mediaDevices.getUserMedia({ audio: true });
    } catch (e) {
        errorMessage.value = t('voice.permission_denied');
        state.value = 'error';
        return;
    }
    chunks = [];
    const mime = MediaRecorder.isTypeSupported('audio/webm;codecs=opus')
        ? 'audio/webm;codecs=opus'
        : (MediaRecorder.isTypeSupported('audio/webm') ? 'audio/webm' : '');
    mediaRecorder = new MediaRecorder(stream, mime ? { mimeType: mime } : undefined);
    mediaRecorder.ondataavailable = (e) => { if (e.data.size > 0) chunks.push(e.data); };
    mediaRecorder.onstop = upload;
    mediaRecorder.start();
    state.value = 'recording';
    elapsed.value = 0;
    timerId = setInterval(() => { elapsed.value += 1; if (elapsed.value >= 60) stop(); }, 1000);
}

function stop() {
    if (mediaRecorder && mediaRecorder.state !== 'inactive') {
        mediaRecorder.stop();
    }
    if (stream) stream.getTracks().forEach(t => t.stop());
    if (timerId) { clearInterval(timerId); timerId = null; }
}

async function upload() {
    state.value = 'uploading';
    const blob = new Blob(chunks, { type: chunks[0]?.type || 'audio/webm' });
    const form = new FormData();
    form.append('audio', blob, 'clip.webm');
    form.append('create_task', createTask.value ? '1' : '0');
    try {
        const { data } = await axios.post(route('voice.transcribe'), form, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
        if (data.ok) {
            transcription.value = data.text;
            state.value = 'done';
            emit('transcribed', data);
        } else {
            errorMessage.value = data.message;
            state.value = 'error';
        }
    } catch (e) {
        errorMessage.value = e.response?.data?.message || e.message;
        state.value = 'error';
    }
}

function reset() {
    state.value = 'idle';
    transcription.value = '';
    errorMessage.value = '';
    elapsed.value = 0;
}

onBeforeUnmount(() => {
    if (mediaRecorder && mediaRecorder.state !== 'inactive') mediaRecorder.stop();
    if (stream) stream.getTracks().forEach(t => t.stop());
    if (timerId) clearInterval(timerId);
});

const mmss = computed(() => {
    const m = String(Math.floor(elapsed.value / 60)).padStart(2, '0');
    const s = String(elapsed.value % 60).padStart(2, '0');
    return `${m}:${s}`;
});
</script>

<template>
    <div class="fm-card border-dashed">
        <div class="flex items-start gap-3">
            <button v-if="state === 'idle' || state === 'error'" @click="start"
                class="w-14 h-14 rounded-full bg-accent text-white flex items-center justify-center hover:bg-accent/90 shadow-md shrink-0"
                :title="t('voice.start')">
                <MicrophoneIcon class="w-6 h-6" />
            </button>
            <button v-else-if="state === 'recording'" @click="stop"
                class="w-14 h-14 rounded-full bg-rose-600 text-white flex items-center justify-center hover:bg-rose-700 shadow-md shrink-0 animate-pulse"
                :title="t('voice.stop')">
                <StopIcon class="w-6 h-6" />
            </button>
            <div v-else class="w-14 h-14 rounded-full bg-graphite-100 text-graphite-500 flex items-center justify-center shrink-0">
                <MicrophoneIcon class="w-6 h-6" />
            </div>

            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="font-display font-semibold text-navy-900">{{ t('voice.title') }}</div>
                        <div class="text-xs text-graphite-600">{{ t('voice.subtitle') }}</div>
                    </div>
                    <div v-if="state === 'recording'" class="text-sm font-mono text-rose-600">{{ mmss }} · {{ t('voice.max_60') }}</div>
                </div>

                <div v-if="state === 'idle'" class="mt-3 flex items-center gap-3 text-xs">
                    <label class="inline-flex items-center gap-2 text-graphite-700">
                        <input v-model="createTask" type="checkbox" class="rounded border-graphite-300" />
                        {{ t('voice.auto_create_task') }}
                    </label>
                </div>

                <div v-if="state === 'uploading'" class="mt-3 text-sm text-graphite-600">{{ t('voice.transcribing') }}</div>

                <div v-if="state === 'done'" class="mt-3 p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-sm text-emerald-900">
                    <div class="flex items-center gap-2 font-medium mb-1">
                        <CheckIcon class="w-4 h-4" /> {{ t('voice.done') }}
                    </div>
                    <div class="italic">"{{ transcription }}"</div>
                    <button @click="reset" class="mt-2 text-xs text-emerald-700 hover:underline">{{ t('voice.record_another') }}</button>
                </div>

                <div v-if="state === 'error'" class="mt-3 p-3 rounded-lg bg-rose-50 border border-rose-200 text-sm text-rose-800">
                    <div class="flex items-center gap-2 font-medium">
                        <XMarkIcon class="w-4 h-4" /> {{ errorMessage }}
                    </div>
                    <button @click="reset" class="mt-2 text-xs text-rose-700 hover:underline">{{ t('voice.try_again') }}</button>
                </div>

                <div v-if="!supported" class="mt-3 text-xs text-amber-700">{{ t('voice.unsupported') }}</div>
            </div>
        </div>
    </div>
</template>
