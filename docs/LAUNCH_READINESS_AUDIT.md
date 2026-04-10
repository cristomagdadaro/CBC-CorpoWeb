# Launch Readiness Audit

Audit date: `2026-04-10`

Scope reviewed:

- root configuration and deployment files
- custom plugins under `wp-content/plugins/`
- primary customized theme under `wp-content/themes/modern-gwt-wordpress/`
- repository hygiene issues that affect launch risk

## Executive Summary

The codebase is not ready for a public launch next week without remediation.

### Critical blockers

1. Committed secrets and credentials exist in the repository.
2. A web-accessible deployment script can execute `git pull` through PHP.
3. `wp-config.php` forces `http://` site URLs for non-CLI requests.
4. Sensitive PII and uploaded documents are stored with broad access patterns.
5. Public-facing feature ownership is inconsistent, especially around forms and REST.

## Findings

### Critical

#### `CBCV-20260410-001` Public deployment endpoint can execute server-side Git commands

- Severity: Critical
- Status: Open
- Files:
  - `deploy_xitrixgx3z790.php`

Why this matters:

- The file is web-accessible at the repository root.
- It calls `shell_exec()` and runs `git pull`.
- It reveals repository pathing and runtime user details in the browser.
- If reachable in production, it expands the attack surface for deployment abuse and information disclosure.

Evidence:

- `deploy_xitrixgx3z790.php:54-74`

Recommended action:

- Remove the file from the public web root.
- Replace it with authenticated CI/CD or a server-side deploy job.
- If temporary retention is unavoidable, restrict by IP, auth, and server config immediately.

#### `CBCV-20260410-002` Secrets are committed in tracked files

- Severity: Critical
- Status: Open
- Files:
  - `wp-config.php`
  - `google_credentials.json`
  - `client_secret_791440731519-patj7mol2ahjmdsetim833rhed5m3ab2.apps.googleusercontent.com.json`

Why this matters:

- The repository contains DB config, salts, reCAPTCHA secrets, and Google OAuth client secrets.
- Once committed, these should be treated as compromised.
- Secret reuse across environments would make production launch unsafe.

Evidence:

- `wp-config.php:18-23`
- `wp-config.php:45-56`
- `wp-config.php:57-58`
- `google_credentials.json`
- `client_secret_791440731519-patj7mol2ahjmdsetim833rhed5m3ab2.apps.googleusercontent.com.json`

Recommended action:

- Rotate all affected secrets before launch.
- Move secrets to environment variables or untracked environment-specific config.
- Remove tracked secret files from version control history where feasible.

#### `CBCV-20260410-003` WordPress bootstrap forces insecure `http://` URLs

- Severity: Critical
- Status: Open
- Files:
  - `wp-config.php`

Why this matters:

- The config unconditionally sets `$_SERVER['REQUEST_SCHEME'] = 'http'` for non-CLI execution.
- `WP_SITEURL` and `WP_HOME` are then derived from that forced scheme.
- This conflicts with HSTS and can generate insecure URLs, mixed-content behavior, wrong canonical URLs, and broken secure assumptions in production.

Evidence:

- `wp-config.php:31-35`
- `.htaccess:8`

Recommended action:

- Remove the forced scheme override.
- Let the actual HTTPS/proxy configuration determine the scheme.
- If behind a proxy, handle forwarded HTTPS headers correctly instead of hardcoding `http`.

#### `CBCV-20260410-004` AI chat endpoint is publicly callable, cost-bearing, and stores PII with broad CPT permissions

- Severity: Critical
- Status: Open
- Files:
  - `wp-content/plugins/cbc-ai-messenger/cbc-ai-messenger.php`

Why this matters:

- The REST route is registered with `permission_callback => '__return_true'`.
- The route can trigger external LLM calls, which creates cost abuse risk.
- Rate limiting is only one request per 10 seconds per IP, which is weak for public launch.
- The feature logs names, emails, IPs, user agents, prompts, and replies.
- Logs are stored in a CPT using default `post` capabilities, which is broader than a dedicated private capability model.

Evidence:

- `wp-content/plugins/cbc-ai-messenger/cbc-ai-messenger.php:342-350`
- `wp-content/plugins/cbc-ai-messenger/cbc-ai-messenger.php:463-468`
- `wp-content/plugins/cbc-ai-messenger/cbc-ai-messenger.php:522-533`
- `wp-content/plugins/cbc-ai-messenger/cbc-ai-messenger.php:17-30`

Recommended action:

- Require stronger anti-abuse controls.
- Revisit whether the route should be public at all.
- Add dedicated capabilities for log access.
- Minimize retained PII and document retention rules.

#### `CBCV-20260410-005` Internship documents are uploaded into publicly accessible media storage

- Severity: Critical
- Status: Open
- Files:
  - `wp-content/plugins/cbc-client-engagement/cbc-client-engagement.php`
  - `wp-content/plugins/cbc-form-manager/src/Application/FormService.php`

Why this matters:

- Internship and file-upload flows store documents using normal WordPress upload handling and attachment creation.
- This produces direct file URLs in the uploads directory.
- For applications containing personal documents, public URL exposure is unacceptable.

Evidence:

- `wp-content/plugins/cbc-client-engagement/cbc-client-engagement.php:780-826`
- `wp-content/plugins/cbc-form-manager/src/Application/FormService.php:90-139`
- `wp-content/plugins/cbc-form-manager/src/Application/FormService.php:261-300`

Recommended action:

- Move sensitive documents to protected storage.
- Do not expose direct public URLs for application files.
- Add download authorization and retention/deletion policy.

### High

#### `CBCV-20260410-006` Two active form systems register the same shortcodes

- Severity: High
- Status: Open
- Files:
  - `wp-content/plugins/cbc-client-engagement/cbc-client-engagement.php`
  - `wp-content/plugins/cbc-form-manager/cbc-form-manager.php`
  - `wp-content/plugins/cbc-form-manager/presentation/cbc_appointment_form/Form.php`
  - `wp-content/plugins/cbc-form-manager/presentation/cbc_feedback_form/Form.php`
  - `wp-content/plugins/cbc-form-manager/presentation/cbc_internship_form/Form.php`

Why this matters:

- `cbc-client-engagement` registers:
  - `cbc_appointment_form`
  - `cbc_feedback_form`
  - `cbc_internship_form`
- `cbc-form-manager` auto-discovers and registers the same shortcode tags.
- In WordPress, duplicate shortcode tags collide and the last registered handler wins.
- This makes behavior environment-dependent and hard to reason about.

Evidence:

- `wp-content/plugins/cbc-client-engagement/cbc-client-engagement.php:27-31`
- `wp-content/plugins/cbc-form-manager/cbc-form-manager.php:35-46`
- `wp-content/plugins/cbc-form-manager/presentation/cbc_appointment_form/Form.php:9-12`
- `wp-content/plugins/cbc-form-manager/presentation/cbc_feedback_form/Form.php:11-18`
- `wp-content/plugins/cbc-form-manager/presentation/cbc_internship_form/Form.php:9-12`

Recommended action:

- Pick one canonical form platform.
- Remove or rename duplicate shortcodes.
- Migrate storage/admin handling into one path before launch.

#### `CBCV-20260410-007` Theme-level REST lockdown conflicts with plugins that expect anonymous REST access

- Severity: High
- Status: Open
- Files:
  - `wp-content/themes/modern-gwt-wordpress/inc/function-disable_api.php`
  - `wp-content/plugins/cbc-ai-messenger/cbc-ai-messenger.php`
  - `wp-content/plugins/wp-cbc-games/src/Infrastructure/Api/Routes.php`
  - `wp-content/plugins/cbc-security-hardening/cbc-security-hardening.php`
  - `wp-content/plugins/tailwind-manager/tailwind-manager.php`

Why this matters:

- The theme blocks REST requests for all logged-out users.
- Several plugins register public REST endpoints anyway.
- That means either:
  - public features are broken, or
  - future changes will bypass the theme restriction in inconsistent ways.

Evidence:

- `wp-content/themes/modern-gwt-wordpress/inc/function-disable_api.php:1-8`
- `wp-content/plugins/cbc-ai-messenger/cbc-ai-messenger.php:288-289`
- `wp-content/plugins/cbc-ai-messenger/cbc-ai-messenger.php:342-345`
- `wp-content/plugins/wp-cbc-games/src/Infrastructure/Api/Routes.php:19-50`
- `wp-content/plugins/cbc-security-hardening/cbc-security-hardening.php:36-47`
- `wp-content/plugins/tailwind-manager/tailwind-manager.php:187-192`

Recommended action:

- Decide a single public REST policy.
- Prefer endpoint-by-endpoint permission hardening instead of a blanket theme block.
- Smoke test AI chat and games anonymously after fixing policy.

#### `CBCV-20260410-008` PII-heavy admin data is exposed with broad `edit_posts`/default post capabilities

- Severity: High
- Status: Open
- Files:
  - `wp-content/plugins/cbc-client-engagement/cbc-client-engagement.php`
  - `wp-content/plugins/cbc-form-manager/src/Infrastructure/Setup/Installer.php`
  - `wp-content/plugins/cbc-ai-messenger/cbc-ai-messenger.php`

Why this matters:

- Appointment, feedback, internship, form submission, and AI log records all use default post capabilities or menu access based on `edit_posts`.
- That is too broad for personal and operational data.
- Editors or other non-admin content roles may gain access to submissions/logs that should be restricted.

Evidence:

- `wp-content/plugins/cbc-client-engagement/cbc-client-engagement.php:111-116`
- `wp-content/plugins/cbc-client-engagement/cbc-client-engagement.php:132-137`
- `wp-content/plugins/cbc-client-engagement/cbc-client-engagement.php:153-158`
- `wp-content/plugins/cbc-client-engagement/cbc-client-engagement.php:198-220`
- `wp-content/plugins/cbc-form-manager/src/Infrastructure/Setup/Installer.php:20-30`
- `wp-content/plugins/cbc-ai-messenger/cbc-ai-messenger.php:23-30`

Recommended action:

- Introduce dedicated capabilities such as `cbc_view_submissions`, `cbc_manage_ai_logs`, etc.
- Restrict menus and post types to the minimum required roles.

### Medium

#### `CBCV-20260410-009` Games leaderboard can be trivially forged from the client

- Severity: Medium
- Status: Open
- Files:
  - `wp-content/plugins/wp-cbc-games/src/Infrastructure/Api/Routes.php`

Why this matters:

- Anyone can POST leaderboard scores anonymously.
- The server trusts client-submitted scores and times.
- This weakens integrity for public-facing rankings and contests.

Evidence:

- `wp-content/plugins/wp-cbc-games/src/Infrastructure/Api/Routes.php:47-50`
- `wp-content/plugins/wp-cbc-games/src/Infrastructure/Api/Routes.php:96-159`

Recommended action:

- If the leaderboard matters, sign results server-side or validate game state.
- If it is purely promotional, document that it is non-authoritative.

#### `CBCV-20260410-010` `lmstudio` is offered in the AI UI but rejected by settings sanitization

- Severity: Medium
- Status: Open
- Files:
  - `wp-content/plugins/cbc-ai-messenger/cbc-ai-messenger.php`

Why this matters:

- The provider selector offers `LM Studio`.
- Settings sanitization only accepts `openai` and `openrouter`.
- This causes configuration drift and operator confusion.

Evidence:

- `wp-content/plugins/cbc-ai-messenger/cbc-ai-messenger.php:121-123`
- `wp-content/plugins/cbc-ai-messenger/cbc-ai-messenger.php:158-166`

Recommended action:

- Make the allowed provider list consistent across defaults, settings, validation, and runtime branching.

#### `CBCV-20260410-011` Repository hygiene is drifting from `.gitignore`

- Severity: Medium
- Status: Open
- Files:
  - `.gitignore`
  - `wp-content/plugins/tailwind-manager/node_modules/`
  - `wp-content/plugins/tailwind-manager/package-lock.json`

Why this matters:

- The repository currently contains tracked build dependencies and lock artifacts under a plugin.
- `.gitignore` already indicates these should not be committed.
- This increases repository size and creates stale dependency surface inside production code.

Evidence:

- `.gitignore`
- tracked contents under `wp-content/plugins/tailwind-manager/node_modules/`

Recommended action:

- Remove tracked vendor/build artifacts from version control unless intentionally vendored.
- Rebuild assets in CI or as part of release preparation.

### Low / Quality

#### `CBCV-20260410-012` Theme markup/code consistency issues exist in core presentation layer

- Severity: Low
- Status: Open
- Files:
  - `wp-content/themes/modern-gwt-wordpress/inc/function-options.php`
  - `wp-content/themes/modern-gwt-wordpress/functions.php`

Examples:

- stray `SAS` token inside an anchor tag
- duplicate inclusion of `template-tags.php`

Evidence:

- `wp-content/themes/modern-gwt-wordpress/inc/function-options.php:1146`
- `wp-content/themes/modern-gwt-wordpress/functions.php:49-58`
- `wp-content/themes/modern-gwt-wordpress/functions.php:95-103`

Why this matters:

- These are not launch blockers by themselves, but they signal avoidable drift in the most central theme code.

## Recommended Launch Sequence

1. Remove/disable the public deploy endpoint.
2. Rotate and externalize all committed secrets.
3. Fix `wp-config.php` scheme handling for HTTPS.
4. Choose one form platform and disable the duplicate shortcode owner.
5. Move private uploads out of public media access paths.
6. Replace broad content-role access with dedicated capabilities.
7. Align the site-wide REST policy with actual feature needs.
8. Smoke test:
   - anonymous site browsing
   - AI chat
   - newsletter subscribe
   - appointments/feedback/internship forms
   - games
   - branded redirects
   - events/calendar
9. Update the vulnerability tracker with owners, target dates, and resolved evidence.

## Review Notes

- This was a codebase audit, not a running-environment penetration test.
- Findings are based on repository evidence and cross-file behavior analysis.
- Production server configuration, database contents, active-theme selection, and real plugin activation state should still be verified in staging.
