<?php
/*
Plugin Name: CBC Form Manager
Description: A DDD-based form manager supporting multiple form modules and shortcodes.
Version: 1.0.0
Author: CBC
Text Domain: cbc-form-manager
*/

if (!defined('ABSPATH')) { exit; }

define('CBC_FM_PLUGIN_FILE', __FILE__);
define('CBC_FM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CBC_FM_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once CBC_FM_PLUGIN_DIR . 'src/Autoloader.php';
\CbcFormManager\Autoloader::register();

// Load translations
add_action('plugins_loaded', function () {
    load_plugin_textdomain('cbc-form-manager', false, dirname(plugin_basename(__FILE__)) . '/languages');

    if (is_admin()) {
        (new \CbcFormManager\Infrastructure\Admin\AdminUi())->register();
    }
});

// Register custom post type and boot services
add_action('init', function () {
    (new \CbcFormManager\Infrastructure\Setup\Installer())->register();

    $repo = new \CbcFormManager\Infrastructure\Repository\WpFormSubmissionRepository();
    $nonce = new \CbcFormManager\Infrastructure\Security\NonceManager();
    $service = new \CbcFormManager\Application\FormService($repo, $nonce);

    // Auto-discover presentation form modules
    $discoveredForms = (new \CbcFormManager\Infrastructure\Discovery\FormDiscovery())->discover();

    /** Allow other plugins/themes to add or modify the list of form modules (instances implementing FormModuleInterface). */
    $forms = apply_filters('cbc_form_manager/forms', $discoveredForms);

    foreach ($forms as $form) {
        $service->registerForm($form);
        do_action('cbc_form_manager/registering_form', $form);
    }

    $service->boot();
});

register_activation_hook(__FILE__, function () {
    $installer = new \CbcFormManager\Infrastructure\Setup\Installer();
    $installer->register();
    if (function_exists('flush_rewrite_rules')) {
        flush_rewrite_rules();
    }
});

register_deactivation_hook(__FILE__, function () {
    if (function_exists('flush_rewrite_rules')) {
        flush_rewrite_rules();
    }
});
