import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {

    darkMode: 'class', // 🔥 THIS IS THE FIX

    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
    ],

    safelist: [
        'grid-cols-2',
        'sm:grid-cols-3',
        'md:grid-cols-4',
        'lg:grid-cols-4',
        'xl:grid-cols-5',
    ],

    theme: {
        extend: {},
    },

    plugins: [forms],
};