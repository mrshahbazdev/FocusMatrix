<script setup>
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import BrandMark from '@/Components/BrandMark.vue';
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue';

const { t } = useI18n();
</script>

<template>
    <div class="min-h-screen bg-graphite-50">
        <header class="bg-white/70 backdrop-blur border-b border-graphite-200/60 sticky top-0 z-40">
            <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
                <Link href="/">
                    <BrandMark />
                </Link>
                <nav class="hidden md:flex items-center gap-8 text-sm text-navy-700">
                    <a href="#principle" class="hover:text-navy-900">{{ t('nav.principle') }}</a>
                    <a href="#features" class="hover:text-navy-900">{{ t('nav.features') }}</a>
                    <a href="#pricing" class="hover:text-navy-900">{{ t('nav.pricing') }}</a>
                </nav>
                <div class="flex items-center gap-3">
                    <LanguageSwitcher />
                    <Link :href="route('login')" class="text-sm text-navy-700 hover:text-navy-900 hidden sm:inline">{{ t('nav.login') }}</Link>
                    <Link :href="route('register')" class="fm-btn-primary">{{ t('nav.signup') }}</Link>
                </div>
            </div>
        </header>

        <main>
            <slot />
        </main>

        <footer class="bg-navy-950 text-navy-100 mt-24">
            <div class="max-w-7xl mx-auto px-6 py-12 grid md:grid-cols-3 gap-8">
                <div>
                    <div class="flex items-center gap-2 text-white font-display text-xl">
                        <div class="grid grid-cols-2 grid-rows-2 gap-0.5 w-6 h-6">
                            <div class="bg-white/90"></div>
                            <div class="bg-accent"></div>
                            <div class="bg-navy-400"></div>
                            <div class="bg-white/90"></div>
                        </div>
                        FocusMatrix
                    </div>
                    <p class="text-sm text-navy-200 mt-3 max-w-sm">{{ t('landing.footer_principle') }}</p>
                </div>
                <div class="text-sm">
                    <div class="fm-section-title text-navy-300 mb-2">{{ t('nav.features') }}</div>
                    <ul class="space-y-1">
                        <li>{{ t('landing.feature_dashboard_t') }}</li>
                        <li>{{ t('landing.feature_inbox_t') }}</li>
                        <li>{{ t('landing.feature_delegate_t') }}</li>
                        <li>{{ t('landing.feature_ai_t') }}</li>
                    </ul>
                </div>
                <div class="text-sm">
                    <div class="fm-section-title text-navy-300 mb-2">Legal</div>
                    <ul class="space-y-1">
                        <li>Impressum</li>
                        <li>DSGVO / GDPR</li>
                        <li>Datenschutz</li>
                        <li>AGB</li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-navy-800">
                <div class="max-w-7xl mx-auto px-6 py-4 text-xs text-navy-300 flex items-center justify-between">
                    <span>© {{ new Date().getFullYear() }} FocusMatrix. {{ t('landing.footer_rights') }}</span>
                    <span class="hidden sm:inline">Made with discipline in EU.</span>
                </div>
            </div>
        </footer>
    </div>
</template>
