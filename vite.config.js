import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/custom-filament.css',         // <— ejemplo
                'resources/css/filament/admin/theme.css',    // <— si usas tema de filament
                'resources/js/app.js',
              ],
            refresh: true,
        }),
    ],
});
