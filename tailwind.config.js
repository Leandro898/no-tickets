// tailwind.config.js
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
module.exports = {
    presets: [ // <-- ¡ASEGÚRATE DE QUE ESTA SECCIÓN ESTÉ AHÍ Y SEA CORRECTA!
        require('./vendor/filament/filament/tailwind.config.preset'),
    ],
    content: [
        // ... (tus rutas de contenido, como las habíamos ampliado)
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
        './app/Filament/**/*.php',
        './resources/views/filament/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
        './vendor/filament/**/*.php',
        './vendor/filament/forms/resources/views/**/*.blade.php',
        './vendor/filament/tables/resources/views/**/*.blade.php',
        './vendor/filament/actions/resources/views/**/*.blade.php',
        './vendor/filament/notifications/resources/views/**/*.blade.php',
        './vendor/filament/infolists/resources/views/**/*.blade.php',
        './vendor/filament/support/resources/views/**/*.blade.php',
        './vendor/filament/widgets/resources/views/**/*.blade.php',
        './resources/css/filament/admin/theme.css',
    ],
    theme: {
        extend: {
            // ... (cualquier extensión de tema que quieras)
        },
    },
    plugins: [
        forms,
        typography,
    ],
};