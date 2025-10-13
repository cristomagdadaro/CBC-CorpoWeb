<?php
/*
Plugin Name: CBC Games
Description: Crop Biotechnology Center mini-games (Quiz, Memory, Scramble) as WordPress shortcodes. Shortcodes: [cbc_quiz], [cbc_memory], [cbc_scramble].
Version: 1.0.0
Author: Cristo Rey C. Magdadaro
*/

if (!defined('ABSPATH')) {
    exit;
}

// Constants
if (!defined('CBC_GAMES_PATH')) {
    define('CBC_GAMES_PATH', plugin_dir_path(__FILE__));
}
if (!defined('CBC_GAMES_URL')) {
    define('CBC_GAMES_URL', plugin_dir_url(__FILE__));
}

// Simple PSR-4-style autoloader for the CBCGames namespace
spl_autoload_register(function ($class) {
    $prefix = 'CBCGames\\';
    $base_dir = CBC_GAMES_PATH . 'src/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', DIRECTORY_SEPARATOR, $relative_class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Register shortcodes on init
add_action('init', function () {
    // Ensure classes loadable
    if (class_exists('CBCGames\\Presentation\\Shortcodes\\RegisterShortcodes')) {
        (new CBCGames\Presentation\Shortcodes\RegisterShortcodes())->register();
    }
});

// Register REST API routes
add_action('rest_api_init', function () {
    // Ensure classes loadable
    if (class_exists('CBCGames\\Infrastructure\\Api\\Routes')) {
        (new CBCGames\Infrastructure\Api\Routes())->register();
    }
});

// Optional: Publish assets URL for use by scripts
add_action('wp_enqueue_scripts', function () {
    // Common fonts and minimal base styles
    wp_register_style('cbc-games-styles', CBC_GAMES_URL . 'assets/css/styles.css', [], '1.0.0');
});
