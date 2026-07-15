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
            colors: {
                primary: '#2563EB',
                'primary-hover': '#1D4ED8',
                'primary-tint': '#EFF6FF',
                accent: '#F97316',
                'accent-hover': '#EA580C',
                'accent-tint': '#FFEDD5',
                secondary: '#1F212B',
                tertiary: '#6B7280',
                neutral: '#F7F8FB',
                surface: '#FFFFFF',
                'on-surface': '#1F212B',
                border: '#E5E7EB',
                error: '#C62828',
            },
            fontFamily: {
                sans: ['Plus Jakarta Sans', 'Inter', ...defaultTheme.fontFamily.sans],
                display: ['Plus Jakarta Sans', 'sans-serif'],
            },
            spacing: {
                '18': '18px',
                '26': '26px',
                '38': '38px',
                '140': '140px',
            },
            borderRadius: {
                'sm': '4px',
                'md': '8px',
                'lg': '16px',
                'xl': '24px',
            },
            fontSize: {
                'headline-display': ['40px', { lineHeight: '48px', fontWeight: '700', letterSpacing: '0px' }],
                'headline-lg': ['31px', { lineHeight: '40px', fontWeight: '500', letterSpacing: '0px' }],
                'headline-md': ['24px', { lineHeight: '29px', fontWeight: '500', letterSpacing: '0px' }],
                'headline-sm': ['18px', { lineHeight: '22px', fontWeight: '500', letterSpacing: '0px' }],
                'body-lg': ['16px', { lineHeight: '24px', fontWeight: '500', letterSpacing: '0px' }],
                'body-md': ['14px', { lineHeight: '21px', fontWeight: '500', letterSpacing: '0px' }],
                'body-sm': ['12px', { lineHeight: '18px', fontWeight: '500', letterSpacing: '0px' }],
                'label-lg': ['14px', { lineHeight: '20px', fontWeight: '500', letterSpacing: '0px' }],
                'label-md': ['12px', { lineHeight: '16px', fontWeight: '500', letterSpacing: '0px' }],
                'label-sm': ['12px', { lineHeight: '16px', fontWeight: '400', letterSpacing: '0px' }],
                'nav-link': ['14px', { lineHeight: '20px', fontWeight: '500', letterSpacing: '0px' }],
            },
        },
    },

    plugins: [forms],
};
