<?php
/**
 * Plugin Name:       CBC Google reCAPTCHA
 * Description:       A simple plugin to add Google reCAPTCHA v2 (checkbox) to the site and provide a verification function.
 * Version:           1.1.0
 * Author:            Cristo Rey C. Magdadaro
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Add the Google reCAPTCHA v2 Checkbox script to the head section of the site.
 */
add_action('wp_head', function() {
	if ( ! defined('CBC_AI_RECAPTCHA_SITE_KEY') ) {
		return;
	}

	$site_key = CBC_AI_RECAPTCHA_SITE_KEY;

    echo '<script src="https://www.google.com/recaptcha/api.js" async defer></script>' . "\n";
});

/**
 * Verify the Google reCAPTCHA v2 response.
 *
 * This function can be called from any form processing logic to verify the reCAPTCHA token.
 *
 * Usage:
 * if ( isset( $_POST['g-recaptcha-response'] ) ) {
 *     if ( function_exists( 'cbc_recaptcha_verify' ) && cbc_recaptcha_verify( sanitize_text_field( wp_unslash( $_POST['g-recaptcha-response'] ) ) ) ) {
 *         // reCAPTCHA verification passed. Process the form.
 *     } else {
 *         // reCAPTCHA verification failed.
 *     }
 * }
 *
 * @param string $token The reCAPTCHA token from the form submission.
 * @return boolean True if verification successful, false otherwise.
 */
function cbc_recaptcha_verify( $token ) {
    // Validate token is not empty
    if ( empty( $token ) || ! is_string( $token ) ) {
        return false;
    }

    if ( ! defined('CBC_AI_RECAPTCHA_SECRET') ) {
        error_log('reCAPTCHA secret key not defined');
        return false;
    }

    $secret_key = CBC_AI_RECAPTCHA_SECRET;

    // Sanitize remote IP
    $remote_ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';

    $response = wp_remote_post( 'https://www.google.com/recaptcha/api/siteverify', array(
        'body'    => array(
            'secret'   => $secret_key,
            'response' => $token,
            'remoteip' => $remote_ip,
        ),
        'timeout' => 5,
        'sslverify' => true,
    ) );

    if ( is_wp_error( $response ) ) {
        error_log('reCAPTCHA API error: ' . $response->get_error_message());
        return false;
    }

    $body = wp_remote_retrieve_body( $response );
    $result = json_decode( $body, true );

    // Check for successful verification
    if ( isset( $result['success'] ) && $result['success'] === true ) {
        return true;
    }

    // Log failed verification details for debugging
    if ( isset( $result['error-codes'] ) ) {
        error_log('reCAPTCHA verification failed: ' . implode(', ', $result['error-codes']));
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

    echo '<div class="g-recaptcha flex justify-center" data-sitekey="' . esc_attr($site_key) . '"></div>';
}
