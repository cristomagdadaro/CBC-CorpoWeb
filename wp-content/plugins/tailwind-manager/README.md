a# Tailwind Manager (WordPress Plugin)

Manage a curated Tailwind CSS configuration from inside WordPress and serve a compiled `tailwind.css` file (PostCSS/CLI build) instead of relying on the **`cdn.tailwindcss.com`** runtime CDN script (which triggers the production warning you saw).

> This plugin eliminates the warning: 
> `cdn.tailwindcss.com should not be used in production. To use Tailwind CSS in production, install it as a PostCSS plugin or use the Tailwind CLI.`

---
## Features
- Admin screen: Appearance → Tailwind Manager (or top-level menu “Tailwind Manager”) to change:
  - Primary / Secondary / Accent colors
  - Dark mode toggle (class vs media)
  - Base font family
- Generates / updates a JSON config (`tailwind-custom.json`) and a scaffolded `tailwind.config.cjs`.
- Provides build tooling (Tailwind CLI + PostCSS + Autoprefixer) via `package.json`.
- Automatically enqueues compiled `dist/tailwind.css` (versioned via filemtime) on the front‑end.
- Attempts to dequeue scripts that reference `cdn.tailwindcss.com` if they were enqueued (best effort – remove hard‑coded `<script>` tags manually from theme templates).
- REST endpoint: `GET /wp-json/tailwind-manager/v1/settings` returns current settings.
- Shortcode: `[tailwind_color name="primary"]` outputs a configured color value.

---
## Directory Layout
```
wp-content/plugins/tailwind-manager/
  tailwind-manager.php
  tailwind.config.cjs        (auto-generated if missing)
  postcss.config.cjs         (auto-generated if missing)
  tailwind-custom.json       (dynamic settings store)
  package.json               (build scripts)
  src/input.css              (source – you add layers/utilities here)
  dist/tailwind.css          (compiled output – created after build)
  README.md
```

---
## Installation
1. Place the `tailwind-manager` folder in `wp-content/plugins/` (already done if you see this file).
2. Activate **Tailwind Manager** in the WP Plugins screen.
3. Navigate to **Tailwind Manager** menu to view settings.

---
## Build Prerequisites
- Node.js 16+ recommended.
- (Optional) A persistent object cache or page cache helps performance but is not required.

---
## Initial Build (Windows CMD Example)
Open a terminal (Command Prompt / PowerShell) and run:
```bat
cd D:\CBC-Apps\CBC-CorpoWeb\wp-content\plugins\tailwind-manager
npm install
npm run build
```
This creates: `dist/tailwind.css`.

### Development (watch mode)
```bat
npm run dev
```
The watch process will rebuild on changes to `src/input.css` or any file referenced in `content` globs of `tailwind.config.cjs`.

> If your theme lives outside the provided relative globs, edit `content` in `tailwind.config.cjs` to include its paths.

---
## Removing the CDN Warning
Find and remove any of the following from your theme (likely in `header.php`, `functions.php`, or a block template):
```html
<script src="https://cdn.tailwindcss.com"></script>
```
If enqueued via `wp_enqueue_script()`, deregister or delete that code. After removal, only the compiled stylesheet from this plugin will provide Tailwind utilities.

---
## Customizing Tailwind
### Via Admin (Simple Tokens)
Change colors, dark mode, or font → Save → Re-run build:
```bat
npm run build
```
The plugin regenerates `tailwind-custom.json`; build picks it up.

### Extending Tailwind (Advanced)
Edit `tailwind.config.cjs` directly for:
- Additional color scales
- Safelist patterns
- Plugins (e.g., `@tailwindcss/typography`)

Example augmentation:
```js
// tailwind.config.cjs (inside module.exports)
plugins: [
  require('@tailwindcss/typography'),
  require('@tailwindcss/forms'),
],
// Add a safelist
safelist: [
  'prose', 'prose-lg', 'btn-primary',
  { pattern: /grid-cols-([1-9]|1[0-2])/ },
],
```
Then rebuild.

### Adding Custom Utilities
Edit `src/input.css`:
```css
@tailwind base;
@tailwind components;
@tailwind utilities;

@layer utilities {
  .btn-primary {
    @apply bg-primary text-white font-medium px-4 py-2 rounded hover:bg-primary/90 transition;
  }
}
```
Rebuild to use `.btn-primary`.

---
## Using the Shortcode
In a post or page editor:
```
[tailwind_color name="accent"]
```
Outputs the hex code defined for `accent`.

---
## REST API
`GET /wp-json/tailwind-manager/v1/settings`
Returns JSON payload:
```json
{
  "primary": "#1f5d2b",
  "secondary": "#1d3b53",
  "accent": "#a2b917",
  "darkMode": 0,
  "fontFamily": "Inter, system-ui, sans-serif"
}
```

---
## Dark Mode Strategy
In Admin you toggle “Dark Mode”.
- Enabled → `darkMode: 'class'` (you add `class="dark"` to `<html>` or `<body>`).
- Disabled → falls back to `'media'` (prefers-color-scheme) for minimal config.
After toggling: rebuild.

---
## Updating the Primary Color in CSS
Inside a custom component file (or `input.css`):
```css
@layer base {
  :root { --color-primary: theme('colors.primary'); }
}
```
Replace or supplement the existing root variable usage.

---
## Versioning / Cache Busting
The plugin automatically versions the stylesheet using `filemtime(dist/tailwind.css)` so browsers get a new version after each build. If using an external caching layer, purge it after builds.

---
## Common Issues
| Issue | Cause | Fix |
|-------|-------|-----|
| Still seeing CDN warning | Script tag still present in theme | Remove `<script src="https://cdn.tailwindcss.com">` everywhere |
| Styles missing | Build not run | Run `npm run build` again |
| Dark utilities not applied | Wrong strategy | Ensure dark mode toggled on & `<html class="dark">` or remove toggle for media mode |
| Custom classes purged | Not in content scan | Add their usage or safelist entry in `tailwind.config.cjs` |
| Font not applied | Invalid font stack | Confirm font loaded (Google Fonts / local) before using | 

---
## Extending Further (Ideas)
- Add more editable tokens (spacing scale, border radius).
- Generate a downloadable design token JSON.
- Add a live preview iframe in the admin.
- Provide a WP-CLI command: `wp tailwind build`.
- Integrate with pattern library or block styles.

---
## Uninstall
Deactivate & delete the plugin. Remove `/tailwind-manager/` folder. Optionally remove transient build artifacts or tokens if you exported them elsewhere.

---
## License
MIT — customize freely.

---
## Quick Reference Commands
```bash
# Install deps (first time)
npm install
# Production build
npm run build
# Watch mode
npm run dev
# Clean built file
npm run clean
```

---
## Support
If you need to add additional configuration fields (e.g., spacing scale editor) you can extend the admin form and mirror new keys in `tailwind-custom.json` consumption logic in `tailwind.config.cjs`.

Enjoy your production-safe Tailwind setup! 🚀

