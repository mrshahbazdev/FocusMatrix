<script setup>
import { computed } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import { setLocale } from '@/i18n';
import { Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/vue';
import { LanguageIcon, CheckIcon } from '@heroicons/vue/24/outline';

const page = usePage();
const current = computed(() => page.props.locale || 'en');

const languages = [
    { code: 'en', label: 'English', flag: '🇬🇧' },
    { code: 'de', label: 'Deutsch', flag: '🇩🇪' },
];

function switchTo(code) {
    if (code === current.value) return;
    setLocale(code);
    router.visit(window.location.pathname + '?lang=' + code, {
        preserveScroll: true,
        preserveState: false,
    });
}
</script>

<template>
    <Menu as="div" class="relative inline-block text-left">
        <MenuButton class="fm-btn-ghost !px-3 !py-1.5 text-sm gap-1">
            <LanguageIcon class="w-4 h-4" />
            <span class="uppercase font-semibold">{{ current }}</span>
        </MenuButton>
        <transition enter-active-class="transition duration-100 ease-out" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100" leave-active-class="transition duration-75 ease-in" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <MenuItems class="absolute right-0 mt-2 w-44 origin-top-right rounded-xl bg-white shadow-lifted border border-graphite-200 focus:outline-none z-50 overflow-hidden">
                <MenuItem v-for="lang in languages" :key="lang.code" v-slot="{ active }">
                    <button
                        type="button"
                        @click="switchTo(lang.code)"
                        :class="['w-full flex items-center gap-2 px-3 py-2 text-sm', active ? 'bg-graphite-50' : '']"
                    >
                        <span>{{ lang.flag }}</span>
                        <span class="flex-1 text-left">{{ lang.label }}</span>
                        <CheckIcon v-if="current === lang.code" class="w-4 h-4 text-navy-700" />
                    </button>
                </MenuItem>
            </MenuItems>
        </transition>
    </Menu>
</template>
