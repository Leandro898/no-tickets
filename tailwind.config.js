// tailwind.config.js
/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
        './app/Filament/**/*.php',
        './resources/views/filament/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
        './vendor/filament/**/*.php',
        './vendor/awcodes/filament-badgeable/resources/views/**/*.blade.php',
        // Asegúrate de que TODAS las rutas donde usas clases de Tailwind estén aquí.
    ],
    theme: {
        extend: {},
    },
    plugins: [],
};