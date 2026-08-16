<?php
/**
 * Plugin Name: SproutAi Global Widget Integration
 * Description: Integrates the ai chatbot widget into the footer of the CBC Corporate Website.
 * Version: 1.0.0
 * Author: DA-CBC
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function sproutai_inject_widget() {
    // Read the token securely from wp-config.php, fallback to empty string
    $api_token = defined('SPROUTAI_API_TOKEN') ? SPROUTAI_API_TOKEN : '';
    
    // Inject the AI chatbot widget script into the footer
    echo '<script src="https://onecbc.philrice.gov.ph/ai/embed.js?v=3" data-site-id="dacbc" data-token="' . esc_attr($api_token) . '" defer></script>' . "\n";
}
add_action('wp_footer', 'sproutai_inject_widget', 100);
