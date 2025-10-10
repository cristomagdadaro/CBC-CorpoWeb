# Full Page Cache (Lightweight)

A minimal, self‑contained WordPress plugin that performs full front‑end HTML page caching using core transients.

It is designed for small / medium sites or development environments where you want a simple speed boost without adding external services or complex stack layers.

---
## Features
- Toggle (enable / disable) from Settings → General.
- Configurable TTL (minutes) for cached pages.
- Exclusions list (partial path matches, one per line).
- Manual purge button with cache entry count.
- Automatic purge on post save / delete / trash and comment add / edit / delete.
- Sends `X-FPC: HIT` or `X-FPC: MISS` response headers for quick debugging.
- Weak ETag header (`ETag`) and 304 handling when browser sends `If-None-Match`.
- Skips (never caches):
  - Logged‑in users
  - Admin dashboard requests
  - AJAX requests
  - REST API (`/wp-json/`)
  - Feeds & search results
  - 404 pages
  - Non‑GET methods
  - Requests with `?nocache=1` or `preview` params
  - During installs / CLI contexts / when `DONOTCACHEPAGE` is set
- Works with older PHP versions (includes polyfills for `str_starts_with` / `str_contains`).

---
## How It Works
1. On each eligible front‑end page request (`template_redirect`), the plugin builds a cache key from `host + request_uri` and attempts to serve a stored transient.
2. If a cache entry is found → returns it immediately (HIT) with ETag support.
3. On a MISS → output buffering starts; final HTML (status 200 + content-type text/html) is saved at shutdown into a transient for the configured TTL.
4. A simple index (`fpc_index` option) stores all transient keys so a purge can remove them quickly.

---
## Installation
1. Place the `full-page-cache` folder inside `wp-content/plugins/`.
2. Activate “Full Page Cache” from the WordPress Plugins screen.
3. Go to Settings → General and scroll to the “Full Page Cache” section.
4. Check “Enable full page output caching”.
5. Set a TTL (e.g. 10–30 minutes), adjust exclusions if needed, click Save.

---
## Configuration
| Option | Description | Default |
| ------ | ----------- | ------- |
| Enable Full Page Cache | Master on/off switch. | Off |
| TTL (minutes) | Lifetime of a cached page before re-generation. | 10 |
| Exclusions | One partial path per line. If the request URI contains the fragment, that page will not be cached. Examples: `/contact`, `/checkout`, `/cart`, `/my-account/` | (empty) |

### Examples (Exclusions)
```
/cart
/checkout
/wp-json/
?utm_  (NOTE: query fragments are matched only in the full REQUEST_URI; include the leading ? if desired)
```
> Matching is a simple substring search (case sensitive). Keep fragments short & precise.

---
## Manual Purge
Use the “Purge Full Page Cache” button (Settings → General). It:
- Deletes all cached transients listed in `fpc_index`.
- Resets the index to an empty array.

### Programmatic Purge
You can purge via code (e.g. a mu-plugin or custom hook):
```php
if ( function_exists( 'FPC_Full_Page_Cache::purge_all' ) ) {
    FPC_Full_Page_Cache::purge_all();
}
```
(Direct static call: `FPC_Full_Page_Cache::purge_all();`)

---
## Debugging & Verification
1. Open an incognito window (ensures you are not logged in).
2. Load a public page – response header should show: `X-FPC: MISS`.
3. Reload – should change to: `X-FPC: HIT`.
4. Add `?nocache=1` to URL – always MISS and never stored.
5. Edit a post or leave a comment – reload: MISS again (auto purge triggered).
6. Use browser dev tools → Network tab → check `ETag` and `X-FPC` headers.

### Curl Example
```bash
curl -I https://example.com/
```
Look for:
```
X-FPC: HIT  (or MISS)
ETag: W/"<hash>"
```

### 304 Check
```bash
# First get the page & capture ETag
e=$(curl -I -s https://example.com/ | awk '/ETag/ {print $2}')
# Re-request with If-None-Match
echo "ETag: $e"; curl -I -H "If-None-Match: $e" https://example.com/
```
Should return `304 Not Modified` while still counting as a HIT.

---
## Performance Notes / Limitations
- Uses transients → persistent object cache (Redis/Memcached) improves reliability; otherwise falls back to the DB `wp_options` table.
- Not multi-device aware (mobile vs desktop) – both share the same cache key.
- Does not differentiate query parameters beyond full URI hash (so `/page?ref=abc` and `/page?ref=xyz` each cache separately). Consider trimming marketing params if needed.
- No fragment caching (all-or-nothing full document).
- Avoid caching pages requiring personalization (e.g. dynamic dashboards) by adding path fragments to exclusions.

---
## Security Considerations
- Only caches publicly accessible GET responses with status 200 and HTML content type.
- Logged-in users are never served cached HTML, reducing risk of leaking personalized data.
- Always sanitize exclusions input and never store raw user HTML.

---
## Extensibility (Hooks & Ideas)
Currently minimal by design. Potential future enhancements (open for extension):
- Filter to modify bypass logic.
- Filter to adjust cache key (e.g. add device / language dimension).
- Partial (selective) purge on a per-post basis vs full flush.
- WP-CLI command: `wp fpc purge`.

If you need any of these, you can fork and extend or request enhancements.

---
## Troubleshooting
| Symptom | Cause | Fix |
| ------- | ----- | --- |
| `X-FPC` header missing | Plugin not active / header stripped by a proxy | Confirm activation; check no proxy removing custom headers. |
| Always MISS | You are logged in / excluded / using `?nocache=1` | Try incognito, remove nocache, check exclusions. |
| Stale page after edit | Auto purge failed | Click manual purge; ensure no fatal errors during save. |
| High DB autoload size | Many transients + no persistent object cache | Reduce TTL or enable a persistent object cache. |

---
## Uninstall / Removal
Just deactivate & delete the plugin. Transients will naturally expire; to eagerly remove them, purge before deactivation.

---
## Changelog
### 0.1
- Initial release: basic full page caching, TTL, exclusions, purge & auto purge, ETag, headers.

---
## License
MIT (adjust if you prefer a different license). Feel free to modify and redistribute.

---
## Author
Created by Cristo Rey C. Magdadaro (with automated assistant refactoring support).

---
## Quick Checklist (Admin)
- [ ] Activate plugin
- [ ] Enable caching
- [ ] Set TTL (e.g., 15)
- [ ] Add exclusions (`/cart`, `/checkout`, `/account`)
- [ ] Save settings
- [ ] Verify HIT/MISS headers
- [ ] Test purge

Happy caching!

