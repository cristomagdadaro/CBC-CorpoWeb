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

// Expose API base, plugin URL, and a custom nonce for leaderboard POSTs
add_action('wp_enqueue_scripts', function(){
    wp_register_script('cbc-games-bootstrap', '', [], '1.0.0', true);
    wp_enqueue_script('cbc-games-bootstrap');
    $data = [
        'apiBase' => esc_url_raw(get_rest_url(null, 'cbc-games/v1/')),
        'pluginUrl' => esc_url_raw(CBC_GAMES_URL),
        // Custom nonce not tied to auth; used to reduce CSRF
        'nonce' => wp_create_nonce('cbc_games_nonce'),
        'siteOrigin' => esc_url_raw(home_url('/')),
    ];
    wp_add_inline_script('cbc-games-bootstrap', 'window.cbcGames = ' . wp_json_encode($data) . ';', 'before');
});

// Activation: create leaderboard table
register_activation_hook(__FILE__, function(){
    global $wpdb;
    $table = $wpdb->prefix . 'cbc_games_leaderboard';
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE {$table} (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        game VARCHAR(32) NOT NULL,
        name VARCHAR(100) NOT NULL,
        agency VARCHAR(150) NOT NULL,
        age INT NULL,
        score INT NOT NULL DEFAULT 0,
        time_ms INT NULL,
        played_at DATE NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        KEY game_score (game, score),
        KEY game_created (game, created_at)
    ) $charset_collate;";
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
});

// Ensure leaderboard table exists at runtime (in case plugin was already active before update)
add_action('plugins_loaded', function(){
    global $wpdb;
    $table = $wpdb->prefix . 'cbc_games_leaderboard';
    $exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table));
    if ($exists !== $table) {
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE {$table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            game VARCHAR(32) NOT NULL,
            name VARCHAR(100) NOT NULL,
            agency VARCHAR(150) NOT NULL,
            age INT NULL,
            score INT NOT NULL DEFAULT 0,
            time_ms INT NULL,
            played_at DATE NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY game_score (game, score),
            KEY game_created (game, created_at)
        ) $charset_collate;";
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }
});

// If another plugin globally blocks unauthenticated REST calls, allow our namespace explicitly
add_filter('rest_authentication_errors', function($result){
    if (is_wp_error($result)) {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $prefix = '/' . rest_get_url_prefix() . '/cbc-games/v1/';
        if ($uri && strpos($uri, $prefix) !== false && is_user_logged_in()) {
            // Allow authenticated users to reach the games namespace even if another layer blocks REST broadly.
            return null;
        }
    }
    return $result;
}, 999);

