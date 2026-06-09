import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                // Sneat core theme
                'resources/assets/vendor/scss/core.scss',
                // Sneat fonts / icons
                'resources/assets/vendor/fonts/iconify/iconify.css',
                // Sneat vendor libs
                'resources/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.scss',
                // Auth page styles
                'resources/assets/vendor/scss/pages/page-auth.scss',
                // Sneat JS
                'resources/assets/vendor/js/helpers.js',
                'resources/assets/js/config.js',
                'resources/assets/vendor/js/menu.js',
                'resources/assets/js/main.js',
                // App custom
                'resources/css/app.scss',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
    css: {
        preprocessorOptions: {
            scss: {
                quietDeps: true,
                silenceDeprecations: ['import', 'global-builtin', 'color-functions', 'if-function'],
            },
        },
    },
});
