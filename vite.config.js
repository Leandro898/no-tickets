// vite.config.js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import { fileURLToPath, URL } from 'node:url';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/filament/admin/filament.css',
                'resources/js/app.js',
                'resources/js/scanner/index.js',
            ],
            refresh: true,
        }),
        vue(),
    ],
    resolve: {
        alias: {
            // Para que import { createApp } from 'vue' funcione bien
            vue: 'vue/dist/vue.esm-bundler.js',
            // Alias “@” apunta a resources/js
            '@': fileURLToPath(new URL('./resources/js', import.meta.url)),
        },
    },
});
