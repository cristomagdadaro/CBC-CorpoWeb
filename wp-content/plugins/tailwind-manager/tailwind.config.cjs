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
        'mx-auto',
        'gap-5',
        'font-bold',
        'leading-none',
        'from-[#1f5d2b]',
        'to-[#a2b917]',
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
                sans: custom.fontFamily ? custom.fontFamily.split(',').map(s => s.trim()) : ['Inter', 'system-ui', 'sans-serif']
            }
        }
    },
    plugins: []
};
