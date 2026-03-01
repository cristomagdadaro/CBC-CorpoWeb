# reCAPTCHA Integration Documentation

**Version:** 1.0  
**Date:** 2024  
**Plugin Version:** cbc-recaptcha v1.1.0 (with security enhancements)

---

## Overview

This document outlines the integration of Google reCAPTCHA v2 (Checkbox) across all public-facing forms on CBC-CorpoWeb to protect against bot submissions and automated spam attacks.

### Protected Forms

| Form | Plugin | Status | File |
|------|--------|--------|------|
| GoLink (Branded Redirect) | cbc-branded-redirect-manager | ✅ Protected | `wp-content/plugins/cbc-branded-redirect-manager/cbc_branded_redirect_manager.php` |
| Appointment Booking | cbc-client-engagement | ✅ Protected | `wp-content/plugins/cbc-client-engagement/cbc-client-engagement.php` |
| Feedback Form | cbc-client-engagement | ✅ Protected | `wp-content/plugins/cbc-client-engagement/cbc-client-engagement.php` |
| Internship Application | cbc-client-engagement | ✅ Protected | `wp-content/plugins/cbc-client-engagement/cbc-client-engagement.php` |
| Newsletter Subscription | cbc-newsletter | ✅ Protected | `wp-content/plugins/cbc-newsletter/cbc-newsletter.php` |

---

## Configuration

### Prerequisites

1. **Google reCAPTCHA Keys**
   - Site Key (public)
   - Secret Key (private)
   - Get keys from: https://www.google.com/recaptcha/admin

2. **WordPress Configuration**
   - Add to `wp-config.php`:
   ```php
   // Google reCAPTCHA v2 Configuration
   define( 'CBC_AI_RECAPTCHA_SITE_KEY', 'YOUR_SITE_KEY_HERE' );
   define( 'CBC_AI_RECAPTCHA_SECRET', 'YOUR_SECRET_KEY_HERE' );
   ```

3. **Enable reCAPTCHA Plugin**
   - Ensure CBC reCAPTCHA plugin is activated in WordPress admin panel
   - Check: **Plugins** → Look for "CBC reCAPTCHA"

---

## Technical Architecture

### Core Component: CBC reCAPTCHA Plugin

**File:** `wp-content/plugins/cbc-recaptcha/cbc-recaptcha.php`

#### Key Functions

**1. `cbc_recaptcha_field()`**
- Renders the reCAPTCHA v2 checkbox widget
- Usage: Call in form templates before submit button
- Example:
  ```php
  <?php if ( function_exists( 'cbc_recaptcha_field' ) ) : ?>
      <div style="margin: 15px 0;">
          <?php cbc_recaptcha_field(); ?>
      </div>
  <?php endif; ?>
  ```

**2. `cbc_recaptcha_verify( $token )`**
- Verifies reCAPTCHA token with Google API
- Parameters:
  - `$token` (string): The g-recaptcha-response token from the form
- Returns:
  - `true` if verification succeeds
  - `false` if verification fails
- Usage in AJAX handlers:
  ```php
  $recaptcha_token = isset($_POST['g-recaptcha-response']) 
      ? sanitize_text_field(wp_unslash($_POST['g-recaptcha-response'])) 
      : '';
  if (!cbc_recaptcha_verify($recaptcha_token)) {
      wp_send_json_error(['message' => 'reCAPTCHA verification failed.']);
      return;
  }
  ```

#### Security Features Implemented

| Feature | Description | Impact |
|---------|-------------|--------|
| IP Sanitization | `sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) )` | Prevents injection attacks on IP address |
| Token Validation | `if ( empty( $token ) \|\| ! is_string( $token ) ) return false;` | Guards against empty/invalid tokens |
| Request Timeout | `'timeout' => 5` | Prevents indefinite API call hangs |
| SSL Verification | `'sslverify' => true` | Enforces HTTPS certificate validation |
| Correct v2 Endpoint | `api.js` (not `api.js?render=`) | Uses proper v2 API URL, not v3 |
| Error Logging | Comprehensive Google API error codes logged | Enables debugging and monitoring |

---

## Integration Points

### 1. GoLink Form (Branded Redirect Manager)

**File:** `wp-content/plugins/cbc-branded-redirect-manager/cbc_branded_redirect_manager.php`

**Frontend Integration (Line ~681)**
```php
<?php if ( function_exists( 'cbc_recaptcha_field' ) ) : ?>
    <div style="margin: 15px 0;">
        <?php cbc_recaptcha_field(); ?>
    </div>
<?php endif; ?>
```

**Backend Verification (Line ~705)**
```php
// reCAPTCHA verification
$recaptcha_token = isset($_POST['g-recaptcha-response']) 
    ? sanitize_text_field(wp_unslash($_POST['g-recaptcha-response'])) 
    : '';
if (function_exists('cbc_recaptcha_verify') && !cbc_recaptcha_verify($recaptcha_token)) {
    return wp_send_json_error(['message' => 'reCAPTCHA verification failed.']);
}
```

**Notes:**
- Only shown on public submissions (admin bypass for testing)
- Gracefully disabled if reCAPTCHA plugin is inactive
- Verification occurs before rate limiting checks

### 2. Client Engagement Forms (Appointment, Feedback, Internship)

**File:** `wp-content/plugins/cbc-client-engagement/cbc-client-engagement.php`

**Forms Affected:**
- `render_appointment_form()`: "Book Appointment" form
- `render_feedback_form()`: "Send Feedback" form
- `render_internship_form()`: "Submit Application" form

**Frontend Integration**
All three forms include reCAPTCHA field before submit button:
```php
<?php if ( function_exists( 'cbc_recaptcha_field' ) ) : ?>
    <div style="margin: 15px 0;">
        <?php cbc_recaptcha_field(); ?>
    </div>
<?php endif; ?>
```

**Backend Verification**
All three handlers verify reCAPTCHA after nonce check:
- `handle_submit_appointment()`
- `handle_submit_feedback()`
- `handle_submit_internship()`

```php
// reCAPTCHA verification
if (function_exists('cbc_recaptcha_verify')) {
    $recaptcha_token = isset($_POST['g-recaptcha-response']) 
        ? sanitize_text_field(wp_unslash($_POST['g-recaptcha-response'])) 
        : '';
    if (!cbc_recaptcha_verify($recaptcha_token)) {
        $this->redirect_with_message('reCAPTCHA verification failed. Please try again.', false);
    }
}
```

**Notes:**
- Verification occurs before honeypot check (early bot protection)
- Uses `->redirect_with_message()` for consistent error handling
- Compatible with file uploads (internship form)

### 3. Newsletter Subscription

**File:** `wp-content/plugins/cbc-newsletter/cbc-newsletter.php`

**Frontend Integration**
reCAPTCHA field added in `cbc_newsletter_subscribe_form()`:
```php
<?php if ( function_exists( 'cbc_recaptcha_field' ) ) : ?>
    <div style="margin: 15px 0;">
        <?php cbc_recaptcha_field(); ?>
    </div>
<?php endif; ?>
```

**Backend Verification**
In `cbc_newsletter_handle_ajax_subscription()`:
```php
// reCAPTCHA verification
if (function_exists('cbc_recaptcha_verify')) {
    $recaptcha_token = isset($_POST['g-recaptcha-response']) 
        ? sanitize_text_field(wp_unslash($_POST['g-recaptcha-response'])) 
        : '';
    if (!cbc_recaptcha_verify($recaptcha_token)) {
        wp_send_json_error(['message' => 'reCAPTCHA verification failed. Please try again.'], 403);
        return;
    }
}
```

**Notes:**
- Uses AJAX-based submission
- Verification occurs after nonce check
- Returns proper HTTP 403 status for failed verification

---

## Data Flow

### User Submission Process

```
1. User fills form and clicks Submit
   ↓
2. reCAPTCHA v2 checkbox appears (or auto-validates)
   ↓
3. Google reCAPTCHA generates token
   ↓
4. Token submitted with form data in g-recaptcha-response field
   ↓
5. Server receives POST request
   ↓
6. cbc_recaptcha_verify() called:
   a. Validates token format
   b. Sends token + secret to Google API
   c. Receives score/success from Google
   d. Logs any errors
   ↓
7. If verified:
   - Form processing continues
   ↓
8. If failed:
   - Error message shown to user
   - Form NOT processed
```

### API Communication (Verification Process)

```
Server → Google reCAPTCHA API
  ├─ URL: https://www.google.com/recaptcha/api/siteverify
  ├─ Method: POST
  ├─ Timeout: 5 seconds
  ├─ SSL Verification: Enabled
  └─ Parameters:
      ├─ secret: [CBC_AI_RECAPTCHA_SECRET]
      ├─ response: [user_token]
      └─ remoteip: [user_ip_sanitized]
       ↓
Google API Response
  ├─ success: (boolean)
  ├─ challenge_ts: (timestamp)
  ├─ hostname: (string)
  ├─ error-codes: (array, if any)
  └─ ChallengeTimeout: (string, if failed)
```

---

## Testing

### Manual Testing Checklist

- [ ] **Form Display**
  - [ ] reCAPTCHA checkbox appears on all 5 forms
  - [ ] Checkbox is properly styled and clickable
  - [ ] Forms submit successfully when reCAPTCHA is checked

- [ ] **Verification**
  - [ ] Form rejects submission when reCAPTCHA unchecked
  - [ ] Error message displays on verification failure
  - [ ] Form accepts submission when reCAPTCHA verified

- [ ] **Admin Bypass**
  - [ ] Admin users can bypass reCAPTCHA on GoLink form
  - [ ] Admin submissions succeed without verification

- [ ] **Configuration**
  - [ ] Check `wp-config.php` for keys defined
  - [ ] Verify keys are correct in Google reCAPTCHA admin console
  - [ ] Check WordPress admin → Plugins for "CBC reCAPTCHA" active

### Browser Console Logging

reCAPTCHA provides debug information in browser console when configured.

To enable debugging on reCAPTCHA widget:
```javascript
window.grecaptcha.ready(function() {
    console.log('reCAPTCHA ready');
});
```

---

## Troubleshooting

### Issue: reCAPTCHA Widget Not Showing

**Causes:**
1. Plugin not activated
2. Site Key not configured in wp-config.php
3. reCAPTCHA script not loaded due to CSP headers

**Solutions:**
1. Check Plugins admin panel for "CBC reCAPTCHA" - activate if needed
2. Verify `CBC_AI_RECAPTCHA_SITE_KEY` in wp-config.php
3. Review browser console for CSP violations or script errors
4. Check that forms include: `if ( function_exists( 'cbc_recaptcha_field' ) )`

### Issue: "Verification Failed" Error

**Causes:**
1. Secret Key incorrect in wp-config.php
2. API timeout (slow server/network)
3. HTTPS certificate validation failure
4. Google API temporarily unavailable

**Solutions:**
1. Double-check `CBC_AI_RECAPTCHA_SECRET` in wp-config.php
2. Check server response times in WordPress debug log
3. Verify server has updated CA certificates
4. Check Google reCAPTCHA API status: https://status.cloud.google.com

### Issue: Forms Submitting Without reCAPTCHA

**Causes:**
1. reCAPTCHA plugin not activated
2. Verification function not called in handler
3. Conditional check excluding form type

**Solutions:**
1. Activate CBC reCAPTCHA plugin in admin panel
2. Verify `function_exists( 'cbc_recaptcha_verify' )` in handler code
3. Check form type isn't excluded by conditional logic
4. Review forms for proper token field: `g-recaptcha-response`

### Issue: Admin Cannot Submit GoLink Form

**Causes:**
1. Admin bypass logic error
2. Admin reCAPTCHA verification not disabled

**Solutions:**
1. Check conditional for `! $is_admin_submission` in handler
2. Verify admin bypass only applies to GoLink, not other forms
3. Debug: Add `error_log()` to verify admin flag value

---

## Security Considerations

### reCAPTCHA v2 vs v3

**CBC Implementation:** v2 Checkbox

| Feature | v2 Checkbox | v3 |
|---------|------------|-----|
| User Interaction | Required | No |
| Bypass Risk | Lower | Higher |
| Accessibility | Good | Limited |
| False Positives | Lower | Can be high |
| Cost | Free | Free (with quotas) |

### Limitations & Recommendations

1. **reCAPTCHA Bypass Techniques:**
   - Sophisticated bots may bypass reCAPTCHA
   - Combine with rate limiting, honeypot fields
   - Monitor logs for suspicious patterns

2. **Data Privacy:**
   - Token sent to Google (Alphabet/Google privacy policy applies)
   - User IP logged by Google for verification
   - Review privacy policy and GDPR compliance

3. **Additional Protections Already Implemented:**
   - ✅ Honeypot fields (all CBC forms)
   - ✅ Rate limiting with exponential backoff (GoLink)
   - ✅ Nonce verification (all forms)
   - ✅ CSRF protection

4. **Server-Side Validation:**
   - Always validate user input server-side (already done)
   - Never rely solely on reCAPTCHA
   - Verify email addresses are authentic
   - Validate file uploads (internship form)

---

## Monitoring & Logging

### Enable WordPress Debug Logging

Add to `wp-config.php`:
```php
// Enable WordPress debug log
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false ); // Don't display errors on frontend
```

### Check Logs

Logs location: `wp-content/debug.log`

Look for:
- `reCAPTCHA verification failed` - Failed token validations
- `Error calling Google reCAPTCHA API` - API communication issues
- HTTP 403 errors - Rejected submissions

### Monitor Form Submissions

Check WordPress database:
```sql
-- Count appointments by date
SELECT DATE(post_date), COUNT(*) as count 
FROM wp_posts 
WHERE post_type = 'cbc_appointment' 
GROUP BY DATE(post_date);

-- Count feedback submissions
SELECT DATE(post_date), COUNT(*) as count 
FROM wp_posts 
WHERE post_type = 'cbc_feedback' 
GROUP BY DATE(post_date);

-- Count internship applications
SELECT DATE(post_date), COUNT(*) as count 
FROM wp_posts 
WHERE post_type = 'cbc_internship' 
GROUP BY DATE(post_date);

-- Count newsletter subscribers
SELECT COUNT(*) as subscriber_count FROM wp_newsletter_subscribers;
```

---

## Maintenance

### Regular Tasks

1. **Monthly:** Review form submissions for anomalies
2. **Quarterly:** Check reCAPTCHA API status and documentation for changes
3. **Semi-Annually:** Review security logs for attack patterns
4. **Annually:** Update reCAPTCHA keys if needed

### Update Process

If updating reCAPTCHA plugin:
1. Backup WordPress database
2. Deactivate CBC reCAPTCHA plugin
3. Delete old plugin files
4. Upload new plugin version
5. Activate plugin
6. Test all 5 forms for functionality
7. Verify keys still configured in wp-config.php

---

## Future Enhancements

1. **reCAPTCHA v3 Migration**
   - Implement score-based verification (non-intrusive)
   - Adjust threshold based on form severity

2. **Advanced Logging**
   - Track reCAPTCHA success/failure rates
   - Dashboard widget for submission trends

3. **Rate Limiting Enhancement**
   - Stricter limits after reCAPTCHA failures
   - Temporary IP blocking after X failures

4. **Email Verification**
   - Send confirmation emails for newsletter signups
   - Add verification link to appointment/feedback/internship

5. **Multi-Language Support**
   - Localize reCAPTCHA widget text
   - Translate error messages

---

## Support & References

- **Google reCAPTCHA Documentation:** https://developers.google.com/recaptcha
- **reCAPTCHA Admin Console:** https://www.google.com/recaptcha/admin
- **WordPress Security:** https://developer.wordpress.org/plugins/security/
- **OWASP Bot Protection:** https://owasp.org/www-community/attacks/csrf

---

## Revision History

| Version | Date | Changes |
|---------|------|---------|
| 1.0 | 2024 | Initial integration across all public forms |

---

**Document Prepared By:** Development Team  
**Last Updated:** 2024  
**Status:** Production Ready
