# DA-CBC Corporate Website

Department of Agriculture - Crop Biotechnology Center corporate website built on WordPress with a heavily customized Government Web Template theme and a set of CBC-specific plugins.

## Current Status

The repository-level mitigations for the launch audit have been applied, but launch readiness still depends on environment and staging verification work.

Before public launch, complete the verification steps in [docs/VULNERABILITY_TRACKER.md](docs/VULNERABILITY_TRACKER.md). The original highest-risk findings have been mitigated in code by:

- removing committed OAuth client files and shifting `wp-config.php` toward env-based secrets
- deleting the public deployment script from the web root
- removing forced `http://` URL generation in `wp-config.php`
- hardening AI, games, and form entrypoints with explicit permission and abuse controls
- moving sensitive upload flows to private storage patterns with controlled admin downloads
- making `cbc-form-manager` the canonical owner for the overlapping public form shortcodes

The remaining blockers are outside pure repository code changes:

- rotate any secrets that were previously committed
- confirm deleted/public endpoints are inaccessible in staging/production
- verify HTTPS/proxy behavior in the real hosting stack
- smoke test anonymous/public user journeys and admin role separation in staging

## Stack

- WordPress core: `6.9.1`
- Primary customized theme in this repo: `wp-content/themes/modern-gwt-wordpress`
- Supporting/legacy themes also present:
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
|-- wp-config.php              Environment-specific WordPress bootstrap config
|-- .htaccess                  Apache routing and security header rules
`-- docs/                      Audit, tracking, and engineering guidance
```

## Architecture

### Presentation

- The theme owns templates, layout, shared shortcodes, menu rendering, and some performance/security behavior.
- `modern-gwt-wordpress` mixes classic theme structure with utility-class-heavy markup and custom shortcode helpers.

### Business Features

- Most CBC-specific business logic lives in plugins, not in the theme.
- Plugins cover AI chat, events/announcements, forms, newsletters, media archive, sliders, branded links, metrics, security hardening, and games.

### Data Storage Patterns

- Some features use custom post types:
  - AI logs
  - appointments
  - feedback
  - internship submissions
  - form-manager submissions
  - sliders
  - media archive items
- Some features use custom database tables:
  - newsletter subscribers/templates
  - branded redirect links
  - calendar announcements
  - games leaderboard

### Design Pattern Direction

The codebase currently uses mixed patterns:

- Traditional WordPress procedural plugin/theme code
- Service-style classes in some plugins
- A stronger layered/DDD-style approach in `cbc-form-manager`

The long-term direction should be:

- theme for presentation only
- plugins for business capabilities
- REST/AJAX endpoints with explicit permissions and rate limits
- dedicated capabilities for sensitive admin data
- a single canonical form platform instead of overlapping implementations

## Custom Component Summary

### `cbc-ai-messenger`

- Public chatbot UI via shortcode or footer injection
- Calls external LLM providers
- Stores chat logs and contact details

### `cbc-client-engagement`

- Appointment, feedback, internship, and events workflows
- Uses `admin-post.php` form handling
- Stores user submissions as CPT entries

### `cbc-form-manager`

- More structured form platform
- Auto-discovers modules from `presentation/`
- Uses application/domain/infrastructure layering
- Canonical owner for `cbc_appointment_form`, `cbc_feedback_form`, and `cbc_internship_form`

### `cbc-calendar-announcements`

- Admin-managed calendar/event/announcement storage
- Uses a custom table and list-table style admin views

### `cbc-branded-redirect-manager`

- Shortlink creation and redirect/click tracking
- QR code generation and public redirect layer

### `tailwind-manager`

- Tailwind settings management and build output wiring
- Includes tracked build dependencies in the plugin folder

### `wp-cbc-games`

- Public quiz, scramble, and memory game shortcodes
- Leaderboard stored in a custom table

## Local Development

### Minimum assumptions

- PHP available on the machine
- WordPress-compatible web server
- MySQL/MariaDB available

### Important caution

Do not use production secrets from tracked files. This repository currently contains committed credential material that should be rotated and removed from version control before launch.

### Useful checks

```powershell
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
- remove or hard-restrict any deployment/debug endpoints
- move environment-specific configuration out of tracked files
- enforce HTTPS at the WordPress config and reverse-proxy layers
- replace broad `post` capabilities for PII-heavy CPTs with dedicated capabilities
- ensure private document uploads are not directly web-accessible
- decide whether public REST is allowed, then align theme and plugin behavior
- choose one form system as the source of truth

## Launch Checklist

- Complete the staging/production-like verification steps for each mitigated item in [docs/VULNERABILITY_TRACKER.md](docs/VULNERABILITY_TRACKER.md)
- Promote items from `MITIGATED` to `RESOLVED` only after that verification evidence exists
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

- The theme currently contains a global REST-authentication restriction. Some plugins simultaneously define public REST routes. That policy mismatch should be resolved before launch.
- The repo contains both `cbc-client-engagement` and `cbc-form-manager`, and both register overlapping form shortcodes. Treat that as an architectural conflict until one path is made canonical.
