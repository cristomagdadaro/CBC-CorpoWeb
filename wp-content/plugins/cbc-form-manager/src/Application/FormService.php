<?php
namespace CbcFormManager\Application;

use CbcFormManager\Domain\Entity\FormSubmission;
use CbcFormManager\Domain\Repository\FormSubmissionRepository;
use CbcFormManager\Infrastructure\Security\NonceManager;

if (!defined('ABSPATH')) { exit; }

class FormService
{
    /** @var array<string, FormModuleInterface> */
    private array $formsByShortcode = [];

    private FormSubmissionRepository $repository;
    private NonceManager $nonce;

    public function __construct(FormSubmissionRepository $repository, NonceManager $nonce)
    {
        $this->repository = $repository;
        $this->nonce = $nonce;
    }

    public function registerForm(FormModuleInterface $module): void
    {
        $this->formsByShortcode[$module->shortcode()] = $module;
    }

    public function boot(): void
    {
        foreach ($this->formsByShortcode as $shortcode => $module) {
            add_shortcode($shortcode, function ($attrs = [], $content = '', $tag = '') use ($module) {
                return $this->handleShortcode($module, $attrs);
            });
        }
    }

    private function handleShortcode(FormModuleInterface $module, array $attrs): string
    {
        $module->enqueue_assets();

        $view = [
            'errors' => [],
            'old' => [],
            'success' => null,
            'action' => esc_url($_SERVER['REQUEST_URI'] ?? ''),
            'nonce_action' => 'cbc_form_' . $module->key(),
            'nonce_name' => '_cbc_form_nonce',
        ];

        if (strtoupper($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['_cbc_form_key']) && $_POST['_cbc_form_key'] === $module->key()) {
            if (!$this->nonce->verify($view['nonce_action'], $view['nonce_name'])) {
                $view['errors']['_global'] = __('Security check failed. Please try again.', 'cbc-form-manager');
                return $module->render($view);
            }

            $fields = $module->fields();
            $sanitized = [];
            $errors = [];

            foreach ($fields as $name => $def) {
                $type = $def['type'] ?? 'text';
                $required = !empty($def['required']);

                if ($type === 'file') {
                    $fileArr = isset($_FILES[$name]) ? $_FILES[$name] : null;
                    if (!$fileArr || ($fileArr['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                        if ($required) {
                            $errors[$name] = sprintf(__('%s is required.', 'cbc-form-manager'), $def['label'] ?? $name);
                        }
                        $sanitized[$name] = null;
                        continue;
                    }

                    // Validate and handle upload (PDF only)
                    require_once ABSPATH . 'wp-admin/includes/file.php';
                    $overrides = [ 'test_form' => false ];
                    $uploaded = wp_handle_upload($fileArr, $overrides);
                    if (!is_array($uploaded) || isset($uploaded['error'])) {
                        $errors[$name] = sprintf(__('Failed to upload %s: %s', 'cbc-form-manager'), $def['label'] ?? $name, $uploaded['error'] ?? __('Unknown error', 'cbc-form-manager'));
                        $sanitized[$name] = null;
                        continue;
                    }

                    $filetype = wp_check_filetype(basename($uploaded['file']), null);
                    $ext = strtolower($filetype['ext'] ?? '');
                    $mime = strtolower($filetype['type'] ?? '');
                    if ($ext !== 'pdf' || $mime !== 'application/pdf') {
                        // Not a PDF; remove the uploaded file
                        @unlink($uploaded['file']);
                        $errors[$name] = sprintf(__('%s must be a PDF file.', 'cbc-form-manager'), $def['label'] ?? $name);
                        $sanitized[$name] = null;
                        continue;
                    }

                    // Insert as attachment
                    $attachment = [
                        'guid' => $uploaded['url'],
                        'post_mime_type' => $mime,
                        'post_title' => sanitize_file_name(basename($uploaded['file'])),
                        'post_content' => '',
                        'post_status' => 'inherit',
                    ];
                    $attach_id = wp_insert_attachment($attachment, $uploaded['file']);
                    if (is_wp_error($attach_id)) {
                        $errors[$name] = sprintf(__('Failed to save uploaded file for %s.', 'cbc-form-manager'), $def['label'] ?? $name);
                        $sanitized[$name] = null;
                        continue;
                    }

                    // Optionally generate metadata; for PDFs this is minimal.
                    if (!function_exists('wp_generate_attachment_metadata')) {
                        require_once ABSPATH . 'wp-admin/includes/image.php';
                    }
                    $attach_data = wp_generate_attachment_metadata($attach_id, $uploaded['file']);
                    if (!empty($attach_data)) {
                        wp_update_attachment_metadata($attach_id, $attach_data);
                    }

                    $sanitized[$name] = [
                        'attachment_id' => (int)$attach_id,
                        'url' => esc_url_raw($uploaded['url']),
                        'type' => $mime,
                        'filename' => basename($uploaded['file']),
                    ];
                    continue;
                }

                $raw = isset($_POST[$name]) ? wp_unslash($_POST[$name]) : '';
                $value = $this->sanitizeByType($raw, $type);

                // If select with options, ensure value is allowed
                if ($type === 'select') {
                    $options = isset($def['options']) && is_array($def['options']) ? array_keys($def['options']) : [];
                    if ($value !== '' && !in_array((string)$value, array_map('strval', $options), true)) {
                        $errors[$name] = sprintf(__('%s has an invalid selection.', 'cbc-form-manager'), $def['label'] ?? $name);
                        $value = '';
                    }
                }

                if ($required && ($value === '' || $value === null)) {
                    $errors[$name] = sprintf(__('%s is required.', 'cbc-form-manager'), $def['label'] ?? $name);
                }
                if ($value !== '' && $value !== null) {
                    if ($type === 'email' && !is_email($value)) {
                        $errors[$name] = sprintf(__('%s must be a valid email address.', 'cbc-form-manager'), $def['label'] ?? $name);
                    }
                }

                $sanitized[$name] = $value;
            }

            // Allow external validation/customization
            $validation = apply_filters('cbc_form_manager/validate', [
                'errors' => $errors,
                'data' => $sanitized,
            ], $module);
            $validation = apply_filters('cbc_form_manager/validate/' . $module->key(), $validation, $module);

            $errors = is_array($validation['errors'] ?? null) ? $validation['errors'] : $errors;
            $sanitized = is_array($validation['data'] ?? null) ? $validation['data'] : $sanitized;

            $view['old'] = $sanitized;

            if (empty($errors)) {
                $userId = get_current_user_id() ?: 0;
                $ip = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field($_SERVER['REMOTE_ADDR']) : '';
                $ua = isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'])) : '';

                $entity = new FormSubmission($module->key(), $sanitized, (int)$userId, $ip, $ua);

                // Allow last-minute mutation before save
                $entity = apply_filters('cbc_form_manager/before_save', $entity, $module);

                $savedId = $this->repository->save($entity);
                $view['success'] = __('Thanks! Your submission has been received.', 'cbc-form-manager');
                // Clear old values on success
                $view['old'] = [];

                // Optionally, could trigger an action hook for further processing (emails, integrations)
                do_action('cbc_form_manager/submitted', $module->key(), $savedId, $sanitized);
            } else {
                $view['errors'] = $errors;
            }
        }

        return $module->render($view);
    }

    private function sanitizeByType($value, string $type)
    {
        if (is_array($value)) {
            return array_map(function ($v) use ($type) { return $this->sanitizeByType($v, $type); }, $value);
        }
        switch ($type) {
            case 'email':
                return sanitize_email((string)$value);
            case 'textarea':
                return wp_kses_post((string)$value);
            case 'url':
                return esc_url_raw((string)$value);
            case 'number':
                return is_numeric($value) ? 0 + $value : '';
            case 'date':
                $v = sanitize_text_field((string)$value);
                // Basic YYYY-MM-DD check
                return preg_match('/^\d{4}-\d{2}-\d{2}$/', $v) ? $v : '';
            case 'select':
                return sanitize_text_field((string)$value);
            case 'file':
                return null; // handled separately
            case 'text':
            default:
                return sanitize_text_field((string)$value);
        }
    }
}
