import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import { createRequire } from 'module';

const require = createRequire(import.meta.url);

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                emerald: {
                    50: '#EEF5F3',
                    100: '#E2ECE9',
                    200: '#C5DCD6',
                    300: '#9CC6BC',
                    400: '#64A497',
                    500: '#0F9F82',
                    600: '#0F9F82',
                    700: '#087F6A',
                    800: '#066352',
                    900: '#054B3E',
                    950: '#02241E',
                },
                slate: {
                    50: '#F8FAFA',
                    100: '#F1F5F4',
                    200: '#E2ECE9',
                    300: '#CAD7D4',
                    400: '#A1B5B1',
                    500: '#647875',
                    600: '#4D615E',
                    700: '#3A4C49',
                    800: '#2A3B38',
                    900: '#17332F',
                    950: '#0D1E1B',
                }
            }
        },
    },

    plugins: [forms],
};
