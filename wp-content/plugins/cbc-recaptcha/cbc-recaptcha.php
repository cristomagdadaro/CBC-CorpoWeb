<?php
/**
 * Plugin Name:       CBC Google reCAPTCHA
 * Description:       A simple plugin to add Google reCAPTCHA v2 (checkbox) to the site and provide a verification function.
 * Version:           1.1.0
 * Author:            GitHub Copilot
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Add the Google reCAPTCHA script to the head section of the site.
 */
add_action('wp_head', function() {
	if ( !defined('CBC_AI_RECAPTCHA_SITE_KEY') ) {
		return false;
	}

	$site_key = CBC_AI_RECAPTCHA_SITE_KEY;

    echo '<script src="https://www.google.com/recaptcha/api.js?render="' . $site_key . ' async defer></script>' . "\n";
});

/**
 * Verify the Google reCAPTCHA response.
 *
 * This function can be called from any form processing logic to verify the reCAPTCHA token.
 *
 * Usage:
 * if ( isset( $_POST['g-recaptcha-response'] ) ) {
 *     if ( function_exists( 'cbc_recaptcha_verify' ) && cbc_recaptcha_verify( $_POST['g-recaptcha-response'] ) ) {
 *         // reCAPTCHA verification passed. Process the form.
 *     } else {
 *         // reCAPTCHA verification failed.
 *     }
 * }
 *
 * @param string $token The reCAPTCHA token from the form submission (e.g., $_POST['g-recaptcha-response']).
 * @return boolean True if the verification is successful, false otherwise.
 */
function cbc_recaptcha_verify( $token ) {
    if ( !defined('CBC_AI_RECAPTCHA_SECRET') ) {
        return false;
    }

    $secret_key = CBC_AI_RECAPTCHA_SECRET;

    $response = wp_remote_post( 'https://www.google.com/recaptcha/api/siteverify', [
        'body' => [
            'secret'   => $secret_key,
            'response' => $token,
            'remoteip' => $_SERVER['REMOTE_ADDR'],
        ],
    ]);

    if ( is_wp_error( $response ) ) {
        error_log('reCAPTCHA verification request failed: ' . $response->get_error_message());
        return false;
    }

    $body = wp_remote_retrieve_body( $response );
    $result = json_decode( $body, true );

    if ( isset( $result['success'] ) && $result['success'] === true ) {
        return true;
    }

    return false;
}

/**
 * Adds a div to forms to hold the reCAPTCHA checkbox.
 *
 * To use this, call cbc_recaptcha_field() within your form.
 *
 * Example:
 * <form method="post">
 *     ... your form fields ...
 *     <?php if (function_exists('cbc_recaptcha_field')) { cbc_recaptcha_field(); } ?>
 *     <input type="submit" value="Submit">
 * </form>
 */
function cbc_recaptcha_field() {
    if ( !defined('CBC_AI_RECAPTCHA_SITE_KEY') ) {
        return;
    }

    $site_key = CBC_AI_RECAPTCHA_SITE_KEY;

    echo '<div class="g-recaptcha" data-sitekey="' . esc_attr($site_key) . '"></div>';
}
