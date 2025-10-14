const fs = require('fs');
const path = require('path');
const cfgPath = path.resolve(__dirname, 'tailwind-custom.json');
let custom = {
    colors: {
        primary: '#1F5D2B',
        secondary: '#A2B917'
    }
};
try {
    custom = JSON.parse(fs.readFileSync(cfgPath, 'utf8'));
} catch (e) {
}
// Backwards compatibility: if legacy `color` array form is used, normalize it into colors object
if (!custom.colors && Array.isArray(custom.color)) {
    custom.colors = Object.assign({}, ...custom.color);
}
const darkMode = custom.darkMode ? 'class' : 'media';
module.exports = {
    darkMode,
    content: [
        '../../themes/**/*.php',
        '../../themes/**/*.twig',
        '../../themes/**/*.js',
        '../../themes/**/*.jsx',
        '../../themes/**/*.ts',
        '../../themes/**/*.tsx',
        '../../themes/**/*.vue',
        '../../themes/**/*.html',
        './src/**/*.{js,ts,jsx,tsx,php,html}'
    ],
    safelist: [
        // Ensure gradient utilities and arbitrary color orders are preserved (explicit + patterns)
        'bg-gradient-to-r',
        'bg-gradient-to-l',
        'p-3',
        'p-6',
        'p-10',
        'pr-4',
        '!m-0',
        'mx-auto',
        'w-[600px]',
        'items-end',
        'col-span-2',
        'grid-cols-3',
        'gap-5',
        'w-[14rem]',
        'h-[12rem]',
        'md:w-[22rem]',
        'md:h-[16rem]',
        '!text-sm',
        'md:!text-lg',
        'mt-3',
        'sm:mt-0',
        'font-bold',
        '!whitespace-nowrap',
        '!leading-none',
        '!leading-tight',
        'mb-1',
        'text-sm',
        'md:text-lg',
        'text-normarl',
        'font-thin',
        'md:text-base',
        'font-semibold',
        'font-bold',
        'backdrop-blur-sm',
        'z-[999]',
        'text-[0.7rem]',
        'md:!leading-6',
        'text-red-500',
        'bg-red-500',
        'from-[#1f5d2b]',
        'to-[#a2b917]',
        '!font-spartan',
        '!font-dancing',
        '!font-lato',
        '!font-montserrat',
        '!font-sans',
        'from-[#a2b917]',
        'to-[#1f5d2b]',
        'hover:border-[#1f5d2b]',
        'leading-[0.9rem]',
        // Arbitrary sizes used for latest-posts thumbnails
        'w-[9rem]', 'h-[6rem]', 'md:w-[12rem]', 'md:h-[9rem]', 'lg:w-[15rem]', 'lg:h-[12rem]',
        'shrink-0', 'block', 'md:hidden', 'rounded-t', 'sm:rounded-l',
        // Broad patterns to keep any arbitrary hex gradient stops we might generate in PHP
        {pattern: /bg-gradient-to-(r|l|t|b|tr|tl|br|bl)/}
    ],
    theme: {
        extend: {
            colors: custom.colors || {},
            fontFamily: {
                dancing: ['"Dancing Script"', 'cursive'],
                lato: ['Lato', 'sans-serif'],
                spartan: ['"League Spartan"', 'sans-serif'],
                montserrat: ['Montserrat', 'sans-serif'],
                sans: custom.fontFamily ? custom.fontFamily.split(',').map(s => s.trim()) : ['Inter', 'system-ui', 'sans-serif']
            }
        }
    },
    plugins: []
};
