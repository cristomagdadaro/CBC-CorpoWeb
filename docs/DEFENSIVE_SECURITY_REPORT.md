# Defensive Security Report

Audit date: `2026-04-10`

Scope:

- WordPress core deployment in this repository
- custom plugins under `wp-content/plugins/`
- customized theme code under `wp-content/themes/modern-gwt-wordpress/`
- hardened server example in `nginx.conf`
- security tracking in `docs/VULNERABILITY_TRACKER.md`

## Executive Summary

This report converts prior reconnaissance and penetration-style observations into a defensive remediation view for the `CBC-CorpoWeb` repository.

The highest-value risks for this repo are:

1. `XML-RPC` exposure and authentication abuse surface
2. inconsistent access control across custom REST/AJAX/plugin boundaries
3. sensitive-data handling in custom features and logs
4. insufficiently structured logging and monitoring for security-relevant events
5. operational dependency/version risk outside direct application code

This hardening pass prioritizes safe, minimal, repo-local changes that:

- disable `xmlrpc.php` by default
- strengthen private REST namespace enforcement
- reduce debug/error leakage in custom plugins
- remove raw request debugging from the theme
- add privacy-conscious security event logging for key abuse signals

## OWASP Top 10 Mapping

| Repo Finding | OWASP Top 10 | Why it maps |
| --- | --- | --- |
| XML-RPC enabled and reachable for authentication-style requests | A07: Identification and Authentication Failures | Enables credential abuse, brute-force amplification, and alternate auth attack surface |
| Public or inconsistently private custom REST endpoints | A01: Broken Access Control | Access expectations differ between theme policy and plugin implementation |
| AI/chat/form/plugin logs storing sensitive request data | A02: Cryptographic Failures / Sensitive Data Exposure | Sensitive data handling and retention boundaries need minimization and control |
| Raw request/user-agent logging and inconsistent security telemetry | A09: Security Logging and Monitoring Failures | Security events were not consistently logged in a privacy-conscious, actionable way |
| Publicly exposed operational/version details and outdated platform concerns | A06: Vulnerable and Outdated Components | Operational patching for WordPress/nginx remains required |
| Over-broad or mismatched capability boundaries across plugins | A01: Broken Access Control | Sensitive admin records must stay behind explicit dedicated capabilities |
| Debug/error detail returned from application endpoints | A05: Security Misconfiguration | Excess internal detail can leak implementation information to clients |
| Input validation, output handling, and endpoint hardening in custom plugins | A03: Injection / A05: Security Misconfiguration / A08: Software and Data Integrity Failures | Risk depends on endpoint ownership and validation discipline |

## Current Defensive Findings

### 1. XML-RPC should be treated as unnecessary attack surface unless explicitly required

Observed risk:

- `xmlrpc.php` is a known brute-force and request-amplification surface
- the prior status summary specifically called out `system.multicall`
- this repo does not show a documented business requirement for XML-RPC

Defensive position:

- disable XML-RPC by default in application code
- deny `xmlrpc.php` in the example nginx config
- require an explicit opt-in if future operations need XML-RPC

### 2. REST access control was split between theme policy and plugin policy

Observed risk:

- `modern-gwt-wordpress/inc/function-disable_api.php` attempted to protect private namespaces
- the route matching logic depended on raw `REQUEST_URI` prefix checks and could miss `/wp-json/...` paths
- plugin endpoints such as `cbc-games` and `post-metrics` also enforce their own permissions

Defensive position:

- normalize namespace matching against the actual REST path
- keep endpoint-specific permission callbacks in plugins
- use theme-level namespace protection as defense in depth, not as the only control

### 3. Custom plugin debug and error behavior leaked more detail than needed

Observed risk:

- `cbc-ai-messenger` included administrator bypass/debug logic in the public AI route
- provider/debug details were conditionally returned in responses
- raw request debugging existed in the theme header via `error_log`

Defensive position:

- remove admin bypasses from public request paths
- return generic client-safe error messages
- keep any deeper diagnostics inside restricted admin/server contexts only

### 4. Security logging needed to be more deliberate and less noisy

Observed risk:

- important security events were not consistently logged
- some logs collected raw request metadata unnecessarily

Defensive position:

- log only security-relevant events
- hash IPs/usernames before logging
- avoid raw prompt/request/UA dumps in general request flow
- route logs into server/SIEM collection rather than browser-visible debugging

### 5. Dependency and version risk still requires operational patching

Observed risk:

- the prior summary called out WordPress `6.9.1` and nginx `1.24.0`
- repository code changes cannot patch running infrastructure versions by themselves
- tracked npm build dependencies in `tailwind-manager/package.json` did not show known CVEs in this pass, but platform risk remains

Defensive position:

- patch WordPress core and nginx in deployment
- keep plugin/theme dependency review in release workflow
- continue validating third-party packages before release

## Safe Fixes Implemented In This Repo

### XML-RPC hardening

Changed files:

- `wp-content/plugins/cbc-security-hardening/cbc-security-hardening.php`
- `nginx.conf`

Implemented:

- XML-RPC is blocked by default in the application layer
- XML-RPC methods are disabled unless explicitly re-enabled
- the example nginx config now returns `403` for `/xmlrpc.php`
- pingback exposure headers are removed

### Access control hardening

Changed files:

- `wp-content/themes/modern-gwt-wordpress/inc/function-disable_api.php`

Implemented:

- normalized REST namespace matching to `/wp-json/...`
- extended private namespace protection for:
  - `/cbc-games/v1/`
  - `/post-metrics/v1/`
  - existing protected namespaces remain in place

### Custom plugin hardening

Changed files:

- `wp-content/plugins/cbc-ai-messenger/cbc-ai-messenger.php`
- `wp-content/themes/modern-gwt-wordpress/header.php`

Implemented:

- removed admin reCAPTCHA bypass from the public AI route
- removed admin debug payloads from AI API responses
- minimized stored upstream error detail in AI logs
- removed raw request/user-agent debug logging from the theme header

### Logging and monitoring hardening

Changed files:

- `wp-content/plugins/cbc-security-hardening/cbc-security-hardening.php`

Implemented:

- added privacy-conscious security event logging for:
  - blocked XML-RPC requests
  - login rate-limit enforcement
  - login lockout threshold reached
  - denied monitor endpoint access
- event logs now use hashed network/user identifiers instead of raw values

## Remediation Plan For This WordPress Repo

### Immediate code/config actions

1. Keep `xmlrpc.php` disabled unless a documented requirement appears.
2. Keep custom plugin REST routes behind explicit permission callbacks.
3. Remove any remaining `error_log()` calls that dump request or user metadata.
4. Review every custom plugin for public state-changing endpoints and require:
   - nonce or anti-automation protection where appropriate
   - rate limits
   - explicit permission checks
   - sanitized input and escaped output

### Near-term application security actions

1. Review public custom plugins for route ownership and endpoint inventory:
   - `cbc-ai-messenger`
   - `wp-cbc-games`
   - `cbc-branded-redirect-manager`
   - `post-metrics`
2. Add security review gates for:
   - REST route additions
   - upload handling
   - AI cost-bearing features
   - new admin utilities
3. Add a release checklist item for verifying:
   - private uploads remain private
   - anonymous REST access is only enabled where documented
   - sensitive CPTs use dedicated capabilities

### Operational follow-up actions

1. Patch WordPress core in deployment.
2. Patch nginx in deployment after validating vendor advisories.
3. Ensure production server config actually denies `/xmlrpc.php`.
4. Remove or block `readme.html` in production if currently reachable.
5. Route server/app logs to centralized monitoring and alert on:
   - repeated login lockouts
   - blocked XML-RPC hits
   - AI abuse/rate-limit events
   - unexpected fatal errors

## Validation Notes

This hardening pass focused on safe repository-level changes.

It does **not** by itself prove that production has:

- upgraded nginx
- upgraded WordPress core
- denied `xmlrpc.php` at the active reverse proxy
- removed publicly reachable version disclosure files from the live host

Those items require staging/production verification.

## Recommended Verification Commands

Run these in an authorized staging or production-like environment:

```powershell
curl.exe -I https://dacbc.philrice.gov.ph/xmlrpc.php
curl.exe -I https://dacbc.philrice.gov.ph/readme.html
curl.exe https://dacbc.philrice.gov.ph/wp-json/cbc-games/v1/quiz
curl.exe https://dacbc.philrice.gov.ph/wp-json/post-metrics/v1/track
```

Expected directionally:

- `xmlrpc.php` should not be usable
- `readme.html` should not expose version info publicly
- private namespaces should reject anonymous callers
- public routes should expose only intentionally public behavior

