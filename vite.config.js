// vite.config.js
import { defineConfig } from 'vite'; // No necesitas loadEnv si no usas variables
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/filament/admin/theme.css',
            ],
            refresh: true,
        }),
    ],
    server: {
        host: '0.0.0.0', // Permite acceso desde tu red local
        hmr: {
            // ¡AQUÍ ES DONDE DEBES PONER EL DOMINIO DE NGROK!
            host: 'e1a7-191-80-168-239.ngrok-free.app', // Ejemplo: 'abcd-1234-5678.ngrok-free.app'
            protocol: 'wss', // Usa 'wss' si tu URL de Ngrok es HTTPS (lo más común). Si fuera HTTP, sería 'ws'.
            clientPort: 5173, // Opcional, pero recomendado mantenerlo.
        },
    },
});