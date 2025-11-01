<?php
namespace CbcFormManager\Infrastructure\Setup;

if (!defined('ABSPATH')) { exit; }

class Installer
{
    public function register(): void
    {
        $this->registerPostType();
    }

    private function registerPostType(): void
    {
        $labels = [
            'name' => __('Form Submissions', 'cbc-form-manager'),
            'singular_name' => __('Form Submission', 'cbc-form-manager'),
        ];

        register_post_type('cbc_form_submission', [
            'labels' => $labels,
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'menu_position' => 25,
            'menu_icon' => 'dashicons-feedback',
            'supports' => ['title'],
            'capability_type' => 'post',
            'map_meta_cap' => true,
        ]);
    }
}

