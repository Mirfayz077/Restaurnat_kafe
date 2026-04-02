import daisyui from 'daisyui';
import flowbite from 'flowbite/plugin';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './app/**/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
        './storage/framework/views/*.php',
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './node_modules/flowbite/**/*.js',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Segoe UI', 'Helvetica Neue', 'Arial', 'sans-serif'],
            },
        },
    },
    plugins: [daisyui, flowbite],
    daisyui: {
        themes: ['night'],
        darkTheme: 'night',
    },
};
