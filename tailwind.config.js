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
                500: '#a084ee',
                600: '#7c3aed',
                // <<<<< AÑADÍ ESTO >>>>>
                success: {
                    DEFAULT: '#22c55e', // tailwind green-500
                    50: '#f0fdf4',
                    100: '#dcfce7',
                    200: '#bbf7d0',
                    300: '#86efac',
                    400: '#4ade80',
                    500: '#22c55e', // verde principal
                    600: '#16a34a',
                    700: '#15803d',
                    800: '#166534',
                    900: '#14532d',
                },
                warning: {
                    DEFAULT: '#facc15', // tailwind yellow-400
                    500: '#facc15',
                },
                danger: {
                    DEFAULT: '#ef4444', // tailwind red-500
                    500: '#ef4444',
                },
            },
        },
    },

    plugins: [
        forms,
    ],
};