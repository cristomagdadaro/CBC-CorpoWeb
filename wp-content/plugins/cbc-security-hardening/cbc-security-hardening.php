<?php
/**
 * Plugin Name: CBC Security Hardening
 * Description: Security headers, login protections, reCAPTCHA, basic monitoring endpoint, and optional Sentry/Rollbar forwarding.
 * Version: 1.0.0
 * Author: Cristo Rey C. Magdadaro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CBC_Security_Hardening {
	const LOGIN_LIMIT = 5; // attempts
	const LOGIN_WINDOW = 15 * MINUTE_IN_SECONDS;
	const MONITOR_CAPABILITY = 'manage_options';

	public static function init() {
		// Headers
		add_action( 'send_headers', array( __CLASS__, 'send_security_headers' ) );
		add_filter( 'wp_headers', array( __CLASS__, 'remove_pingback_headers' ) );

		// XML-RPC hardening
		add_action( 'init', array( __CLASS__, 'maybe_block_xmlrpc' ), 0 );
		add_filter( 'xmlrpc_enabled', array( __CLASS__, 'filter_xmlrpc_enabled' ) );
		add_filter( 'xmlrpc_methods', array( __CLASS__, 'filter_xmlrpc_methods' ) );

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
				'permission_callback' => array( __CLASS__, 'monitor_permission' ),
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

	private static function xmlrpc_allowed() {
		return defined( 'CBC_ENABLE_XMLRPC' ) && CBC_ENABLE_XMLRPC;
	}

	private static function request_path() {
		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
		$path        = $request_uri ? wp_parse_url( $request_uri, PHP_URL_PATH ) : '';

		return is_string( $path ) ? $path : '';
	}

	private static function anonymize_for_logs( $value ) {
		if ( '' === (string) $value ) {
			return '';
		}

		return hash_hmac( 'sha256', (string) $value, wp_salt( 'auth' ) );
	}

	private static function sanitize_log_context( array $context ) {
		$sanitized = array();

		foreach ( $context as $key => $value ) {
			$key = sanitize_key( (string) $key );

			if ( is_scalar( $value ) || null === $value ) {
				$sanitized[ $key ] = sanitize_text_field( (string) $value );
			}
		}

		return $sanitized;
	}

	private static function log_security_event( $event, array $context = array(), $level = 'notice' ) {
		if ( defined( 'CBC_DISABLE_SECURITY_EVENT_LOGS' ) && CBC_DISABLE_SECURITY_EVENT_LOGS ) {
			return;
		}

		$payload = array(
			'source' => 'cbc-security-hardening',
			'event'  => sanitize_key( (string) $event ),
			'level'  => sanitize_key( (string) $level ),
			'time'   => gmdate( 'c' ),
			'route'  => self::request_path(),
			'ip_hash' => self::anonymize_for_logs( self::client_ip() ),
			'context' => self::sanitize_log_context( $context ),
		);

		error_log( 'cbc_security_event ' . wp_json_encode( $payload ) );
	}

	public static function maybe_block_xmlrpc() {
		if ( self::xmlrpc_allowed() ) {
			return;
		}

		$path = self::request_path();
		if ( '' === $path || 'xmlrpc.php' !== wp_basename( $path ) ) {
			return;
		}

		self::log_security_event( 'xmlrpc_blocked', array( 'method' => isset( $_SERVER['REQUEST_METHOD'] ) ? wp_unslash( $_SERVER['REQUEST_METHOD'] ) : '' ), 'warning' );

		status_header( 403 );
		nocache_headers();
		header( 'Content-Type: text/plain; charset=utf-8' );
		echo esc_html__( 'XML-RPC is disabled on this site.', 'cbc-security-hardening' );
		exit;
	}

	public static function filter_xmlrpc_enabled( $enabled ) {
		return self::xmlrpc_allowed() ? $enabled : false;
	}

	public static function filter_xmlrpc_methods( $methods ) {
		if ( self::xmlrpc_allowed() ) {
			return $methods;
		}

		return array();
	}

	public static function remove_pingback_headers( $headers ) {
		if ( isset( $headers['X-Pingback'] ) ) {
			unset( $headers['X-Pingback'] );
		}

		return $headers;
	}

	public static function monitor_permission( $request ) {
		if ( defined( 'CBC_MONITOR_PUBLIC_ENDPOINT' ) && CBC_MONITOR_PUBLIC_ENDPOINT ) {
			return true;
		}

		if ( current_user_can( self::MONITOR_CAPABILITY ) ) {
			return true;
		}

		self::log_security_event( 'monitor_access_denied', array( 'method' => $request instanceof WP_REST_Request ? $request->get_method() : '' ), 'warning' );

		return new WP_Error( 'cbc_monitor_forbidden', __( 'You are not allowed to access this monitoring endpoint.', 'cbc-security-hardening' ), array( 'status' => 403 ) );
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
		$local_dev = self::is_dev_or_local();

		// HSTS only on HTTPS
		if ( ! $local_dev && is_ssl() ) {
			header( 'Strict-Transport-Security: max-age=31536000; includeSubDomains; preload' );
		}

		header( 'X-Content-Type-Options: nosniff' );
		header( 'X-Frame-Options: SAMEORIGIN' );
		header( 'Referrer-Policy: strict-origin-when-cross-origin' );
		header( 'Permissions-Policy: geolocation=(), microphone=(), camera=()' );
		header( 'Cross-Origin-Opener-Policy: same-origin' );

		if ( $local_dev ) {
			return;
		}

		$csp_parts = array(
			"default-src 'self'",
			"script-src 'self' 'unsafe-inline' 'unsafe-eval' https://gwhs.i.gov.ph https://www.googletagmanager.com https://www.google-analytics.com https://www.google.com https://www.gstatic.com https://accounts.google.com",
			"style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
			"img-src 'self' data: https://www.google-analytics.com https://www.googletagmanager.com https:",
			"font-src 'self' https://fonts.gstatic.com data:",
			"connect-src 'self' https://api.openai.com https://openrouter.ai https://gwhs.i.gov.ph https://www.google-analytics.com https://www.googletagmanager.com https://www.google.com https://www.gstatic.com https://accounts.google.com",
			"frame-src 'self' https://www.google.com https://www.youtube.com https://accounts.google.com",
			"worker-src 'self' blob:",
			"object-src 'none'",
			"base-uri 'self'",
			"form-action 'self' https://www.google.com https://accounts.google.com",
		);

		if ( defined( 'CBC_CSP_ENFORCE' ) && CBC_CSP_ENFORCE ) {
			$csp_parts[] = 'upgrade-insecure-requests';
		}

		$csp = implode( '; ', $csp_parts );

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
			self::log_security_event( 'login_rate_limited', array( 'username_hash' => self::anonymize_for_logs( (string) $username ) ), 'warning' );
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

		if ( $failures >= self::LOGIN_LIMIT ) {
			self::log_security_event( 'login_lockout_threshold_reached', array( 'failures' => $failures ), 'warning' );
		}
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
			$language = apply_filters( 'cbc_recaptcha_language', 'en' );
			$language = is_string( $language ) ? strtolower( trim( $language ) ) : 'en';
			if ( ! preg_match( '/^[a-z]{2,3}(?:-[a-z]{2})?$/', $language ) ) {
				$language = 'en';
			}
			$script_url = add_query_arg( 'hl', $language, 'https://www.google.com/recaptcha/api.js' );
			echo '<script src="' . esc_url( $script_url ) . '" async defer></script>'; // phpcs:ignore WordPress.Security.EscapeOutput
		}
	}

	public static function render_login_recaptcha() {
		if ( ! self::recaptcha_enabled() ) {
			return;
		}
		self::recaptcha_script();
		cbc_recaptcha_field(); // Use the function from cbc-recaptcha plugin to render the field
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
