<?php
namespace CbcFormManager\Infrastructure\Setup;

use CbcFormManager\Infrastructure\Storage\PrivateUploadManager;

if (!defined('ABSPATH')) { exit; }

class Installer
{
    public function register(): void
    {
        PrivateUploadManager::registerCapabilities();
        $this->registerPostType();
    }

    private function registerPostType(): void
    {
        $labels = [
            'name' => __('Form Manager', 'cbc-form-manager'),
            'singular_name' => __('Form Manager', 'cbc-form-manager'),
        ];

        register_post_type('cbc_form_submission', [
            'labels' => $labels,
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'menu_position' => 25,
            'menu_icon' => 'dashicons-feedback',
            'supports' => ['title'],
            'capabilities' => PrivateUploadManager::postTypeCapabilities(),
            'map_meta_cap' => false,
        ]);
    }
}

