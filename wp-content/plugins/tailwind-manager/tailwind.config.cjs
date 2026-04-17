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
const extendedColors = Object.assign({}, custom.colors || {}, {
    'cbc-yellow-green': (custom.colors && (custom.colors['cbc-yellow-green'] || custom.colors.secondary)) || '#A2B917',
});
const darkMode = custom.darkMode ? 'class' : 'media';

const CONTENT_GLOBS = [
    '../../themes/**/*.php',
    '../../themes/**/*.twig',
    '../../themes/**/*.js',
    '../../themes/**/*.jsx',
    '../../themes/**/*.ts',
    '../../themes/**/*.tsx',
    '../../themes/**/*.vue',
    '../../themes/**/*.html',
    '../../plugins/**/*.php',
    './src/**/*.{js,ts,jsx,tsx,php,html}',
    '../../wp-admin/**/*.php',
    '../../wp-includes/**/*.php'
];

const dedupeSafelist = (entries) => {
    const seen = new Set();

    return entries.filter((entry) => {
        const key = typeof entry === 'string'
            ? `class:${entry}`
            : `pattern:${entry.pattern ? entry.pattern.toString() : JSON.stringify(entry)}`;

        if (seen.has(key)) {
            return false;
        }

        seen.add(key);
        return true;
    });
};

const walkerSafelist = [
    'group',
    'group-hover:flex',
    'group-hover:block',
    'group-hover:right-0',
    'hidden',
    'top-full',
    'top-0',
    'top-2',
    'top-3',
    'left-0',
    'right-[-10rem]',
    'right-[-4rem]',
    'pt-1',
    'pt-2',
    'mb-2',
    'ml-2',
    '-mr-0.5'
];

const layoutSafelist = [
    'relative',
    'absolute',
    'fixed',
    'block',
    'inline-block',
    'flex',
    'inline-flex',
    'grid',
    'inline-grid',
    'flex-row',
    'flex-col',
    'flex-row-reverse',
    'flex-col-reverse',
    'flex-wrap',
    'flex-grow',
    'shrink-0',
    'items-start',
    'items-center',
    'items-end',
    'justify-start',
    'justify-center',
    'justify-between',
    'justify-end',
    'justify-items-center',
    'place-items-center',
    'content-center',
    'list-none',
    'select-none',
    'sm:block',
    'sm:hidden',
    'sm:flex-row',
    'sm:grid-cols-2',
    'sm:items-center',
    'sm:mt-0',
    'sm:rounded-lg',
    'sm:rounded-l',
    'md:flex-col',
    'md:justify-start',
    'md:col-span-1',
    'md:grid-cols-5',
    'col-span-2',
    'grid-cols-3'
];

const sizingSafelist = [
    'w-full',
    'min-w-full',
    'w-fit',
    'w-10',
    'w-16',
    'w-32',
    'w-34',
    'w-48',
    'md:w-20',
    'md:w-48',
    'md:w-[4rem]',
    'md:w-[12rem]',
    'md:w-[22rem]',
    'lg:w-[15rem]',
    'w-[9rem]',
    'w-[14rem]',
    'w-[25%]',
    'w-[50%]',
    'w-[300px]',
    'w-[600px]',
    'h-4',
    'h-10',
    'h-16',
    'h-48',
    'h-64',
    'h-[6rem]',
    'h-[12rem]',
    'h-[16rem]',
    'h-[100vh]',
    'h-[300px]',
    'h-full',
    'min-h-screen',
    'min-h-[2.5em]',
    'max-h-full',
    'max-h-[2.5em]',
    'max-h-[5.375rem]',
    'max-h-[50%]',
    'max-h-[50vh]',
    'max-w-4xl',
    'max-w-lg',
    'max-w-[1200px]',
    'md:h-64',
    'md:h-[9rem]',
    'md:h-[16rem]',
    'sm:h-16',
    'lg:h-24',
    'lg:h-[12rem]'
];

const spacingSafelist = [
    'p-1',
    'p-2',
    'p-3',
    'p-4',
    'p-6',
    'p-8',
    'p-10',
    'px-1',
    'px-2',
    'px-3',
    'px-4',
    'px-5',
    'px-6',
    'py-1',
    'py-2',
    'py-2.5',
    'py-3',
    'py-6',
    'pl-4',
    'pl-6',
    'pr-2',
    'pr-4',
    'pr-6',
    'pt-5',
    'pt-6',
    'border-b',
    'border-b-2',
    'border-t',
    'mx-2',
    'mx-5',
    'mx-auto',
    'my-5',
    'm-4',
    '!m-0',
    'mt-1',
    'mt-2',
    'mt-3',
    'mb-1',
    'mb-4',
    'mb-6',
    'gap-3',
    'gap-5',
    'gap-6',
    'sm:gap-2',
    'md:gap-5',
    'md:gap-6',
    'space-x-2',
    'space-y-4',
    'space-y-6',
    'space-y-10',
    'md:space-y-10',
    'md:space-y-12',
    'divide-y',
    'divide-gray-200'
];

const backgroundAndColorSafelist = [
    'bg-white',
    'bg-white/50',
    'bg-[#ffffff]',
    'bg-[#1f5d2b]',
    'bg-gray-50',
    'bg-gray-100',
    'bg-red-500',
    'bg-yellow-100',
    'bg-blue-100',
    'bg-indigo-600',
    'bg-opacity-50',
    'bg-gradient-to-r',
    'bg-gradient-to-l',
    'bg-gradient-to-t',
    'bg-gradient-to-b',
    'bg-gradient-to-tr',
    'bg-gradient-to-bl',
    'bg-[#a2b917]',
    'from-transparent',
    'from-white',
    'from-[#1f5d2b]',
    'from-[#a2b917]',
    'to-white/90',
    'to-[#a2b917]',
    'to-[#1f5d2b]',
    'via-white/70',
    'text-white',
    '!text-white',
    '!text-xs',
    '!text-sm',
    'text-[#000000]',
    'text-[#1f5d2b]',
    'text-gray-100',
    'text-gray-300',
    'text-gray-500',
    'text-gray-700',
    'text-gray-900',
    'text-red-500',
    'text-red-600',
    'text-green-600',
    'text-yellow-600',
    'text-yellow-700',
    'text-blue-700',
    'text-indigo-600',
    'hover:bg-cbc-yellow-green',
    'hover:bg-[#a2b917]',
    'hover:bg-gray-50',
    'hover:bg-indigo-700',
    'hover:bg-transparent',
    'hover:text-[#1f5d2b]',
    'hover:text-[#ffffff]',
    'hover:text-gray-100',
    'hover:text-green-900',
    'hover:text-yellow-900',
    'hover:text-red-900',
    'hover:bg-[#a2b917]',
    'opacity-50',
    'opacity-60',
    'hover:opacity-100'
];

const typographySafelist = [
    'text-[0.7rem]',
    'text-[9px]',
    'text-xs',
    'text-sm',
    'text-base',
    'text-lg',
    'text-3xl',
    'text-center',
    'text-left',
    'text-right',
    'text-ellipsis',
    'text-normal',
    'text-subtitle',
    'text-wrap',
    'font-thin',
    'font-medium',
    'font-semibold',
    'font-bold',
    'leading-5',
    'leading-[0.9rem]',
    '!leading-none',
    '!leading-tight',
    'tracking-tight',
    'tracking-wide',
    'tracking-wider',
    'uppercase',
    'lowercase',
    'italic',
    'whitespace-nowrap',
    '!whitespace-nowrap',
    'whitespace-normal',
    'whitespace-pre-wrap',
    'whitespace-break-spaces',
    'line-clamp-2',
    'md:line-clamp-3',
    'md:text-sm',
    'md:text-base',
    'md:text-lg',
    'md:text-2xl',
    'sm:text-xl',
    'sm:text-2xl',
    'lg:text-xl',
    'lg:text-3xl',
    'md:font-extrabold',
    'md:leading-1',
    'md:!leading-2',
    'md:!leading-3',
    'md:!leading-4',
    'md:!leading-5',
    'md:!leading-6',
    '!font-dancing',
    '!font-lato',
    '!font-montserrat',
    '!font-sans',
    '!font-spartan'
];

const borderAndShadowSafelist = [
    'border',
    'border-transparent',
    'border-gray-100',
    'border-gray-200',
    'border-gray-300',
    'border-yellow-400',
    'border-blue-400',
    'border-[#a2b917]',
    'rounded',
    'rounded-sm',
    'rounded-md',
    'rounded-lg',
    'rounded-2xl',
    'rounded-t',
    'shadow',
    'shadow-sm',
    'shadow-md',
    'shadow-lg',
    'shadow-inner',
    'shadow-[#1f5d2b]/40',
    'drop-shadow-md',
    'no-underline',
    'hover:border-gray-300',
    'hover:border-[#1f5d2b]',
    'hover:shadow'
];

const interactionSafelist = [
    'transition',
    'transform',
    'duration-100',
    'duration-150',
    'duration-200',
    'duration-300',
    'duration-400',
    'duration-500',
    'duration-600',
    'duration-700',
    'duration-800',
    'duration-900',
    'ease-in',
    'ease-out',
    'ease-in-out',
    'scale-75',
    'md:scale-100',
    'hover:scale-105',
    'hover:scale-x-110',
    'focus:scale-x-110',
    'active:scale-x-110',
    'hover:rotate-1',
    'focus:outline-none',
    'focus:text-gray-700',
    'focus:border-gray-300',
    'focus:border-indigo-500',
    'focus:ring-2',
    'focus:ring-offset-2',
    'focus:ring-indigo-500'
];

const mediaAndUtilitySafelist = [
    'overflow-hidden',
    'overflow-x-auto',
    'overflow-y-scroll',
    'object-cover',
    'object-center',
    'object-contain',
    'cursor-pointer',
    'cursor-zoom-in',
    'backdrop-blur-sm',
    'backdrop-blur-md',
    'wrap',
    'aspect-[5040/1692]',
    'aspect-[16/9]',
    'aspect-4/3',
    'aspect-3/2',
    'aspect-16/9',
    'z-[1]',
    'z-[10]',
    'z-[46]',
    'z-[99]',
    'z-[100]',
    'z-[999]'
];

const arbitraryVariantSafelist = [
    '[&>li]:flex',
    '[&>li]:flex-wrap',
    '[&>li]:whitespace-nowrap',
    '[&>li>span]:mr-1',
    '[&>li>a]:!text-white',
    '[&>li>a:hover]:!text-[#a2b917]',
    '[&>p]:text-gray-600',
    '[&>img]:rounded-lg',
    '[&>figure>img]:w-[5rem]',
    '[&>figure>img]:md:w-full',
    '[&>g-recaptcha]:flex',
    '[&>g-recaptcha]:items-center',
    '[&>g-recaptcha]:justify-center',
    '[&>div>ul>li]:text-[11.2px]',
    'group-hover:[text-shadow:1px_1px_0_white,-1px_1px_0_white,1px_-1px_0_white,-1px_-1px_0_white,0_2px_0_white,2px_0_0_white,-2px_0_0_white,0_-2px_0_white]'
];

const jsStateSafelist = [
    'cbc-is-scrolled',
    'is-visible'
];

const safelist = dedupeSafelist([
    ...walkerSafelist,
    ...layoutSafelist,
    ...sizingSafelist,
    ...spacingSafelist,
    ...backgroundAndColorSafelist,
    ...typographySafelist,
    ...borderAndShadowSafelist,
    ...interactionSafelist,
    ...mediaAndUtilitySafelist,
    ...arbitraryVariantSafelist,
    ...jsStateSafelist,
    { pattern: /bg-gradient-to-(r|l|t|b|tr|tl|br|bl)/ }
]);

module.exports = {
    darkMode,
    content: CONTENT_GLOBS,
    safelist,
    variants: {
        extend: {
            opacity: ['group-hover'],
            visibility: ['group-hover'],
            transform: ['group-hover'],
        }
    },
    theme: {
        extend: {
            colors: Object.assign({}, extendedColors, {
                'biotech-primary': '#2e7d32',
                'biotech-dark': '#1b5e20',
                'biotech-light': '#4caf50',
                'biotech-accent': '#76ff03',
                'biotech-dark-bg': '#212121',
                'biotech-slate': '#64748b'
            }),
            spacing: {
                'shell-x-sm': '1rem',
                'shell-x-md': '1.5rem',
                'shell-x-lg': '3rem',
                'shell-x-xl': '5rem',
                'section-y-sm': '3.5rem',
                'section-y-lg': '5rem'
            },
            borderRadius: {
                xl: 'calc(var(--radius) + 4px)',
                lg: 'var(--radius)',
                md: 'calc(var(--radius) - 2px)',
                sm: 'calc(var(--radius) - 4px)',
                xs: 'calc(var(--radius) - 6px)'
            },
            boxShadow: {
                xs: '0 1px 2px 0 rgb(0 0 0 / 0.05)',
                card: '0 10px 24px rgba(0, 0, 0, 0.10)',
                cardHover: '0 18px 40px rgba(0, 0, 0, 0.16)',
                glow: '0 0 20px rgba(118, 255, 3, 0.3)'
            },
            keyframes: {
                shimmer: {
                    '0%': { backgroundPosition: '-200% 0' },
                    '100%': { backgroundPosition: '200% 0' }
                },
                'fade-in': {
                    from: { opacity: '0' },
                    to: { opacity: '1' }
                },
                'slide-up': {
                    from: { opacity: '0', transform: 'translateY(20px)' },
                    to: { opacity: '1', transform: 'translateY(0)' }
                }
            },
            animation: {
                shimmer: 'shimmer 1.5s linear infinite',
                'fade-in': 'fade-in 0.5s ease-out forwards',
                'slide-up': 'slide-up 0.6s ease-out forwards'
            },
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
