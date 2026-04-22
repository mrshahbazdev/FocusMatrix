<script setup>
import { ref, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const visible = ref(false);

function getCookie(name) {
    const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
    return match ? match[2] : null;
}

function setCookie(name, value, days = 365) {
    const expires = new Date(Date.now() + days * 864e5).toUTCString();
    document.cookie = `${name}=${encodeURIComponent(value)}; expires=${expires}; path=/; SameSite=Lax`;
}

onMounted(() => {
    if (!getCookie('fm_cookie_consent')) {
        visible.value = true;
    }
});

function accept() {
    setCookie('fm_cookie_consent', 'essential-only');
    visible.value = false;
}
</script>

<template>
    <transition
        enter-active-class="transition duration-300"
        enter-from-class="opacity-0 translate-y-4"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition duration-200"
        leave-to-class="opacity-0 translate-y-4"
    >
        <div v-if="visible"
            class="fixed bottom-0 inset-x-0 z-50 p-4 md:p-6 flex justify-center pointer-events-none">
            <div class="max-w-3xl w-full pointer-events-auto bg-navy-900 text-white rounded-xl shadow-2xl border border-navy-800 p-5 md:p-6 flex flex-col md:flex-row items-start gap-4">
                <div class="flex-1 text-sm leading-relaxed">
                    <div class="font-display text-base font-semibold mb-1">🍪 {{ t('legal.cookies_banner_title') }}</div>
                    <p class="text-navy-200">
                        {{ t('legal.cookies_banner_body') }}
                        <Link href="/legal/cookies" class="underline hover:text-accent">{{ t('legal.cookies_banner_learn') }}</Link>
                        ·
                        <Link href="/legal/privacy" class="underline hover:text-accent">{{ t('legal.privacy') }}</Link>
                    </p>
                </div>
                <button @click="accept"
                    class="shrink-0 px-5 py-2.5 rounded-lg bg-accent hover:bg-accent-600 transition font-medium text-sm">
                    {{ t('legal.cookies_banner_accept') }}
                </button>
            </div>
        </div>
    </transition>
</template>
