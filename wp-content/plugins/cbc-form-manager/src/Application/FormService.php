<?php
namespace CbcFormManager\Application;

use CbcFormManager\Domain\Entity\FormSubmission;
use CbcFormManager\Domain\Repository\FormSubmissionRepository;
use CbcFormManager\Infrastructure\Security\NonceManager;
use CbcFormManager\Infrastructure\Storage\PrivateUploadManager;

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

        add_action('wp_ajax_cbc_form_submit', [$this, 'ajaxSubmit']);
        add_action('wp_ajax_nopriv_cbc_form_submit', [$this, 'ajaxSubmit']);
    }

    private function handleShortcode(FormModuleInterface $module, array $attrs): string
    {
        $global_script_rel = 'assets/js/form-ajax.js';
        $global_script_path = CBC_FM_PLUGIN_DIR . $global_script_rel;
        $global_script_url = CBC_FM_PLUGIN_URL . $global_script_rel;
        $ver_script = file_exists($global_script_path) ? (string) @filemtime($global_script_path) : '1.0.0';
        wp_enqueue_script('cbc-form-ajax', $global_script_url, ['jquery'], $ver_script, true);
        wp_localize_script('cbc-form-ajax', 'cbcFormAjax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'action'   => 'cbc_form_submit',
        ]);

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

            [$sanitized, $errors] = $this->collectSubmissionData($module);
            $validation = $this->validateSubmission($module, $sanitized, $errors);
            $errors = $validation['errors'];
            $sanitized = $validation['data'];
            $view['old'] = $sanitized;

            if (empty($errors)) {
                $savedId = $this->saveSubmission($module, $sanitized);
                $view['success'] = __('Thanks! Your submission has been received.', 'cbc-form-manager');
                $view['old'] = [];
                do_action('cbc_form_manager/submitted', $module->key(), $savedId, $sanitized);
            } else {
                $view['errors'] = $errors;
            }
        }

        return $module->render($view);
    }

    public function ajaxSubmit(): void
    {
        $formKey = isset($_POST['_cbc_form_key']) ? sanitize_text_field(wp_unslash($_POST['_cbc_form_key'])) : '';
        if ($formKey === '') {
            wp_send_json_error(['message' => __('Missing form key.', 'cbc-form-manager')]);
        }

        $module = null;
        foreach ($this->formsByShortcode as $m) {
            if ($m->key() === $formKey) {
                $module = $m;
                break;
            }
        }
        if (!$module) {
            wp_send_json_error(['message' => __('Unknown form.', 'cbc-form-manager')]);
        }

        $override = apply_filters('cbc_form_manager/ajax_handle', null, $module);
        $override = apply_filters('cbc_form_manager/ajax_handle/' . $module->key(), $override, $module);
        if (is_wp_error($override)) {
            wp_send_json_error(['message' => $override->get_error_message()]);
        } elseif (is_array($override) && array_key_exists('success', $override)) {
            if (!empty($override['success'])) {
                wp_send_json_success($override['data'] ?? []);
            }
            wp_send_json_error($override['data'] ?? []);
        }

        if (!$this->nonce->verify('cbc_form_' . $module->key(), '_cbc_form_nonce')) {
            wp_send_json_error(['errors' => ['_global' => __('Security check failed. Please try again.', 'cbc-form-manager')]]);
        }

        [$sanitized, $errors] = $this->collectSubmissionData($module);
        $validation = $this->validateSubmission($module, $sanitized, $errors);
        $errors = $validation['errors'];
        $sanitized = $validation['data'];

        if (!empty($errors)) {
            wp_send_json_error(['errors' => $errors, 'old' => $sanitized]);
        }

        $savedId = $this->saveSubmission($module, $sanitized);
        do_action('cbc_form_manager/submitted', $module->key(), $savedId, $sanitized);

        wp_send_json_success(['success' => __('Thanks! Your submission has been received.', 'cbc-form-manager')]);
    }

    private function collectSubmissionData(FormModuleInterface $module): array
    {
        $fields = $module->fields();
        $sanitized = [];
        $errors = [];

        foreach ($fields as $name => $def) {
            $type = $def['type'] ?? 'text';
            $required = !empty($def['required']);

            if ($type === 'file') {
                $fileResult = $this->processFileField(isset($_FILES[$name]) ? $_FILES[$name] : null, $def, $name, $module);
                if (!empty($fileResult['missing']) && $required) {
                    $errors[$name] = sprintf(__('%s is required.', 'cbc-form-manager'), $def['label'] ?? $name);
                }
                if (!empty($fileResult['error'])) {
                    $errors[$name] = $fileResult['error'];
                }
                $sanitized[$name] = $fileResult['value'] ?? null;
                continue;
            }

            $raw = isset($_POST[$name]) ? wp_unslash($_POST[$name]) : '';
            $value = $this->sanitizeByType($raw, $type);

            if ($type === 'select') {
                $options = isset($def['options']) && is_array($def['options']) ? array_keys($def['options']) : [];
                if ($value !== '' && !in_array((string) $value, array_map('strval', $options), true)) {
                    $errors[$name] = sprintf(__('%s has an invalid selection.', 'cbc-form-manager'), $def['label'] ?? $name);
                    $value = '';
                }
            }

            if ($required && ($value === '' || $value === null)) {
                $errors[$name] = sprintf(__('%s is required.', 'cbc-form-manager'), $def['label'] ?? $name);
            }
            if ($value !== '' && $value !== null && $type === 'email' && !is_email($value)) {
                $errors[$name] = sprintf(__('%s must be a valid email address.', 'cbc-form-manager'), $def['label'] ?? $name);
            }

            $sanitized[$name] = $value;
        }

        return [$sanitized, $errors];
    }

    private function validateSubmission(FormModuleInterface $module, array $sanitized, array $errors): array
    {
        $validation = apply_filters('cbc_form_manager/validate', [
            'errors' => $errors,
            'data' => $sanitized,
        ], $module);
        $validation = apply_filters('cbc_form_manager/validate/' . $module->key(), $validation, $module);

        return [
            'errors' => is_array($validation['errors'] ?? null) ? $validation['errors'] : $errors,
            'data' => is_array($validation['data'] ?? null) ? $validation['data'] : $sanitized,
        ];
    }

    private function saveSubmission(FormModuleInterface $module, array $sanitized): int
    {
        $userId = get_current_user_id() ?: 0;
        $ip = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field($_SERVER['REMOTE_ADDR']) : '';
        $ua = isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'])) : '';

        $entity = new FormSubmission($module->key(), $sanitized, (int) $userId, $ip, $ua);
        $entity = apply_filters('cbc_form_manager/before_save', $entity, $module);

        return $this->repository->save($entity);
    }

    private function processFileField($fileArr, array $def, string $name, FormModuleInterface $module): array
    {
        if (!$fileArr || ($fileArr['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return [
                'missing' => true,
                'value' => null,
            ];
        }

        $stored = PrivateUploadManager::storeUploadedFile($fileArr, $module->key());
        if (is_wp_error($stored)) {
            return [
                'error' => sprintf(__('Failed to upload %s: %s', 'cbc-form-manager'), $def['label'] ?? $name, $stored->get_error_message()),
                'value' => null,
            ];
        }

        return ['value' => $stored];
    }

    private function sanitizeByType($value, string $type)
    {
        if (is_array($value)) {
            return array_map(function ($v) use ($type) { return $this->sanitizeByType($v, $type); }, $value);
        }

        switch ($type) {
            case 'email':
                return sanitize_email((string) $value);

            case 'textarea':
                return wp_kses_post((string) $value);

            case 'url':
                return esc_url_raw((string) $value);

            case 'number':
                return is_numeric($value) ? (float) $value : '';

            case 'date':
                $v = sanitize_text_field((string) $value);
                return preg_match('/^\d{4}-\d{2}-\d{2}$/', $v) ? $v : '';

            case 'datetime':
                $v = sanitize_text_field((string) $value);
                return preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $v) ? $v : '';

            case 'time':
                $v = sanitize_text_field((string) $value);
                return preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $v) ? $v : '';

            case 'select':
            case 'radio':
                return sanitize_text_field((string) $value);

            case 'checkbox':
                return $value ? 1 : 0;

            case 'file':
                return null;

            case 'text':
            default:
                return sanitize_text_field((string) $value);
        }
    }
}
