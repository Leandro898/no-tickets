// vite.config.js
import { defineConfig } from 'vite';
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
        // ¡¡¡AQUÍ ESTÁ LA CLAVE PARA EL ERROR DE CONTENIDO MIXTO!!!
        origin: 'https://e1a7-191-80-168-239.ngrok-free.app:5173', // <-- Usa la URL de Ngrok CON HTTPS y el puerto de Vite
        hmr: {
            host: 'e1a7-191-80-168-239.ngrok-free.app', // Solo el dominio de Ngrok
            protocol: 'wss', // Mantén 'wss' para WebSockets seguros
            clientPort: 5173,
        },
    },
});