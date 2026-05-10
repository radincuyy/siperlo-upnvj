import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

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
                sans: ['Atkinson Hyperlegible', ...defaultTheme.fontFamily.sans],
                display: ['Literata', ...defaultTheme.fontFamily.serif],
            },
            colors: {
                // SIPERLO semantic tokens — mirror DESIGN.md frontmatter.
                // Prefer these over raw hex literals in templates.
                campus: {
                    green: '#155b32',
                    'green-deep': '#0f3f25',
                    gold: '#d7a82f',
                },
                ink: '#17201b',
                'muted-ink': '#5f6b60',
                'border-line': '#dbe2d9',
                'field-border': '#cfd8cf',
                paper: '#fbfcf7',
                panel: '#ffffff',
                'soft-green': '#edf4ec',
                'hover-green-surface': '#f4f7f0',
                'admin-note-surface': '#f7faf4',
            },
        },
    },

    plugins: [forms],
};
