import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        // Vistas de Laravel y tus Blade:
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',

        // ** NUEVAS RUTAS PARA CSS Y FILAMENT **
        './resources/css/**/*.css',                // todos tus CSS (incluyendo app.css con @layer)
        './app/Filament/**/*.php',                 // clases PHP de tus Resources/Pages/…
        './vendor/filament/**/*.blade.php',        // vistas internas de Filament
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: '#7c3aed',
            },
        },
    },

    plugins: [
        forms,
    ],
};
