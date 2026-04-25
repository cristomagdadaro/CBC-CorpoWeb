# Launch Readiness Audit

Audit date: `2026-04-25`

Scope reviewed:

- root configuration and deployment files
- custom plugins under `wp-content/plugins/`
- customized themes and repo-level web server rules
- launch documentation created from the previous audit

## Executive Summary

The repository is safer than it was on `2026-04-10`, but it is still **not ready for full public launch or full-blown deployment**.

Several previously reported code-level risks have been mitigated:

- the public deploy script has been removed from the repo
- committed Google OAuth JSON files have been deleted
- overlapping form shortcodes now default to `cbc-form-manager` ownership
- sensitive uploads in `cbc-client-engagement` and `cbc-form-manager` now use private storage patterns
- `cbc-ai-messenger` and `wp-cbc-games` now use stronger permission and abuse controls

However, launch remains blocked by configuration rollout and operational verification work:

1. previously exposed DB credentials, salts, and reCAPTCHA values still must be rotated before launch even though the runtime `wp-config.php` and tracked `wp-config-sample.php` now use environment-driven/server-local config.
2. the new environment-driven `wp-config.php` flow must be populated correctly in staging/production and verified with a real WordPress bootstrap.
3. root web hardening has been added in-repo, but the active hosting stack still must prove it denies `readme.html`, `license.txt`, and similar disclosure files.
4. several tracker entries are only mitigated in code and still require staging or production-like verification before go-live.

## Launch Verdict

Current verdict: **No-Go for public launch**

Minimum blockers to close before approving launch:

1. rotate all previously exposed DB credentials, salts, and reCAPTCHA values and populate them outside the tracked repo
2. verify the new environment-owned `WP_HOME` / `WP_SITEURL` and HTTPS handling in staging
3. confirm the live web tier denies `readme.html`, `license.txt`, and related metadata files
4. re-run staging smoke tests for forms, AI, games, metrics, redirects, and admin-only submission access

## Findings

### Critical

#### `CBCV-20260410-001` Public deployment endpoint can execute server-side Git commands

- Severity: Critical
- Status: Mitigated in repository, still needs deployment verification
- Files:
  - `deploy_xitrixgx3z790.php`

Current state:

- The reviewed repository no longer contains `deploy_xitrixgx3z790.php`.

Remaining launch requirement:

- Confirm the deleted path returns `404` or equivalent denial in staging and production.
- Confirm no replacement ad hoc deploy endpoint exists outside the repo.

#### `CBCV-20260410-002` Repo and runtime config now externalize secrets, but rotation is still mandatory

- Severity: Critical
- Status: Mitigated in repository, still needs credential rotation and deployment verification
- Files:
  - `wp-config.php`

What changed:

- the runtime `wp-config.php` copy now reads DB credentials, salts, and reCAPTCHA keys from environment variables or an untracked `wp-config-local.php`
- the tracked `wp-config-sample.php` now reflects the same environment-driven pattern instead of encouraging checked-in secrets

Why this still matters:

- Any DB passwords, salts, or reCAPTCHA keys that were committed earlier must still be treated as compromised.
- Placeholder defaults remain intentionally non-production and will break or weaken deployment if real environment values are not supplied.

Evidence:

- `wp-config.php` now loads configuration from `WP_DB_*`, `WP_*_KEY`, `WP_*_SALT`, `CBC_AI_RECAPTCHA_*`, and `RECAPTCHA_*` environment variables.
- `wp-config.php` and `wp-config-sample.php` now support an untracked `wp-config-local.php` include for server-local overrides.

Required action before launch:

- Rotate all affected secrets.
- Populate production/staging secrets through environment variables or an untracked server-local config include.
- Verify tracked deployments no longer rely on placeholder values.

#### `CBCV-20260425-001` WordPress bootstrap typo was fixed in-repo, but runtime boot still needs verification

- Severity: Critical
- Status: Mitigated in repository, still needs staging verification
- Files:
  - `wp-config.php`

What changed:

- `wp-config.php` now defines `ABSPATH` with `__DIR__`, restoring the expected WordPress bootstrap path.

Why this still matters:

- The repo-level typo is fixed, but the new config flow still needs a real bootstrap check in staging to confirm no environment assumptions are missing.

Evidence:

- `wp-config.php` now uses `define( 'ABSPATH', __DIR__ . '/' );`

Required action before launch:

- Re-run PHP lint and a real WordPress bootstrap test in the target environment.

#### `CBCV-20260410-003` WordPress URL/bootstrap configuration is now environment-owned, but needs staging validation

- Severity: Critical
- Status: Mitigated in repository, still needs staging verification
- Files:
  - `wp-config.php`

What changed:

- `wp-config.php` now reads `WP_SITEURL` and `WP_HOME` from environment variables instead of deriving them from `HTTP_HOST`.
- HTTPS handling now honors forwarded HTTPS state or an explicit `WP_FORCE_HTTPS` flag instead of forcing scheme behavior unconditionally.

Why this still matters:

- The new approach is safer, but production correctness now depends on deployment configuration supplying the intended values.
- Canonical URLs, admin routing, and reverse-proxy behavior still need real environment verification.

Evidence:

- `wp-config.php` now normalizes environment-owned `WP_SITEURL` / `WP_HOME` values and only enables HTTPS when the request or deployment config indicates it.

Required action before launch:

- Verify canonical URLs, admin URLs, and asset URLs in staging.
- If a reverse proxy or load balancer is involved, confirm `HTTP_X_FORWARDED_PROTO` or `WP_FORCE_HTTPS` is populated as expected.

#### `CBCV-20260410-015` Root-level file denial rules were added, but the active hosting stack still needs proof

- Severity: High
- Status: Mitigated in repository, still needs deployment verification
- Files:
  - `.htaccess`
  - `readme.html`
  - `license.txt`

What changed:

- `.htaccess` now denies direct access to `readme.html`, `license.txt`, `wp-config-sample.php`, Composer metadata, package metadata, and PHPUnit config files.
- `nginx.conf` already contained matching deny rules for the same disclosure paths.

Why this still matters:

- Root `readme.html` and `license.txt` are still present in the repo, so the launch posture still depends on the active server enforcing the deny rules.
- Version masking such as `server_tokens off;` still has to be confirmed in the real hosting stack.

Evidence:

- `.htaccess` now includes a dedicated deny rule for `readme.html`, `license.txt`, and related metadata/config files.
- root files present: `readme.html`, `license.txt`

Required action before launch:

- Confirm the production server also disables version leakage such as `server_tokens`.
- Re-run header and direct-file access checks in staging.

### High

#### `CBCV-20260410-004` AI endpoint is better protected, but privacy and launch verification are still required

- Severity: Critical
- Status: Mitigated in code, verification still required
- Files:
  - `wp-content/plugins/cbc-ai-messenger/cbc-ai-messenger.php`
  - `wp-config.php`

What changed:

- REST registration now uses `cbc_ai_rest_permission` instead of `__return_true`.
- reCAPTCHA is required when configured outside local/dev.
- throttling now includes short, hourly, and daily limits.
- raw IP and UA values are no longer stored directly; hashes/prefixes are logged instead.

Evidence:

- `wp-content/plugins/cbc-ai-messenger/cbc-ai-messenger.php:381-384`
- `wp-content/plugins/cbc-ai-messenger/cbc-ai-messenger.php:393-402`
- `wp-content/plugins/cbc-ai-messenger/cbc-ai-messenger.php:497-522`
- `wp-content/plugins/cbc-ai-messenger/cbc-ai-messenger.php:548-583`
- `wp-content/plugins/cbc-ai-messenger/cbc-ai-messenger.php:635-638`

Residual concerns:

- reCAPTCHA secrets now come from environment variables or an untracked local config file, but previously exposed values still need rotation.
- AI logs still retain names, emails, prompts, and responses, so retention and access control still matter.
- The endpoint currently only accepts Gmail addresses, which is a product/UX restriction that should be intentionally approved before launch.

Required action before launch:

- populate rotated reCAPTCHA keys through environment variables or an untracked local config file
- verify admin-only access to AI logs in staging
- confirm the Gmail-only requirement is intentional

#### `CBCV-20260410-005` Sensitive upload handling is improved and now uses private storage patterns

- Severity: Critical
- Status: Mitigated in code, verification still required
- Files:
  - `wp-content/plugins/cbc-client-engagement/cbc-client-engagement.php`
  - `wp-content/plugins/cbc-form-manager/src/Infrastructure/Storage/PrivateUploadManager.php`

What changed:

- internship files are stored under `cbc-private-uploads/client-engagement`
- form-manager uploads are stored under `cbc-private-uploads/form-manager`
- stored filenames are randomized
- file permissions are tightened to `0600` when available
- downloads require admin-side capability and nonce checks

Evidence:

- `wp-content/plugins/cbc-client-engagement/cbc-client-engagement.php:972-1022`
- `wp-content/plugins/cbc-client-engagement/cbc-client-engagement.php:1034-1057`
- `wp-content/plugins/cbc-form-manager/src/Infrastructure/Storage/PrivateUploadManager.php:31-38`
- `wp-content/plugins/cbc-form-manager/src/Infrastructure/Storage/PrivateUploadManager.php:55-109`
- `wp-content/plugins/cbc-form-manager/src/Infrastructure/Storage/PrivateUploadManager.php:124-157`

Remaining launch requirement:

- Upload and download a real test document in staging.
- Confirm no direct public URL is created or leaked for submitted files.

#### `CBCV-20260410-006` Duplicate form shortcode ownership is now controlled

- Severity: High
- Status: Mitigated in code, verification still required
- Files:
  - `wp-content/plugins/cbc-client-engagement/cbc-client-engagement.php`
  - `wp-content/plugins/cbc-form-manager/cbc-form-manager.php`

What changed:

- `cbc-client-engagement` now disables its legacy duplicate shortcodes by default when `cbc-form-manager` is active.

Evidence:

- `wp-content/plugins/cbc-client-engagement/cbc-client-engagement.php:92-106`

Remaining launch requirement:

- Confirm production plugin activation order matches expectations.
- Smoke test appointment, feedback, and internship public forms and admin submission views.

#### `CBCV-20260410-007` REST policy mismatch is partially reduced, but overall policy still needs to be explicit

- Severity: High
- Status: In Progress
- Files:
  - `wp-content/themes/modern-gwt-wordpress/inc/function-disable_api.php`
  - `wp-content/plugins/cbc-ai-messenger/cbc-ai-messenger.php`
  - `wp-content/plugins/wp-cbc-games/src/Infrastructure/Api/Routes.php`

Why this still matters:

- `wp-cbc-games` now requires authenticated access for game and leaderboard routes.
- `cbc-ai-messenger` is still intentionally callable by unauthenticated visitors, subject to its own anti-abuse checks.
- The theme-level blanket REST restriction still creates policy ambiguity for future maintainers.

Evidence:

- `wp-content/plugins/wp-cbc-games/src/Infrastructure/Api/Routes.php:15-40`
- `wp-content/plugins/wp-cbc-games/src/Infrastructure/Api/Routes.php:48-79`

Required action before launch:

- Document the intended anonymous REST policy explicitly.
- Test anonymous AI use and logged-out REST responses in staging.

### Medium

#### `CBCV-20260410-008` Sensitive admin data access is improved but still must be role-tested

- Severity: High
- Status: Mitigated in code, verification still required
- Files:
  - `wp-content/plugins/cbc-client-engagement/cbc-client-engagement.php`
  - `wp-content/plugins/cbc-form-manager/src/Infrastructure/Storage/PrivateUploadManager.php`

What changed:

- client engagement now uses `cbc_manage_client_engagement`
- form-manager private downloads require `cbc_manage_form_submissions`

Required action before launch:

- Confirm editors and non-admin users cannot access submissions, uploads, or AI logs.
- Confirm administrators retain required access.

#### `CBCV-20260410-009` Games leaderboard forgery risk is reduced, but the feature is still not authoritative

- Severity: Medium
- Status: Mitigated in code, verification still required
- Files:
  - `wp-content/plugins/wp-cbc-games/src/Infrastructure/Api/Routes.php`

What changed:

- game routes now require login
- leaderboard submission now requires a nonce, signed submission token, valid game, and rate limits

Evidence:

- `wp-content/plugins/wp-cbc-games/src/Infrastructure/Api/Routes.php:15-40`
- `wp-content/plugins/wp-cbc-games/src/Infrastructure/Api/Routes.php:48-79`
- `wp-content/plugins/wp-cbc-games/src/Infrastructure/Api/Routes.php:213-305`

Residual concern:

- The server still trusts client-reported score and timing values within bounded validation.
- Treat the leaderboard as promotional unless server-authoritative gameplay verification is added.

#### `CBCV-20260410-010` AI provider handling may still need product-level cleanup

- Severity: Medium
- Status: Mitigated in code, verify in staging

Launch note:

- This item no longer appears to be a primary launch blocker, but settings persistence and runtime provider selection should still be checked in staging before closure.

#### `CBCV-20260410-011` Repo hygiene remains mixed and should be cleaned before release packaging

- Severity: Medium
- Status: Mitigated in tracker, but still worth checking before release

Launch note:

- Reconfirm no vendored build artifacts that should stay out of production packaging are being shipped unintentionally.

## Recommended Launch Sequence

1. Populate the new environment-driven `wp-config.php` settings in staging:
  - database credentials
  - salts
  - reCAPTCHA keys
  - `WP_HOME` / `WP_SITEURL`
2. Rotate all previously exposed secrets.
3. Lint `wp-config.php` and confirm a real WordPress bootstrap in staging.
4. Re-run direct-file checks for `readme.html`, `license.txt`, and other denied metadata files.
5. Re-run staging smoke tests:
  - homepage and main navigation
  - appointments, feedback, and internship submissions
  - AI chat
  - newsletter
  - branded redirects
  - games
  - events/calendar
  - admin-only submission downloads and logs
6. Promote tracker items from `MITIGATED` to `RESOLVED` only after evidence exists.

## Review Notes

- This remains a repository audit, not a running-environment penetration test.
- The docs have been updated to distinguish:
  - code mitigations already present in the repo
  - deployment or staging verification that still has to happen
- Based on the current repository state alone, launch approval should not be given yet.
