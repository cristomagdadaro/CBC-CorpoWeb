# CBC Form Manager

A Domain-Driven Design (DDD) WordPress plugin to create and manage multiple form modules, each with its own PHP implementation and shortcode. Forms are auto-discovered from the `presentation/` directory and registered automatically.

- Storage: Custom Post Type (`cbc_form_submission`) via repository pattern
- Security: Nonce verification, sanitization, basic validation
- Extensibility: Add new form modules easily; hooks/filters for validation and processing
- Architecture: Domain, Application, Infrastructure, Presentation

## Requirements

- PHP 7.4+ (PHP 8.x compatible)
- WordPress 6.x recommended

## Installation

1. Copy the `cbc-form-manager` folder to `wp-content/plugins/`.
2. In WordPress Admin, go to Plugins and activate “CBC Form Manager”.

## Quick Start

Add one of these shortcodes to any page or post:

- `[cbc_appointment_form]`
- `[cbc_feedback_form]`
- `[cbc_internship_form]`

Submissions appear under Admin → Form Submissions.

## Available Forms (initial)

- cbc_appointment_form — Appointment request form
- cbc_feedback_form — General feedback form with rating
- cbc_internship_form — Internship application form

Each form lives in `presentation/<form_key>/`, with its own `Form.php`, CSS, and JS assets.

## Architecture Overview

- `cbc-form-manager.php` — plugin bootstrap
- `src/` — domain/application/infrastructure code
  - `Domain/` — entities and repository interfaces
    - `Entity/FormSubmission.php`
    - `Repository/FormSubmissionRepository.php`
  - `Application/` — service layer and module interfaces
    - `FormModuleInterface.php`
    - `FormService.php`
  - `Infrastructure/` — WP implementations (repository, setup, security, admin UI, discovery)
    - `Repository/WpFormSubmissionRepository.php`
    - `Setup/Installer.php`
    - `Security/NonceManager.php`
    - `Admin/AdminUi.php`
    - `Discovery/FormDiscovery.php`
- `presentation/` — per-form presentation code and assets
  - `cbc_appointment_form/`
  - `cbc_feedback_form/`
  - `cbc_internship_form/`

## How Form Auto-Discovery Works

Any class at `presentation/<form_key>/Form.php` with namespace:

```
CbcFormManager\Presentation\<form_key>\Form
```

…that implements `CbcFormManager\Application\FormModuleInterface` is automatically discovered and registered on `init`.

## Creating a New Form

1. Create a folder `presentation/your_form_key/`.
2. Add `Form.php` implementing `FormModuleInterface` methods:
   - `key()` → returns `your_form_key`
   - `shortcode()` → returns a unique shortcode tag (e.g., `your_form_key`)
   - `title()` → human-readable title
   - `fields()` → define field schema (labels, type, required, options for select)
   - `enqueue_assets()` → enqueue CSS/JS
   - `render(array $view)` → return HTML for the form
3. Optionally add `assets/style.css` and `assets/script.js`.
4. The plugin will auto-discover and register it.

Alternatively, register via filter (e.g., from a theme or another plugin):

```php
add_filter('cbc_form_manager/forms', function ($forms) {
    $forms[] = new \CbcFormManager\Presentation\your_form_key\Form();
    return $forms;
});
```

## Field Types and Validation

Supported field types in `fields()`:
- `text`, `email`, `textarea`, `url`, `number`, `date`, `select`
- `required: true` enforces presence
- `email` is validated using `is_email`
- `select` is validated against declared `options` keys

Sanitization by type:
- `text` → `sanitize_text_field`
- `email` → `sanitize_email`
- `textarea` → `wp_kses_post`
- `url` → `esc_url_raw`
- `number` → numeric cast (or empty if invalid)
- `date` → `YYYY-MM-DD` format check
- `select` → `sanitize_text_field` + whitelist check

The `render($view)` method receives:
- `errors` (array), `old` (array), `success` (string|null)
- `action` (string), `nonce_action` (string), `nonce_name` (string)

## Hooks and Filters

- `cbc_form_manager/forms` (filter): Modify the array of form module instances before registration.
- `cbc_form_manager/registering_form` (action): Fires for each form during registration.
- `cbc_form_manager/validate` (filter): Adjust validation result for any form.
- `cbc_form_manager/validate/{form_key}` (filter): Adjust validation result for a specific form.
  - Expects array: `['errors' => [], 'data' => []]`
- `cbc_form_manager/before_save` (filter): Last-minute mutation of the `FormSubmission` entity before persistence.
- `cbc_form_manager/submitted` (action): After successful save: `(form_key, saved_id, sanitized_data)`.

## Data Storage & Admin

- Submissions are saved as custom posts of type `cbc_form_submission` with private status.
- Data is stored as JSON in meta key `_cbc_form_data`, along with metadata like IP, user agent, user, and timestamps.
- Admin UI:
  - Columns: Form Key, Submitted At
  - Metabox: “Submitted Data” (read-only table)

## Security

- Nonce check on every submission
- Sanitization and validation by field type
- Safe output with esc_html/esc_attr/esc_textarea in renders

For added anti-spam, consider adding honeypots or integrating with a CAPTCHA plugin using the validation hooks.

## Internationalization

- Text domain: `cbc-form-manager`
- Domain Path: `/languages`
- Use a tool like `wp i18n make-pot` to regenerate the POT file

## Uninstall Behavior

By default, data is preserved. To delete all plugin data on uninstall, set this option to `yes` before uninstalling:

- Option: `cbc_fm_delete_data_on_uninstall`

## Troubleshooting

- Shortcode not rendering: ensure the plugin is active and your theme isn’t stripping shortcodes.
- Submissions not saved: verify nonces aren’t being removed by caching or security plugins.
- Custom form not appearing: check your class namespace and that it implements `FormModuleInterface`. Confirm the file path is `presentation/<key>/Form.php`.

## Changelog

- 1.0.0 — Initial release with auto-discovered forms, repository pattern, and three built-in forms.

## License

This project follows the license of the host WordPress site. If you need a specific open-source license, add it here.

## Support

For questions or improvements, open an internal ticket or extend via hooks/filters described above.

