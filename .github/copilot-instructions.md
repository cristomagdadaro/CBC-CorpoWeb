# Copilot Agentic Guide

## Purpose

This repository is a WordPress codebase for the DA-CBC corporate website. Copilot and other coding agents should optimize for safe changes, minimal regressions, and strong respect for WordPress conventions.

## What This Codebase Is

- A full WordPress deployment, not a small isolated app
- Theme-led presentation using `wp-content/themes/modern-gwt-wordpress`
- Plugin-led business features under `wp-content/plugins/`
- Mixed architecture:
  - classic procedural WordPress code
  - class-based feature plugins
  - one layered/DDD-style plugin: `cbc-form-manager`

## Architectural Rules

### Prefer this ownership model

- Theme:
  - templates
  - layout
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
- creating public REST routes without explicit permission strategy
- storing secrets in tracked files
- using default `post` capabilities for sensitive submission data
- uploading sensitive documents into publicly accessible media paths without protection

## Canonical Design Principles

### Security first

- Every public endpoint must have:
  - capability logic or explicit public justification
  - nonce or anti-automation protection where state changes occur
  - rate limiting for expensive or abuse-prone operations
  - sanitized inputs
  - escaped outputs
- Never add `permission_callback => '__return_true'` by habit.
- Never expose deployment, debugging, or credential-handling code on public routes.

### One owner per concern

- Do not introduce another form system.
- Use `cbc-form-manager` as the preferred model for future structured forms unless the team explicitly retires it.
- If touching appointment, feedback, or internship flows, first verify whether the change belongs in:
  - `cbc-client-engagement`
  - `cbc-form-manager`
- If both currently handle the same concern, propose consolidation instead of extending both.

### Use WordPress APIs correctly

- Use `register_post_type`, `register_rest_route`, `add_action`, `add_filter`, `wp_insert_post`, `update_post_meta`, `$wpdb->prepare`, `wp_safe_redirect`, `check_admin_referer`, `wp_verify_nonce`, `sanitize_*`, and `esc_*`.
- Prefer `wp_remote_get` / `wp_remote_post` over raw cURL.
- Prefer `admin-post.php` or REST with correct auth over ad hoc root PHP entrypoints.

### Respect data sensitivity

Treat the following as sensitive:

- AI chat logs
- names, emails, phone numbers
- internship documents
- IP addresses and user agents
- newsletter subscriber data

When modifying data access:

- prefer dedicated capabilities over `edit_posts`
- minimize retention
- do not make sensitive documents directly public

## Repo-Specific Warnings

### Public REST policy mismatch

The theme includes a global REST auth restriction in:

- `wp-content/themes/modern-gwt-wordpress/inc/function-disable_api.php`

Several plugins define public REST routes anyway. Before adding or changing public REST features, decide one of these approaches:

1. Keep REST private by default and explicitly whitelist public endpoints.
2. Remove the theme-wide global block and secure endpoints individually.

Do not assume a public REST route is actually reachable by anonymous users.

### Duplicate form ownership

These shortcode names overlap across plugins:

- `cbc_appointment_form`
- `cbc_feedback_form`
- `cbc_internship_form`

Do not add more overlap. If changing these features, document which plugin is intended to own them.

### Sensitive uploads

Internship uploads currently flow through public upload storage patterns. Any new file-upload feature must define:

- allowed extensions
- allowed MIME types
- max size
- private storage or access control strategy
- retention/deletion rules

### AI endpoint cost risk

AI features can create direct external cost. Any AI-related change should include:

- abuse prevention
- request throttling
- provider error handling
- privacy review
- clear logging boundaries

## Coding Preferences

### PHP

- Keep code WordPress-compatible and readable.
- Prefer small named functions/methods over deeply nested anonymous callbacks when logic grows.
- Use strict sanitization on input and context-appropriate escaping on output.
- Keep query construction explicit and prepared.

### JavaScript

- Keep frontend scripts small and framework-free unless the existing module already uses a library.
- Use localized WordPress data instead of hard-coded URLs.
- Avoid introducing build systems unless the feature already has one.

### CSS

- Reuse theme/plugin patterns where already established.
- Do not introduce one-off visual systems that fight the current site styling.

## Suggested Change Workflow

1. Identify whether the change belongs to theme, plugin, or server config.
2. Check for an existing plugin/module already owning that concern.
3. Verify security boundaries:
   - roles
   - nonce
   - REST/AJAX auth
   - upload handling
   - secret management
4. Make the smallest coherent change.
5. Update docs if the change affects:
   - architecture
   - public routes
   - deployment
   - security posture
6. Add or update an entry in `docs/VULNERABILITY_TRACKER.md` when resolving a tracked issue.

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
- duplicated shortcodes / overlapping features
- theme logic that should be plugin logic
- plugin logic that conflicts with theme-wide policy

## Definition Of Done For Sensitive Changes

A change touching auth, forms, uploads, AI, newsletter, redirects, or REST is not done until:

- the permission model is explicit
- spam/abuse controls are explicit
- storage exposure is reviewed
- documentation is updated
- the change is added to or reconciled with the vulnerability tracker when applicable
