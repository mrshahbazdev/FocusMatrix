import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { createPinia } from 'pinia';
import { i18n, setLocale } from './i18n';

const appName = import.meta.env.VITE_APP_NAME || 'FocusMatrix';

createInertiaApp({
    title: (title) => `${title ? title + ' · ' : ''}${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        const locale = props?.initialPage?.props?.locale;
        if (locale) setLocale(locale);

        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(createPinia())
            .use(i18n)
            .mount(el);
    },
    progress: {
        color: '#2f6bff',
    },
});
