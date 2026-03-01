<?php
/**
 * Plugin Name: CBC Security Hardening
 * Description: Security headers, login protections, reCAPTCHA, basic monitoring endpoint, and optional Sentry/Rollbar forwarding.
 * Version: 1.0.0
 * Author: CBC
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CBC_Security_Hardening {
	const LOGIN_LIMIT = 5; // attempts
	const LOGIN_WINDOW = 15 * MINUTE_IN_SECONDS;

	public static function init() {
		// Headers
		add_action( 'send_headers', array( __CLASS__, 'send_security_headers' ) );

		// Login rate limiting
		add_filter( 'authenticate', array( __CLASS__, 'check_login_rate_limit' ), 1, 3 );
		add_action( 'wp_login_failed', array( __CLASS__, 'record_login_failure' ) );
		add_action( 'wp_login', array( __CLASS__, 'clear_login_failures' ), 10, 2 );

		// Login reCAPTCHA
		add_action( 'login_form', array( __CLASS__, 'render_login_recaptcha' ) );
		add_filter( 'authenticate', array( __CLASS__, 'verify_login_recaptcha' ), 20, 3 );

		// Comment reCAPTCHA
		add_action( 'comment_form_after_fields', array( __CLASS__, 'render_comment_recaptcha' ) );
		add_action( 'comment_form_logged_in_after', array( __CLASS__, 'render_comment_recaptcha' ) );
		add_filter( 'preprocess_comment', array( __CLASS__, 'verify_comment_recaptcha' ) );

		// Monitoring ping endpoint
		add_action( 'rest_api_init', function() {
			register_rest_route( 'cbc-monitor/v1', '/ping', array(
				'methods'  => 'GET',
				'permission_callback' => '__return_true',
				'callback' => function() {
					return array(
						'status' => 'ok',
						'timestamp' => time(),
						'wp_version' => get_bloginfo( 'version' ),
					);
				},
			) );
		});

		// Simple PHP error forwarding to Sentry/Rollbar if DSN is provided.
		set_exception_handler( array( __CLASS__, 'capture_exception' ) );
		set_error_handler( array( __CLASS__, 'capture_error' ) );
		register_shutdown_function( array( __CLASS__, 'capture_shutdown' ) );

		// Login page branding
		add_action( 'login_enqueue_scripts', array( __CLASS__, 'brand_login_logo' ) );
		add_filter( 'login_headerurl', array( __CLASS__, 'login_logo_url' ) );
		add_filter( 'login_headertext', array( __CLASS__, 'login_logo_title' ) );
	}

	/* ------------------------ LOGIN BRANDING ------------------------ */
	public static function brand_login_logo() {
		// Change URL below if you move the logo.
		$logo_url = esc_url( home_url( '/wp-content/uploads/2024/06/DA-CBC-Logo-white-DA.png' ) );

                // Added filter: drop-shadow for a clean glow/shadow effect
                $css = "#login h1 a { 
                background-image: url('{$logo_url}'); 
                background-size: contain; 
                width: 260px; 
                height: 120px; 
                filter: drop-shadow(0px 4px 6px rgba(0, 0, 0, 0.3)); 
                }";

                wp_add_inline_style( 'login', $css );
	}

	public static function login_logo_url( $url ) {
		return home_url( '/' );
	}

	public static function login_logo_title( $text ) {
		return get_bloginfo( 'name', 'display' );
	}

	/* ------------------------ SECURITY HEADERS ------------------------ */
	public static function send_security_headers() {
		// HSTS only on HTTPS
		if ( is_ssl() ) {
			header( 'Strict-Transport-Security: max-age=31536000; includeSubDomains; preload' );
		}

		header( 'X-Content-Type-Options: nosniff' );
		header( 'X-Frame-Options: SAMEORIGIN' );
		header( 'Referrer-Policy: strict-origin-when-cross-origin' );
		header( 'Permissions-Policy: geolocation=(), microphone=(), camera=()' );
		header( 'Cross-Origin-Opener-Policy: same-origin' );

		// CSP (report-only by default to avoid breakage). Define CBC_CSP_ENFORCE=true to enforce.
		$csp = "default-src 'self'; " .
			"script-src 'self' 'unsafe-inline' 'unsafe-eval' https://www.googletagmanager.com https://www.google-analytics.com https://www.google.com https://www.gstatic.com; " .
			"style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; " .
			"img-src 'self' data: https://www.google-analytics.com https://www.googletagmanager.com; " .
			"font-src 'self' https://fonts.gstatic.com data:; " .
			"connect-src 'self' https://www.google-analytics.com https://www.googletagmanager.com; " .
			"frame-src 'self' https://www.google.com https://www.youtube.com; " .
			"object-src 'none'; base-uri 'self'; form-action 'self' https://www.google.com; upgrade-insecure-requests";

		if ( defined( 'CBC_CSP_ENFORCE' ) && CBC_CSP_ENFORCE ) {
			header( 'Content-Security-Policy: ' . $csp );
		} else {
			header( 'Content-Security-Policy-Report-Only: ' . $csp );
		}
	}

	/* ------------------------ LOGIN RATE LIMIT ------------------------ */
	private static function client_ip() {
		return isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '0.0.0.0';
	}

	private static function login_key( $ip ) {
		return 'cbc_login_fail_' . md5( $ip );
	}

	public static function check_login_rate_limit( $user, $username, $password ) {
		$ip = self::client_ip();
		$failures = (int) get_transient( self::login_key( $ip ) );
		if ( $failures >= self::LOGIN_LIMIT ) {
			return new WP_Error( 'too_many_attempts', __( 'Too many failed login attempts. Please try again in a few minutes.' ) );
		}
		return $user;
	}

	public static function record_login_failure() {
		$ip = self::client_ip();
		$key = self::login_key( $ip );
		$failures = (int) get_transient( $key );
		$failures++;
		set_transient( $key, $failures, self::LOGIN_WINDOW );
	}

	public static function clear_login_failures( $user_login, $user ) {
		$ip = self::client_ip();
		delete_transient( self::login_key( $ip ) );
	}

	/* ------------------------ reCAPTCHA (Login + Comments) ------------------------ */
	private static function recaptcha_enabled() {
		if ( self::is_dev_or_local() ) {
			return false;
		}

		return defined( 'CBC_AI_RECAPTCHA_SITE_KEY' ) && CBC_AI_RECAPTCHA_SITE_KEY && defined( 'CBC_AI_RECAPTCHA_SECRET' ) && CBC_AI_RECAPTCHA_SECRET;
	}

	private static function is_dev_or_local() {
		$env = function_exists( 'wp_get_environment_type' ) ? strtolower( wp_get_environment_type() ) : 'production';
		if ( in_array( $env, array( 'development', 'local', 'dev' ), true ) ) {
			return true;
		}

		$host = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';
		$ip   = self::client_ip();
		$local_hosts = array( 'localhost', '127.0.0.1', '::1' );

		return in_array( $host, $local_hosts, true ) || in_array( $ip, $local_hosts, true );
	}

	private static function recaptcha_script() {
		if ( self::recaptcha_enabled() ) {
			echo "<script src='https://www.google.com/recaptcha/api.js' async defer></script>"; // phpcs:ignore WordPress.Security.EscapeOutput
		}
	}

	public static function render_login_recaptcha() {
		if ( ! self::recaptcha_enabled() ) {
			return;
		}
		self::recaptcha_script();
		echo cbc_recaptcha_field(); // Use the function from cbc-recaptcha plugin to render the field
	}

	public static function verify_login_recaptcha( $user, $username, $password ) {
		if ( ! self::recaptcha_enabled() ) {
			return $user;
		}

		if ( empty( $_POST['g-recaptcha-response'] ) ) {
			return new WP_Error( 'recaptcha_missing', __( 'Please complete the reCAPTCHA.' ) );
		}

		if ( ! self::verify_recaptcha_response( sanitize_text_field( wp_unslash( $_POST['g-recaptcha-response'] ) ) ) ) {
			return new WP_Error( 'recaptcha_failed', __( 'reCAPTCHA verification failed. Please try again.' ) );
		}

		return $user;
	}

	public static function render_comment_recaptcha() {
		if ( ! self::recaptcha_enabled() ) {
			return;
		}
		self::recaptcha_script();
		echo '<p class="comment-form-recaptcha"><label>' . esc_html__( 'Anti-spam check', 'cbc' ) . '</label>';
		cbc_recaptcha_field(); // Use the function from cbc-recaptcha plugin to render the field
		echo '</p>';
	}

	public static function verify_comment_recaptcha( $commentdata ) {
		if ( ! self::recaptcha_enabled() ) {
			return $commentdata;
		}

		if ( empty( $_POST['g-recaptcha-response'] ) ) {
			wp_die( esc_html__( 'reCAPTCHA is required. Please go back and complete the check.', 'cbc' ) );
		}

		if ( ! self::verify_recaptcha_response( sanitize_text_field( wp_unslash( $_POST['g-recaptcha-response'] ) ) ) ) {
			wp_die( esc_html__( 'reCAPTCHA failed. Please go back and try again.', 'cbc' ) );
		}

		return $commentdata;
	}

	private static function verify_recaptcha_response( $token ) {
		$secret = CBC_AI_RECAPTCHA_SECRET;
		$response = wp_remote_post( 'https://www.google.com/recaptcha/api/siteverify', array(
			'body' => array(
				'secret'   => $secret,
				'response' => $token,
				'remoteip' => self::client_ip(),
			),
			'timeout' => 5,
		) );

		if ( is_wp_error( $response ) ) {
			return false;
		}
		$body = wp_remote_retrieve_body( $response );
		$decoded = json_decode( $body, true );
		return ! empty( $decoded['success'] );
	}

	/* ------------------------ Sentry/Rollbar lightweight forwarding ------------------------ */
	private static function send_sentry_like( $message, $level = 'error', $context = array() ) {
		$dsn = defined( 'CBC_SENTRY_DSN' ) ? CBC_SENTRY_DSN : ( getenv( 'CBC_SENTRY_DSN' ) ?: '' );
		if ( ! $dsn ) {
			return;
		}

		// Parse DSN: https://<key>@<host>/<project>
		$parts = wp_parse_url( $dsn );
		if ( empty( $parts['user'] ) || empty( $parts['host'] ) || empty( $parts['path'] ) ) {
			return;
		}
		$public_key = $parts['user'];
		$project_id = ltrim( $parts['path'], '/' );
		$timestamp = time();

		$endpoint = 'https://' . $parts['host'] . '/api/' . $project_id . '/store/';
		$auth = sprintf( 'Sentry sentry_version=7, sentry_client=cbc-security-hardening/1.0, sentry_timestamp=%d, sentry_key=%s', $timestamp, $public_key );

		$payload = array(
			'level' => $level,
			'message' => $message,
			'platform' => 'php',
			'timestamp' => microtime( true ),
			'request' => array(
				'url' => ( isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '' ),
				'method' => ( isset( $_SERVER['REQUEST_METHOD'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) : '' ),
			),
			'contexts' => array( 'extra' => $context ),
		); // @codingStandardsIgnoreLine

		wp_remote_post( $endpoint, array(
			'headers' => array(
				'Content-Type' => 'application/json',
				'X-Sentry-Auth' => $auth,
			),
			'body' => wp_json_encode( $payload ),
			'timeout' => 3,
		) );
	}

	public static function capture_exception( $exception ) {
		self::send_sentry_like( $exception->getMessage(), 'error', array( 'trace' => $exception->getTraceAsString() ) );
		// Let WordPress handle default behavior
		restore_exception_handler();
		throw $exception;
	}

	public static function capture_error( $errno, $errstr, $errfile, $errline ) {
		// Respect @-silenced errors
		if ( error_reporting() === 0 ) {
			return false;
		}
		self::send_sentry_like( "$errstr in $errfile:$errline", 'error', array( 'code' => $errno ) );
		return false; // continue normal PHP handling
	}

	public static function capture_shutdown() {
		$error = error_get_last();
		if ( $error && in_array( $error['type'], array( E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR ), true ) ) {
			self::send_sentry_like( $error['message'] . ' in ' . $error['file'] . ':' . $error['line'], 'fatal' );
		}
	}
}

CBC_Security_Hardening::init();
