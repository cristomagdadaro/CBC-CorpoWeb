Developer Deck — Developer's Page

This small plugin helps you keep developer notes and scan the codebase for common features (shortcodes, CPTs, taxonomies, sidebars, enqueues).

Usage

- Admin UI: Tools → Developer's Page
  - Run "Run Codebase Scan" to collect matches from plugins/themes (shortcodes, CPTs, taxonomies, sidebars, enqueues, dynamic_sidebar uses).
  - Add developer notes and mark them as "Visible (front-end)" to show on the Developer's Page.

- Shortcode: [devs_developer_page]
  - Place on a page to render public notes and a lightweight scan summary.

Notes

- The scanner is intentionally lightweight and only looks through `wp-content/plugins` and `wp-content/themes` for `.php`, `.inc`, `.tpl` files. It returns matches of patterns and the filename.
- Keep sensitive notes private (don't check them as public).

Development

- The plugin stores notes in the option `developer_deck_notes` as an array.
- To extend the scanner, modify `scan_project()` in `developer-deck.php`.
