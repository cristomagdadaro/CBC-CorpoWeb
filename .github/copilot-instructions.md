# Copilot Agentic Guide

## Purpose

This repository is a WordPress codebase for the DA-CBC corporate website. Copilot and other coding agents should optimize for safe changes, minimal regressions, and strong respect for WordPress conventions.

## What This Codebase Is

- a full WordPress deployment, not a small isolated app
- theme-led presentation with business features primarily owned by plugins
- mixed architecture:
    - classic procedural WordPress code
    - class-based feature plugins
    - one stronger layered/DDD-style plugin: `cbc-form-manager`

## Current Engineering Reality

Use these assumptions when making changes:

- `cbc-form-manager` is the preferred canonical owner for structured public forms
- `cbc-client-engagement` still exists and bridges into the form ecosystem, but its duplicate legacy shortcodes are disabled by default when form manager is active
- private-file handling patterns now exist and should be reused instead of WordPress media-library uploads for sensitive submissions
- repo-level launch hardening is still incomplete because `wp-config.php` currently contains tracked secrets and a bootstrap defect

Do not write code that assumes the site is already launch-clean.

## Architectural Rules

### Prefer this ownership model

- Theme:
    - templates
    - layout
    - navigation
    - display-only helpers
    - presentation shortcodes that do not own business data
- Plugins:
    - custom post types
    - custom tables
    - form handling
    - external integrations
    - AI, newsletter, metrics, redirects, games, security, events

### Avoid these patterns

- adding new business logic to theme files when it belongs in a plugin
- duplicating shortcode tags across plugins
- creating public REST routes without an explicit permission strategy
- storing secrets in tracked files
- using default broad post capabilities for sensitive submission data
- uploading sensitive documents into publicly accessible media paths
- introducing root-level utility PHP files as deploy/debug/admin entrypoints

## Canonical Design Principles

### Security first

Every public endpoint must have:

- capability logic or explicit public justification
- nonce or anti-automation protection where state changes occur
- rate limiting for expensive or abuse-prone operations
- sanitized inputs
- escaped outputs

Do not add `permission_callback => '__return_true'` by habit.

Do not expose deployment, debugging, or credential-handling code on public routes.

### One owner per concern

- Do not introduce another form system.
- Use `cbc-form-manager` as the preferred model for future structured forms unless the team explicitly retires it.
- If touching appointment, feedback, or internship flows, first verify whether the change belongs in:
    - `cbc-client-engagement`
    - `cbc-form-manager`
- If both currently handle the same concern, consolidate instead of extending both in parallel.

### Use WordPress APIs correctly

- Use `register_post_type`, `register_rest_route`, `add_action`, `add_filter`, `wp_insert_post`, `update_post_meta`, `$wpdb->prepare`, `wp_safe_redirect`, `check_admin_referer`, `wp_verify_nonce`, `sanitize_*`, and `esc_*`.
- Prefer `wp_remote_get` / `wp_remote_post` over raw cURL.
- Prefer `admin-post.php` or REST with correct auth over ad hoc root PHP entrypoints.

### Respect data sensitivity

Treat the following as sensitive:

- AI chat logs
- names, emails, phone numbers
- internship documents
- form attachments
- IP addresses and user agents
- newsletter subscriber data

When modifying data access:

- prefer dedicated capabilities over `edit_posts`
- minimize retention
- do not make sensitive documents directly public

## Repo-Specific Warnings

### `wp-config.php` is not a safe pattern to copy forward

Current repo state shows launch blockers in `wp-config.php`, including:

- tracked secrets and salts
- local/default DB credentials
- host-derived `WP_HOME` / `WP_SITEURL`
- a current bootstrap typo risk around `ABSPATH`

Do not add more environment-specific or secret-bearing config there unless it is part of moving values out to environment-driven configuration.

### Public REST policy still needs to stay explicit

The theme includes a global REST auth restriction in:

- `wp-content/themes/modern-gwt-wordpress/inc/function-disable_api.php`

Plugin behavior is now mixed:

- `wp-cbc-games` routes require logged-in access
- `cbc-ai-messenger` is intentionally public, but only with anti-abuse controls

Before adding or changing REST features:

1. decide whether the route is intentionally public
2. define its permission model explicitly
3. verify it does not conflict with theme-level restrictions

Do not assume a public route is reachable just because it registers.

### Form ownership is narrower than before, but still easy to regress

These shortcode names are historically sensitive:

- `cbc_appointment_form`
- `cbc_feedback_form`
- `cbc_internship_form`

`cbc-client-engagement` now disables its duplicate legacy ownership by default when `cbc-form-manager` is active.

Do not reintroduce overlapping shortcode registration unless the team intentionally re-architects the feature.

### Sensitive uploads must use the private storage pattern

Private upload handling already exists in:

- `wp-content/plugins/cbc-client-engagement/cbc-client-engagement.php`
- `wp-content/plugins/cbc-form-manager/src/Infrastructure/Storage/PrivateUploadManager.php`

Any new file-upload feature should define:

- allowed extensions
- allowed MIME types
- max size
- private storage outside the public uploads path when documents are sensitive
- authorized download handling
- retention/deletion rules

### AI endpoint cost and privacy risk remains real

AI features can create direct external cost and privacy exposure. Any AI-related change should include:

- abuse prevention
- request throttling
- provider error handling
- privacy review
- clear logging boundaries
- deliberate approval if the UX restricts accepted email domains or similar user input rules

## Coding Preferences

### PHP

- Keep code WordPress-compatible and readable.
- Prefer small named functions or methods over deeply nested anonymous callbacks when logic grows.
- Use strict sanitization on input and context-appropriate escaping on output.
- Keep query construction explicit and prepared.

### JavaScript

- Keep frontend scripts small and framework-free unless the existing module already uses a library.
- Use localized WordPress data instead of hard-coded URLs.
- Avoid introducing build systems unless the feature already has one.

### CSS

- Reuse theme or plugin patterns where already established.
- Do not introduce one-off visual systems that fight the current site styling.

## Suggested Change Workflow

1. Identify whether the change belongs to theme, plugin, root config, or server config.
2. Check for an existing plugin or module already owning that concern.
3. Verify security boundaries:
    - roles
    - nonce
    - REST or AJAX auth
    - upload handling
    - secret management
4. Make the smallest coherent change.
5. Update docs if the change affects:
    - architecture
    - public routes
    - deployment
    - security posture
6. Update `docs/VULNERABILITY_TRACKER.md` when resolving or changing the posture of a tracked issue.

## When Writing New Features

Prefer this structure:

- bootstrap file registers hooks
- service class contains workflow logic
- repository class owns persistence
- admin UI stays separate from domain logic
- presentation stays separate from processing

This mirrors the strongest pattern already present in `cbc-form-manager`.

## When Reviewing Code

Look specifically for:

- hardcoded secrets
- `shell_exec`, `exec`, deployment hooks, or public admin utilities
- `permission_callback => '__return_true'`
- CPTs holding PII with broad capabilities
- direct public URLs for private uploads
- duplicated shortcodes or overlapping features
- theme logic that should be plugin logic
- plugin logic that conflicts with theme-wide policy
- config changes that make assumptions about hostnames, proxies, or launch environment

## Definition Of Done For Sensitive Changes

A change touching auth, forms, uploads, AI, newsletter, redirects, or REST is not done until:

- the permission model is explicit
- spam or abuse controls are explicit
- storage exposure is reviewed
- configuration and secret handling are reviewed
- documentation is updated
- the change is added to or reconciled with the vulnerability tracker when applicable
