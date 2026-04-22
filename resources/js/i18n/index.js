import { createI18n } from 'vue-i18n';
import en from './locales/en.json';
import de from './locales/de.json';

const DEFAULT_LOCALE = 'en';

function detectLocale() {
    if (typeof window === 'undefined') return DEFAULT_LOCALE;
    const fromHtml = document.documentElement.lang;
    if (fromHtml && ['en', 'de'].includes(fromHtml)) return fromHtml;
    const fromStorage = window.localStorage?.getItem('fm_locale');
    if (fromStorage && ['en', 'de'].includes(fromStorage)) return fromStorage;
    const fromBrowser = (navigator.language || 'en').slice(0, 2);
    return ['en', 'de'].includes(fromBrowser) ? fromBrowser : DEFAULT_LOCALE;
}

export const i18n = createI18n({
    legacy: false,
    globalInjection: true,
    locale: detectLocale(),
    fallbackLocale: DEFAULT_LOCALE,
    messages: { en, de },
});

export function setLocale(locale) {
    if (!['en', 'de'].includes(locale)) return;
    i18n.global.locale.value = locale;
    if (typeof window !== 'undefined') {
        window.localStorage?.setItem('fm_locale', locale);
        document.documentElement.lang = locale;
    }
}
