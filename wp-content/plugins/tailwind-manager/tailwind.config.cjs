const fs = require('fs');
const path = require('path');
const cfgPath = path.resolve(__dirname, 'tailwind-custom.json');
let custom = {
    colors: {
        primary: '#1F5D2B',
        secondary: '#A2B917'
    }
};
try { custom = JSON.parse(fs.readFileSync(cfgPath, 'utf8')); } catch (e) {}
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
    // Ensure gradient utilities and arbitrary color orders are preserved
    'bg-gradient-to-r',
    'bg-gradient-to-l',
    'from-[#1f5d2b]',
    'to-[#a2b917]',
    'from-[#a2b917]',
    'to-[#1f5d2b]'
  ],
  theme: {
    extend: {
      colors: custom.colors || {},
      fontFamily: {
        sans: custom.fontFamily ? custom.fontFamily.split(',').map(s=>s.trim()) : ['Inter','system-ui','sans-serif']
      }
    }
  },
  plugins: []
};
