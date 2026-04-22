<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { XMarkIcon, CalendarDaysIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    open: Boolean,
    task: Object,
});
const emit = defineEmits(['close']);

const { t } = useI18n();

const start = ref('');
const minutes = ref(60);
const processing = ref(false);

function defaultStart() {
    const d = new Date();
    d.setMinutes(0, 0, 0);
    d.setHours(d.getHours() + 1);
    const tzOffset = d.getTimezoneOffset() * 60000;
    return new Date(d - tzOffset).toISOString().slice(0, 16);
}

watch(() => props.open, (v) => {
    if (v) start.value = defaultStart();
});

function submit() {
    processing.value = true;
    router.post(route('calendar.focus-block', props.task.id), {
        start: start.value,
        minutes: minutes.value,
    }, {
        preserveScroll: true,
        onFinish: () => {
            processing.value = false;
            emit('close');
        },
    });
}
</script>

<template>
    <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-navy-900/40">
        <div class="bg-white rounded-2xl shadow-lifted border border-graphite-200 w-full max-w-md">
            <div class="flex items-center justify-between p-5 border-b border-graphite-100">
                <div class="flex items-center gap-2">
                    <CalendarDaysIcon class="w-5 h-5 text-accent" />
                    <h3 class="font-display text-lg font-semibold text-navy-900">{{ t('calendar.block_focus') }}</h3>
                </div>
                <button @click="emit('close')" class="text-graphite-500 hover:text-navy-900">
                    <XMarkIcon class="w-5 h-5" />
                </button>
            </div>
            <div class="p-5 space-y-4">
                <div class="text-sm text-graphite-600">{{ t('calendar.block_focus_for') }}: <span class="font-medium text-navy-900">{{ task?.title }}</span></div>
                <div>
                    <label class="fm-label">{{ t('calendar.start_at') }}</label>
                    <input v-model="start" type="datetime-local" class="fm-input" />
                </div>
                <div>
                    <label class="fm-label">{{ t('calendar.duration') }}</label>
                    <input v-model.number="minutes" type="number" min="15" max="240" step="15" class="fm-input" />
                </div>
            </div>
            <div class="p-5 border-t border-graphite-100 flex justify-end gap-2">
                <button @click="emit('close')" class="fm-btn-ghost">{{ t('common.cancel') }}</button>
                <button :disabled="processing" @click="submit" class="fm-btn-primary disabled:opacity-50">
                    {{ t('calendar.create_block') }}
                </button>
            </div>
        </div>
    </div>
</template>
