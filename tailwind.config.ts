import type { Config } from 'tailwindcss';
import defaultTheme from 'tailwindcss/defaultTheme';

const token = (name: string) => `rgb(var(--color-${name}) / <alpha-value>)`;

export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            boxShadow: {
                card: '0 1px 2px rgb(19 21 21 / 0.04), 0 32px 64px -32px rgb(19 21 21 / 0.25)',
            },
            colors: {
                background: token('background'),
                foreground: token('foreground'),
                surface: {
                    DEFAULT: token('surface'),
                    foreground: token('surface-foreground'),
                },
                muted: {
                    DEFAULT: token('muted'),
                    foreground: token('muted-foreground'),
                },
                border: token('border'),
                input: token('input'),
                ring: token('ring'),

                // Escala de marca: Pearl Aqua (300) e Verdigris (500) compartilham o mesmo matiz.
                primary: {
                    DEFAULT: token('primary'),
                    foreground: token('primary-foreground'),
                    50: '#E9FCF7',
                    100: '#CDF7EE',
                    200: '#A6EFE1',
                    300: '#7DE2D1',
                    400: '#4FBFAD',
                    500: '#339989',
                    600: '#1B7C6F',
                    700: '#136358',
                    800: '#0F4D44',
                    900: '#0D3B34',
                    950: '#05221E',
                },
                secondary: {
                    DEFAULT: token('secondary'),
                    foreground: token('secondary-foreground'),
                },
                accent: {
                    DEFAULT: token('accent'),
                    foreground: token('accent-foreground'),
                },

                // Escala neutra: Snow (50), Graphite (900) e Onyx (950).
                neutral: {
                    50: '#FFFAFB',
                    100: '#F0F0ED',
                    200: '#E1E1DE',
                    300: '#CECECA',
                    400: '#A5A5A1',
                    500: '#80807C',
                    600: '#636460',
                    700: '#4D4D49',
                    800: '#3B3B37',
                    900: '#2B2C28',
                    950: '#131515',
                },

                success: {
                    DEFAULT: token('success'),
                    foreground: token('success-foreground'),
                },
                warning: {
                    DEFAULT: token('warning'),
                    foreground: token('warning-foreground'),
                },
                danger: {
                    DEFAULT: token('danger'),
                    foreground: token('danger-foreground'),
                },
                info: {
                    DEFAULT: token('info'),
                    foreground: token('info-foreground'),
                },
            },
        },
    },
    plugins: [],
} satisfies Config;
