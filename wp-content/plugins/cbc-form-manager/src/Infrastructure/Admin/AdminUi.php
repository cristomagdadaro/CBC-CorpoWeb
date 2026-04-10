<?php
namespace CbcFormManager\Infrastructure\Admin;

use CbcFormManager\Infrastructure\Storage\PrivateUploadManager;

if (!defined('ABSPATH')) { exit; }

class AdminUi
{
    public function register(): void
    {
        add_filter('manage_cbc_form_submission_posts_columns', [$this, 'columns']);
        add_action('manage_cbc_form_submission_posts_custom_column', [$this, 'columnContent'], 10, 2);
        add_action('add_meta_boxes', [$this, 'addMetabox']);
    }

    public function columns($columns)
    {
        $columns['cbc_form_key'] = __('Form Key', 'cbc-form-manager');
        $columns['cbc_submitted_at'] = __('Submitted At', 'cbc-form-manager');
        return $columns;
    }

    public function columnContent($column, $postId): void
    {
        if ($column === 'cbc_form_key') {
            echo esc_html(get_post_meta($postId, '_cbc_form_key', true));
        } elseif ($column === 'cbc_submitted_at') {
            $created = get_post_meta($postId, '_cbc_form_created_at', true);
            echo esc_html($created ?: get_the_date('', $postId));
        }
    }

    public function addMetabox(): void
    {
        add_meta_box(
            'cbc_form_submission_data',
            __('Submitted Data', 'cbc-form-manager'),
            [$this, 'renderDataMetabox'],
            'cbc_form_submission',
            'normal',
            'default'
        );
    }

    public function renderDataMetabox($post): void
    {
        $json = (string)get_post_meta($post->ID, '_cbc_form_data', true);
        $data = json_decode($json, true);
        if (!is_array($data)) {
            echo '<p>' . esc_html__('No data available.', 'cbc-form-manager') . '</p>';
            return;
        }
        echo '<table class="widefat striped"><tbody>';
        foreach ($data as $key => $value) {
            echo '<tr>';
            echo '<th style="width:220px">' . esc_html($key) . '</th>';
            if (PrivateUploadManager::isPrivateFileMeta($value)) {
                $label = $value['original_name'] ?? basename($value['private_path']);
                echo '<td>';
                echo '<a href="' . esc_url(PrivateUploadManager::buildDownloadUrl((int) $post->ID, (string) $key)) . '">' . esc_html($label) . '</a>';
                if (!empty($value['size'])) {
                    echo ' <span class="description">(' . esc_html(size_format((int) $value['size'])) . ')</span>';
                }
                echo '</td>';
            } elseif (is_array($value)) {
                echo '<td><pre>' . esc_html(wp_json_encode($value, JSON_PRETTY_PRINT)) . '</pre></td>';
            } else {
                echo '<td>' . nl2br(esc_html((string)$value)) . '</td>';
            }
            echo '</tr>';
        }
        echo '</tbody></table>';
    }
}


