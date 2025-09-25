const fs = require('fs');
const path = require('path');
const cfgPath = path.resolve(__dirname, 'tailwind-custom.json');
let custom = {
    color: [
        { 'primary':'#1F5D2B' },
        { 'secondary': '#A2B917' },
    ]
}
try { custom = JSON.parse(fs.readFileSync(cfgPath, 'utf8')); } catch (e) {}
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
