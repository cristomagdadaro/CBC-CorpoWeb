# CBC Slider

A robust, accessible WordPress slider managed from the WP Admin sidebar (no block required). Add images or self‑hosted videos from the Media Library, plus per‑slide custom HTML overlays. Embed via shortcode or template tag.

Features:
- Manage sliders under WP Admin → CBC Slider
- Add images or MP4/WebM videos from the Media Library
- Optional poster image for videos
- Per‑slide custom HTML overlay, sanitized for safety
- Autoplay, delay, loop, pause on hover
- Arrows and dots navigation
- Object‑fit control, aspect ratio or custom height
- Keyboard navigation and basic touch/drag support

## Installation
1. Copy this folder `cbc-slider` into `wp-content/plugins/`.
2. In WP Admin, go to Plugins → CBC Slider → Activate.

## Create a slider
- Go to WP Admin → CBC Slider → Add New
- Give your slider a title
- In the “Slider Builder” meta box:
  - Use “Add Image(s)” / “Add Video(s)” to pick from the Media Library
  - For each slide you can:
    - Replace media (switch between image/video)
    - For videos, optionally set a poster image and/or a direct video URL
    - For images, set Alt text
    - Choose overlay position (top/bottom left/right, or center)
    - Enter Overlay HTML (only safe HTML/attrs allowed)
  - Configure options on the left: autoplay, delay, loop, pause on hover, object‑fit, aspect ratio or custom height, arrows/dots, and video flags (muted/loop/controls)
- Click Publish or Update

## Embed the slider
- Shortcode (pages, posts, widgets): `[cbc_slider id="123"]`
  - The list table shows the ID and shortcode for each slider.
- Template tag (in PHP theme files): `cbc_slider_render($id)` or `echo cbc_slider_render_by_id($id);`

## Notes
- Overlay HTML is sanitized using `wp_kses` with a friendly whitelist (div, span, p, h1–h6, ul/ol/li, a, img) including class/style/data-* and aria-* attributes. Developers can extend this via the filter `cbc_slider_allowed_overlay_html`.
- Videos on non‑active slides are paused; the active slide can autoplay (browser policies may restrict autoplay with sound).
- Frontend assets are enqueued only when a slider is rendered.

## Styling
Override styles from your theme as needed. Main classes:
- `.cbc-slider` (root)
- `.cbc-slider__viewport`, `.cbc-slider__track`, `.cbc-slider__slide`, `.cbc-slider__img`, `.cbc-slider__video`
- `.cbc-slider__overlay` + modifiers: `--top-left`, `--top-right`, `--bottom-left`, `--bottom-right`, `--center`
- `.cbc-slider__prev`, `.cbc-slider__next`, `.cbc-slider__dots`, `.cbc-slider__dot`

Aspect Ratio and Height:
- Uses CSS `aspect-ratio` via `--cbc-slider-aspect` (e.g., `16/9`).
- If a custom height is set (e.g., `480px` or `60vh`), it overrides the aspect ratio.

## Accessibility
- ARIA roles/labels for carousel and slides
- Keyboard support with arrow keys
- Focusable viewport and semantic buttons for controls

## Changelog
- 1.1.0: Admin CPT-based slider with shortcode/template tag (no block required)
- 1.0.0: Initial block-based version
