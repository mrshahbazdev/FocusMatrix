import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
        './resources/js/**/*.js',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', 'Figtree', ...defaultTheme.fontFamily.sans],
                display: ['"DM Sans"', 'Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Corporate navy + grey palette
                navy: {
                    50:  '#f4f6fb',
                    100: '#e6ebf4',
                    200: '#c5d1e5',
                    300: '#9cb0d0',
                    400: '#6c85b2',
                    500: '#476294',
                    600: '#304b79',
                    700: '#233a61',
                    800: '#172a48',
                    900: '#0d1c33',
                    950: '#06101f',
                },
                graphite: {
                    50:  '#f7f8fa',
                    100: '#eef0f3',
                    200: '#d9dde3',
                    300: '#b7bec8',
                    400: '#8a93a1',
                    500: '#646d7d',
                    600: '#4c5362',
                    700: '#3b414e',
                    800: '#292e38',
                    900: '#1b1f26',
                },
                accent: {
                    DEFAULT: '#2f6bff',
                    500: '#2f6bff',
                    600: '#1f54d9',
                },
                primary: {
                    DEFAULT: '#172a48',
                    50:  '#f4f6fb',
                    100: '#e6ebf4',
                    200: '#c5d1e5',
                    300: '#9cb0d0',
                    400: '#6c85b2',
                    500: '#476294',
                    600: '#304b79',
                    700: '#233a61',
                    800: '#172a48',
                    900: '#0d1c33',
                },
            },
            boxShadow: {
                soft: '0 1px 2px rgba(13,28,51,0.04), 0 2px 8px rgba(13,28,51,0.06)',
                lifted: '0 4px 12px rgba(13,28,51,0.08), 0 12px 32px rgba(13,28,51,0.08)',
            },
            borderRadius: {
                xl: '0.9rem',
                '2xl': '1.1rem',
            },
        },
    },

    plugins: [forms, typography],
};
