<?php
/**
 * Theme bootstrap.
 *
 * @package ModernBiotechWordPress
 */

if (! defined('ABSPATH')) {
    exit;
}

function mbw_theme_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script'));

    register_nav_menus(
        array(
            'primary' => __('Primary Menu', 'modern-biotech-wordpress'),
            'footer'  => __('Footer Menu', 'modern-biotech-wordpress'),
        )
    );
}
add_action('after_setup_theme', 'mbw_theme_setup');

function mbw_enqueue_assets() {
    wp_enqueue_style(
        'mbw-google-fonts',
        'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Montserrat:wght@400;500;600;700;800;900&display=swap',
        array(),
        null
    );

    wp_enqueue_style(
        'mbw-main-style',
        get_template_directory_uri() . '/assets/css/main.css',
        array('mbw-google-fonts'),
        wp_get_theme()->get('Version')
    );

    wp_enqueue_script(
        'mbw-main-script',
        get_template_directory_uri() . '/assets/js/theme.js',
        array(),
        wp_get_theme()->get('Version'),
        true
    );
}
add_action('wp_enqueue_scripts', 'mbw_enqueue_assets');
