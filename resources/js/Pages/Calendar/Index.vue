<script setup>
import { computed, ref } from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import FocusLayout from '@/Layouts/FocusLayout.vue';
import {
    ChevronLeftIcon, ChevronRightIcon, PlusIcon, XMarkIcon,
    MapPinIcon, ClockIcon, TrashIcon, ExclamationTriangleIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    connected: Boolean,
    account_email: String,
    events: Array,
    weak_meetings: Array,
    month_anchor: String, // YYYY-MM
    grid_start: String,
    grid_end: String,
    today: String,
});

const { t, locale } = useI18n();

const anchor = computed(() => {
    const [y, m] = props.month_anchor.split('-').map(Number);
    return new Date(y, m - 1, 1);
});

const monthLabel = computed(() =>
    anchor.value.toLocaleDateString(locale.value === 'de' ? 'de-DE' : 'en-US', { month: 'long', year: 'numeric' })
);

const gridStart = computed(() => new Date(props.grid_start));
const gridEnd = computed(() => new Date(props.grid_end));

// Build 42-cell grid (6 weeks)
const days = computed(() => {
    const arr = [];
    const cursor = new Date(gridStart.value);
    while (cursor <= gridEnd.value) {
        arr.push(new Date(cursor));
        cursor.setDate(cursor.getDate() + 1);
    }
    return arr;
});

const weekdays = computed(() => {
    const ref = new Date(gridStart.value);
    const names = [];
    for (let i = 0; i < 7; i++) {
        const d = new Date(ref);
        d.setDate(ref.getDate() + i);
        names.push(d.toLocaleDateString(locale.value === 'de' ? 'de-DE' : 'en-US', { weekday: 'short' }));
    }
    return names;
});

const isSameDay = (a, b) => a.getFullYear() === b.getFullYear() && a.getMonth() === b.getMonth() && a.getDate() === b.getDate();
const isToday = (d) => isSameDay(d, new Date(props.today));
const isCurrentMonth = (d) => d.getMonth() === anchor.value.getMonth();

function eventsFor(day) {
    return props.events.filter(ev => {
        const s = new Date(ev.starts_at);
        return isSameDay(s, day);
    }).sort((a, b) => new Date(a.starts_at) - new Date(b.starts_at));
}

function prevMonth() {
    const d = new Date(anchor.value);
    d.setMonth(d.getMonth() - 1);
    router.get(route('calendar.index'), { month: `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}` }, { preserveState: false });
}
function nextMonth() {
    const d = new Date(anchor.value);
    d.setMonth(d.getMonth() + 1);
    router.get(route('calendar.index'), { month: `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}` }, { preserveState: false });
}
function goToday() {
    router.get(route('calendar.index'), {}, { preserveState: false });
}

// ---- Create / edit modal ----
const modalOpen = ref(false);
const editing = ref(null);

const form = useForm({
    title: '',
    description: '',
    location: '',
    color: 'accent',
    all_day: false,
    starts_at: '',
    ends_at: '',
});

function openCreate(day = null) {
    editing.value = null;
    const base = day ? new Date(day) : new Date();
    base.setHours(9, 0, 0, 0);
    const end = new Date(base);
    end.setHours(base.getHours() + 1);
    form.reset();
    form.title = '';
    form.description = '';
    form.location = '';
    form.color = 'accent';
    form.all_day = false;
    form.starts_at = toLocal(base);
    form.ends_at = toLocal(end);
    modalOpen.value = true;
}

function openEdit(ev) {
    if (ev.type !== 'native' || ev.source !== 'manual') return;
    editing.value = ev;
    form.title = ev.title;
    form.description = ev.description ?? '';
    form.location = ev.location ?? '';
    form.color = ev.color;
    form.all_day = ev.all_day;
    form.starts_at = toLocal(new Date(ev.starts_at));
    form.ends_at = toLocal(new Date(ev.ends_at));
    modalOpen.value = true;
}

function toLocal(d) {
    const pad = (n) => String(n).padStart(2, '0');
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
}

function submit() {
    if (editing.value) {
        form.patch(route('calendar.events.update', { event: editing.value.id }), {
            preserveScroll: true,
            onSuccess: () => { modalOpen.value = false; },
        });
    } else {
        form.post(route('calendar.events.store'), {
            preserveScroll: true,
            onSuccess: () => { modalOpen.value = false; },
        });
    }
}

function destroy() {
    if (!editing.value) return;
    if (!confirm(t('calendar.confirm_delete'))) return;
    router.delete(route('calendar.events.destroy', { event: editing.value.id }), {
        preserveScroll: true,
        onSuccess: () => { modalOpen.value = false; },
    });
}

const colorClasses = {
    accent: 'bg-accent/10 text-accent border-accent/30',
    navy: 'bg-navy-100 text-navy-800 border-navy-300',
    emerald: 'bg-emerald-100 text-emerald-800 border-emerald-300',
    amber: 'bg-amber-100 text-amber-800 border-amber-300',
    rose: 'bg-rose-100 text-rose-800 border-rose-300',
    graphite: 'bg-graphite-100 text-graphite-700 border-graphite-300',
};

function formatTime(iso) {
    return new Date(iso).toLocaleTimeString(locale.value === 'de' ? 'de-DE' : 'en-US', { hour: '2-digit', minute: '2-digit' });
}
</script>

<template>
    <FocusLayout>
        <template #breadcrumbs>FocusMatrix · {{ t('nav.calendar') }}</template>
        <template #title>{{ t('nav.calendar') }}</template>

        <!-- Toolbar -->
        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
            <div class="flex items-center gap-2">
                <button @click="prevMonth" class="fm-btn-ghost px-2 py-2" :aria-label="t('calendar.prev_month')">
                    <ChevronLeftIcon class="w-4 h-4" />
                </button>
                <button @click="nextMonth" class="fm-btn-ghost px-2 py-2" :aria-label="t('calendar.next_month')">
                    <ChevronRightIcon class="w-4 h-4" />
                </button>
                <button @click="goToday" class="fm-btn-ghost text-xs">{{ t('calendar.today') }}</button>
                <div class="ml-2 font-display text-xl font-semibold text-navy-900 capitalize">{{ monthLabel }}</div>
            </div>
            <div class="flex items-center gap-2">
                <span v-if="connected" class="fm-badge bg-emerald-100 text-emerald-800 text-xs">
                    Google: {{ account_email }}
                </span>
                <Link v-else :href="route('integrations.index')" class="fm-btn-ghost text-xs">
                    {{ t('calendar.connect_google') }}
                </Link>
                <button @click="openCreate()" class="fm-btn-primary">
                    <PlusIcon class="w-4 h-4" /> {{ t('calendar.new_event') }}
                </button>
            </div>
        </div>

        <!-- Legend -->
        <div class="flex flex-wrap items-center gap-3 text-xs mb-3 text-graphite-600">
            <span class="inline-flex items-center gap-1"><span class="inline-block w-3 h-3 rounded bg-accent"></span>{{ t('calendar.legend_manual') }}</span>
            <span class="inline-flex items-center gap-1"><span class="inline-block w-3 h-3 rounded bg-emerald-500"></span>{{ t('calendar.legend_keep') }}</span>
            <span class="inline-flex items-center gap-1"><span class="inline-block w-3 h-3 rounded bg-navy-700"></span>{{ t('calendar.legend_focus') }}</span>
            <span v-if="connected" class="inline-flex items-center gap-1"><span class="inline-block w-3 h-3 rounded bg-amber-500"></span>{{ t('calendar.legend_google') }}</span>
        </div>

        <!-- Month grid -->
        <div class="fm-card !p-0 overflow-x-auto">
            <div class="min-w-[500px]">
                <div class="grid grid-cols-7 bg-navy-900 text-navy-100 text-xs uppercase tracking-wide">
                    <div v-for="(w, i) in weekdays" :key="i" class="px-1 sm:px-2 py-2 text-center font-medium">{{ w }}</div>
                </div>
                <div class="grid grid-cols-7">
                    <div v-for="(day, i) in days" :key="i"
                        @click="openCreate(day)"
                        :class="[
                            'min-h-[70px] sm:min-h-[110px] border-t border-r border-graphite-200 p-1 sm:p-1.5 cursor-pointer hover:bg-accent/5 transition flex flex-col gap-0.5 sm:gap-1',
                            !isCurrentMonth(day) ? 'bg-graphite-50/50 text-graphite-400' : 'bg-white',
                            (i + 1) % 7 === 0 ? 'border-r-0' : '',
                        ]">
                        <div class="flex items-center justify-between">
                            <span :class="[
                                'text-[10px] sm:text-xs font-medium',
                                isToday(day) ? 'bg-accent text-white px-1 sm:px-1.5 py-0.5 rounded-full' : 'text-navy-800',
                                !isCurrentMonth(day) ? 'text-graphite-400' : '',
                            ]">{{ day.getDate() }}</span>
                        </div>
                        <div class="flex flex-col gap-0.5">
                            <button v-for="ev in eventsFor(day).slice(0, 3)" :key="ev.id"
                                @click.stop="openEdit(ev)"
                                :class="['text-left text-[10px] sm:text-xs px-1 sm:px-1.5 py-0.5 rounded border truncate', colorClasses[ev.color] || colorClasses.accent]">
                                <span v-if="!ev.all_day" class="font-mono text-[10px] mr-1 hidden sm:inline">{{ formatTime(ev.starts_at) }}</span>
                                {{ ev.title }}
                            </button>
                            <div v-if="eventsFor(day).length > 3" class="text-[10px] text-graphite-500 px-1">
                                +{{ eventsFor(day).length - 3 }} {{ t('calendar.more') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Weak meetings (Google only) -->
        <div v-if="weak_meetings.length" class="fm-card mt-6">
            <div class="flex items-center gap-2 mb-2 text-navy-900">
                <ExclamationTriangleIcon class="w-5 h-5 text-amber-600" />
                <h3 class="font-display font-semibold">{{ t('calendar.weak_meetings') }}</h3>
            </div>
            <ul class="text-sm space-y-1">
                <li v-for="(m, i) in weak_meetings" :key="i" class="text-graphite-700">
                    <span class="font-medium">{{ m.title }}</span>
                    <span class="text-xs text-amber-700 ml-2">{{ m.flags.join(', ') }}</span>
                </li>
            </ul>
        </div>

        <!-- Create/edit modal -->
        <div v-if="modalOpen" class="fixed inset-0 z-50 bg-navy-900/50 flex items-center justify-center p-4" @click.self="modalOpen = false">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg">
                <div class="flex items-center justify-between p-5 border-b border-graphite-200">
                    <h2 class="font-display text-lg font-semibold text-navy-900">
                        {{ editing ? t('calendar.edit_event') : t('calendar.new_event') }}
                    </h2>
                    <button @click="modalOpen = false" class="text-graphite-500 hover:text-navy-900">
                        <XMarkIcon class="w-5 h-5" />
                    </button>
                </div>

                <form @submit.prevent="submit" class="p-5 space-y-3">
                    <div>
                        <label class="fm-label">{{ t('calendar.field_title') }}</label>
                        <input v-model="form.title" type="text" class="fm-input w-full" required autofocus />
                        <div v-if="form.errors.title" class="text-xs text-rose-600 mt-1">{{ form.errors.title }}</div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="fm-label flex items-center gap-1"><ClockIcon class="w-3 h-3" /> {{ t('calendar.field_starts') }}</label>
                            <input v-model="form.starts_at" type="datetime-local" class="fm-input w-full" required />
                        </div>
                        <div>
                            <label class="fm-label">{{ t('calendar.field_ends') }}</label>
                            <input v-model="form.ends_at" type="datetime-local" class="fm-input w-full" required />
                        </div>
                    </div>

                    <label class="inline-flex items-center gap-2 text-sm text-graphite-700">
                        <input v-model="form.all_day" type="checkbox" class="rounded border-graphite-300" />
                        {{ t('calendar.field_all_day') }}
                    </label>

                    <div>
                        <label class="fm-label flex items-center gap-1"><MapPinIcon class="w-3 h-3" /> {{ t('calendar.field_location') }}</label>
                        <input v-model="form.location" type="text" class="fm-input w-full" />
                    </div>

                    <div>
                        <label class="fm-label">{{ t('calendar.field_description') }}</label>
                        <textarea v-model="form.description" rows="2" class="fm-input w-full"></textarea>
                    </div>

                    <div>
                        <label class="fm-label">{{ t('calendar.field_color') }}</label>
                        <div class="flex gap-2">
                            <button v-for="c in ['accent', 'navy', 'emerald', 'amber', 'rose', 'graphite']" :key="c" type="button"
                                @click="form.color = c"
                                :class="[
                                    'w-7 h-7 rounded-full border-2 transition',
                                    form.color === c ? 'border-navy-900 scale-110' : 'border-transparent',
                                    c === 'accent' ? 'bg-accent' : '',
                                    c === 'navy' ? 'bg-navy-700' : '',
                                    c === 'emerald' ? 'bg-emerald-500' : '',
                                    c === 'amber' ? 'bg-amber-500' : '',
                                    c === 'rose' ? 'bg-rose-500' : '',
                                    c === 'graphite' ? 'bg-graphite-500' : '',
                                ]" />
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-2 border-t border-graphite-100">
                        <button v-if="editing" type="button" @click="destroy" class="fm-btn-ghost text-rose-600 flex items-center gap-1">
                            <TrashIcon class="w-4 h-4" /> {{ t('calendar.delete') }}
                        </button>
                        <div v-else></div>
                        <div class="flex items-center gap-2">
                            <button type="button" @click="modalOpen = false" class="fm-btn-ghost">{{ t('calendar.cancel') }}</button>
                            <button type="submit" :disabled="form.processing" class="fm-btn-primary">
                                {{ editing ? t('calendar.save') : t('calendar.create') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </FocusLayout>
</template>
