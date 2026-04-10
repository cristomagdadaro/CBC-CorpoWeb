<?php
namespace CbcFormManager\Infrastructure\Storage;

if (!defined('ABSPATH')) { exit; }

class PrivateUploadManager
{
    public const DOWNLOAD_ACTION = 'cbc_fm_download_private_file';
    public const MANAGE_CAPABILITY = 'cbc_manage_form_submissions';

    public static function registerCapabilities(): void
    {
        $role = get_role('administrator');
        if (!$role) {
            return;
        }

        $role->add_cap(self::MANAGE_CAPABILITY);
    }

    public static function postTypeCapabilities(): array
    {
        return [
            'edit_post'              => self::MANAGE_CAPABILITY,
            'read_post'              => self::MANAGE_CAPABILITY,
            'delete_post'            => self::MANAGE_CAPABILITY,
            'edit_posts'             => self::MANAGE_CAPABILITY,
            'edit_others_posts'      => self::MANAGE_CAPABILITY,
            'publish_posts'          => self::MANAGE_CAPABILITY,
            'read_private_posts'     => self::MANAGE_CAPABILITY,
            'delete_posts'           => self::MANAGE_CAPABILITY,
            'delete_private_posts'   => self::MANAGE_CAPABILITY,
            'delete_published_posts' => self::MANAGE_CAPABILITY,
            'delete_others_posts'    => self::MANAGE_CAPABILITY,
            'edit_private_posts'     => self::MANAGE_CAPABILITY,
            'edit_published_posts'   => self::MANAGE_CAPABILITY,
            'create_posts'           => self::MANAGE_CAPABILITY,
        ];
    }

    public static function basePath(): string
    {
        return rtrim(dirname(untrailingslashit(ABSPATH)), '/\\') . DIRECTORY_SEPARATOR . 'cbc-private-uploads' . DIRECTORY_SEPARATOR . 'form-manager';
    }

    public static function ensureDirectory(string $formKey = '')
    {
        $path = self::basePath();

        if ($formKey !== '') {
            $path .= DIRECTORY_SEPARATOR . sanitize_key($formKey);
        }

        if (!is_dir($path) && !wp_mkdir_p($path)) {
            return new \WP_Error('cbc_fm_private_dir', __('Could not prepare secure upload storage.', 'cbc-form-manager'));
        }

        return $path;
    }

    public static function storeUploadedFile(array $fileArr, string $formKey = '')
    {
        if (($fileArr['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return new \WP_Error('cbc_fm_upload_error', __('The uploaded file could not be processed.', 'cbc-form-manager'));
        }

        if (($fileArr['size'] ?? 0) > 10 * 1024 * 1024) {
            return new \WP_Error('cbc_fm_upload_size', __('The uploaded file exceeds the 10 MB limit.', 'cbc-form-manager'));
        }

        $originalName = sanitize_file_name(wp_basename($fileArr['name'] ?? 'document.pdf'));
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        if ($extension !== 'pdf') {
            return new \WP_Error('cbc_fm_upload_type', __('Uploaded documents must be PDF files.', 'cbc-form-manager'));
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = $finfo ? (string) finfo_file($finfo, $fileArr['tmp_name']) : '';
        if ($finfo) {
            finfo_close($finfo);
        }

        if ($mime !== 'application/pdf') {
            return new \WP_Error('cbc_fm_upload_mime', __('Uploaded documents must be valid PDF files.', 'cbc-form-manager'));
        }

        $directory = self::ensureDirectory($formKey);
        if (is_wp_error($directory)) {
            return $directory;
        }

        $storedName = wp_generate_password(20, false, false) . '-' . $originalName;
        $target = trailingslashit($directory) . $storedName;

        if (!move_uploaded_file($fileArr['tmp_name'], $target)) {
            return new \WP_Error('cbc_fm_upload_move', __('Could not store the uploaded document securely.', 'cbc-form-manager'));
        }

        if (function_exists('chmod')) {
            @chmod($target, 0600);
        }

        return [
            'private_path' => $target,
            'original_name' => $originalName,
            'stored_name' => $storedName,
            'mime' => $mime,
            'size' => (int) filesize($target),
        ];
    }

    public static function isPrivateFileMeta($value): bool
    {
        return is_array($value) && !empty($value['private_path']);
    }

    public static function buildDownloadUrl(int $submissionId, string $field): string
    {
        return wp_nonce_url(
            add_query_arg([
                'action' => self::DOWNLOAD_ACTION,
                'submission_id' => $submissionId,
                'field' => $field,
            ], admin_url('admin-post.php')),
            'cbc_fm_download_' . $submissionId . '_' . $field
        );
    }

    public static function handleDownload(): void
    {
        $submissionId = isset($_GET['submission_id']) ? absint($_GET['submission_id']) : 0;
        $field = isset($_GET['field']) ? sanitize_key(wp_unslash($_GET['field'])) : '';

        if (!$submissionId || $field === '' || !current_user_can(self::MANAGE_CAPABILITY)) {
            wp_die(__('You are not allowed to access this file.', 'cbc-form-manager'), 403);
        }

        check_admin_referer('cbc_fm_download_' . $submissionId . '_' . $field);

        $post = get_post($submissionId);
        if (!$post || $post->post_type !== 'cbc_form_submission') {
            wp_die(__('Invalid form submission.', 'cbc-form-manager'), 404);
        }

        $json = (string) get_post_meta($submissionId, '_cbc_form_data', true);
        $data = json_decode($json, true);
        $file = is_array($data) ? ($data[$field] ?? null) : null;

        if (!self::isPrivateFileMeta($file) || empty($file['private_path']) || !file_exists($file['private_path'])) {
            wp_die(__('The requested file is no longer available.', 'cbc-form-manager'), 404);
        }

        $base = realpath(self::basePath());
        $real = realpath($file['private_path']);
        if (!$base || !$real || strpos($real, $base) !== 0) {
            wp_die(__('Invalid secure file path.', 'cbc-form-manager'), 403);
        }

        nocache_headers();
        header('Content-Description: File Transfer');
        header('Content-Type: ' . sanitize_text_field($file['mime'] ?? 'application/octet-stream'));
        header('Content-Disposition: attachment; filename="' . rawurlencode($file['original_name'] ?? basename($real)) . '"');
        header('Content-Length: ' . filesize($real));
        readfile($real);
        exit;
    }
}

