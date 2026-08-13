# DA-CBC Corporate Website

Department of Agriculture - Crop Biotechnology Center corporate website built on WordPress with customized themes and a set of CBC-specific plugins.

## Current Status

This repository is **not yet ready for full public launch or full-blown deployment** as of `2026-04-25`.

The codebase has improved since the original audit. Several important repo-level mitigations are already present:

- the public deploy script has been removed from the repo
- committed Google OAuth JSON files have been deleted
- `cbc-form-manager` is now the default owner for overlapping public form shortcodes
- sensitive uploads now use private storage patterns in `cbc-client-engagement` and `cbc-form-manager`
- AI and games endpoints now have stronger permission and abuse controls

But launch is still blocked by core config and deployment posture:

- `wp-config.php` still contains tracked secrets and environment-specific values
- `wp-config.php` currently defines `ABSPATH` with `_DIR_` instead of `__DIR__`
- `WP_HOME` / `WP_SITEURL` are still derived from `HTTP_HOST` rather than production-safe environment config
- root `readme.html` and `license.txt` are still present and not yet denied by `.htaccess`
- several mitigated items still need staging verification before they can be treated as resolved

Use [docs/LAUNCH_READINESS_AUDIT.md](docs/LAUNCH_READINESS_AUDIT.md) and [docs/VULNERABILITY_TRACKER.md](docs/VULNERABILITY_TRACKER.md) as the source of truth for launch decisions.

## Stack

- WordPress core: `6.9.1`
- Primary customized theme in this repo: `wp-content/themes/modern-gwt-wordpress`
- Other themes present in the repo:
  - `gwt-wordpress-26.0.0`
  - `modern-biotech-wordpress`
  - `customcbc`
- Key first-party plugins:
  - `cbc-ai-messenger`
  - `cbc-branded-redirect-manager`
  - `cbc-calendar-announcements`
  - `cbc-client-engagement`
  - `cbc-form-manager`
  - `cbc-media-archive`
  - `cbc-newsletter`
  - `cbc-security-hardening`
  - `cbc-slider`
  - `post-metrics`
  - `tailwind-manager`
  - `wp-cbc-games`

## Repository Layout

```text
.
|-- wp-admin/                  WordPress core admin files
|-- wp-includes/               WordPress core includes
|-- wp-content/
|   |-- themes/
|   |   `-- modern-gwt-wordpress/   Primary customized presentation layer
|   |-- plugins/
|   |   |-- cbc-ai-messenger/
|   |   |-- cbc-client-engagement/
|   |   |-- cbc-form-manager/
|   |   |-- cbc-calendar-announcements/
|   |   |-- cbc-branded-redirect-manager/
|   |   |-- cbc-media-archive/
|   |   |-- cbc-newsletter/
|   |   |-- cbc-security-hardening/
|   |   |-- cbc-slider/
|   |   |-- post-metrics/
|   |   |-- tailwind-manager/
|   |   `-- wp-cbc-games/
|   `-- uploads/               Runtime media uploads
|-- wp-config.php              WordPress bootstrap config
|-- .htaccess                  Apache routing and security header rules
`-- docs/                      Audit, tracking, and engineering guidance
```

## Architecture

### Presentation

- The active presentation layer is theme-driven.
- `modern-gwt-wordpress` owns templates, layout, navigation, and display helpers.
- `modern-biotech-wordpress` is also present and has local customizations, but it should not be treated as the main architecture reference unless it is the active launch theme.

### Business Features

- CBC-specific business logic primarily lives in plugins, not in the theme.
- Plugins cover AI chat, events and announcements, forms, newsletters, media archive, sliders, branded links, metrics, security hardening, and games.

### Data Storage Patterns

Current storage patterns include:

- custom post types for submissions, logs, and content-like records
- custom database tables for newsletters, redirects, calendars, and game leaderboards
- private filesystem storage for sensitive uploaded documents

### Design Pattern Direction

The codebase is mixed:

- classic procedural WordPress code
- class-based feature plugins
- a stronger layered approach in `cbc-form-manager`

The preferred direction going forward is:

- themes for presentation
- plugins for business capabilities
- explicit permission models on every public endpoint
- private storage for sensitive uploads
- dedicated capabilities for PII-heavy admin areas
- one canonical form platform instead of overlapping implementations

## Custom Component Summary

### `cbc-ai-messenger`

- public chatbot UI via shortcode or footer injection
- calls external LLM providers
- stores chat logs and contact details
- now uses reCAPTCHA and throttling, but still requires secret externalization and staging verification

### `cbc-client-engagement`

- appointment, feedback, internship, and events workflows
- uses `admin-post.php` form handling
- now stores internship files in private storage outside the public uploads path

### `cbc-form-manager`

- structured form platform
- auto-discovers modules from `presentation/`
- uses application/domain/infrastructure layering
- current canonical owner for `cbc_appointment_form`, `cbc_feedback_form`, and `cbc_internship_form`

### `cbc-calendar-announcements`

- admin-managed calendar, event, and announcement storage
- uses a custom table and list-table style admin views

### `cbc-branded-redirect-manager`

- shortlink creation and redirect/click tracking
- QR code generation and public redirect layer

### `tailwind-manager`

- Tailwind settings management and build output wiring
- should be treated carefully during release packaging and admin-hardening review

### `wp-cbc-games`

- quiz, scramble, and memory game features
- leaderboard stored in a custom table
- now uses stronger permission controls and anti-forgery measures, but should still be treated as promotional rather than authoritative

## Local Development

### Minimum assumptions

- PHP available on the machine
- a WordPress-compatible web server
- MySQL or MariaDB available

### Important caution

Do not use the current tracked `wp-config.php` as a production-ready template.

Before launch:

- move secrets out of the tracked repo
- replace local/default DB settings
- fix the `ABSPATH` bootstrap typo
- validate startup in a production-like environment

### Useful checks

```powershell
php -l wp-config.php
php -l wp-content\plugins\cbc-ai-messenger\cbc-ai-messenger.php
php -l wp-content\plugins\cbc-client-engagement\cbc-client-engagement.php
php -l wp-content\plugins\cbc-form-manager\cbc-form-manager.php
```

### Tailwind manager

If `tailwind-manager` is used:

```powershell
cd wp-content\plugins\tailwind-manager
npm install
npm run build
```

## Security Baseline

Before release, confirm all of the following:

- rotate and remove committed secrets from the repo and from server history where possible
- remove or hard-restrict any deployment or debug endpoints
- move environment-specific configuration out of tracked files
- fix the WordPress bootstrap path in `wp-config.php`
- verify HTTPS and reverse-proxy behavior in the real hosting stack
- replace broad role access for PII-heavy areas with dedicated capabilities
- ensure private document uploads are not directly web-accessible
- deny version-disclosure files like `readme.html` and `license.txt`
- decide whether public REST is allowed, then align theme and plugin behavior
- verify one form system is the source of truth

## Launch Checklist

- Close the `OPEN` items in [docs/VULNERABILITY_TRACKER.md](docs/VULNERABILITY_TRACKER.md)
- Re-test every `MITIGATED` item in staging before marking it `RESOLVED`
- Smoke test public pages, forms, AI chat, games, metrics, newsletter, redirects, and event pages
- Verify HTTPS, cookies, CSP behavior, and login protections in staging
- Confirm role-based access for editors vs administrators
- Rebuild frontend assets where applicable
- Back up database and uploads before go-live

## Engineering Docs

- [docs/LAUNCH_READINESS_AUDIT.md](docs/LAUNCH_READINESS_AUDIT.md)
- [docs/VULNERABILITY_TRACKER.md](docs/VULNERABILITY_TRACKER.md)
- [.github/copilot-instructions.md](.github/copilot-instructions.md)

## Notes

- The repo is in a transition state: safer than the earlier audit, but not deployment-clean yet.
- Launch approval should be based on the tracker status plus staging evidence, not on code changes alone.
