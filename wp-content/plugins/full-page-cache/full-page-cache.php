<?php
/**
 * Plugin Name: Full Page Cache
 * Description: Simple full front-end HTML page caching using WordPress transients. Skips logged-in users, admin, AJAX, REST, feeds, search, previews, and requests with ?nocache=1. Includes TTL, exclusions, purge button, and automatic purge on content/comment changes.
 * Version: 0.1
 * Author: Cristo Rey C. Magdadaro
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'FPC_Full_Page_Cache' ) ) {
	class FPC_Full_Page_Cache {
		private static $buffering = false;
		private static $cache_key = '';

		public static function init() {
			add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
			add_action( 'template_redirect', array( __CLASS__, 'maybe_serve_cache' ), 0 );
			add_action( 'shutdown', array( __CLASS__, 'maybe_store_cache' ), 9999 );
			add_action( 'admin_post_fpc_purge_all', array( __CLASS__, 'handle_manual_purge' ) );
			// Auto purge on content / comment changes
			add_action( 'save_post', array( __CLASS__, 'auto_purge' ), 20, 2 );
			add_action( 'deleted_post', array( __CLASS__, 'auto_purge_simple' ), 20 );
			add_action( 'trashed_post', array( __CLASS__, 'auto_purge_simple' ), 20 );
			add_action( 'comment_post', array( __CLASS__, 'auto_purge_simple' ), 20 );
			add_action( 'edit_comment', array( __CLASS__, 'auto_purge_simple' ), 20 );
			add_action( 'deleted_comment', array( __CLASS__, 'auto_purge_simple' ), 20 );
		}

		/* ---------- Settings ---------- */
		public static function register_settings() {
			register_setting( 'general', 'fpc_enabled', array( 'type' => 'boolean', 'sanitize_callback' => 'absint', 'default' => 0 ) );
			register_setting( 'general', 'fpc_ttl_minutes', array( 'type' => 'integer', 'sanitize_callback' => 'absint', 'default' => 10 ) );
			register_setting( 'general', 'fpc_exclusions', array( 'type' => 'string', 'sanitize_callback' => array( __CLASS__, 'sanitize_exclusions' ), 'default' => '' ) );
			add_settings_field( 'fpc_enabled', __( 'Full Page Cache', 'full-page-cache' ), array( __CLASS__, 'field_enabled' ), 'general' );
			add_settings_field( 'fpc_ttl_minutes', __( 'Full Page Cache TTL (minutes)', 'full-page-cache' ), array( __CLASS__, 'field_ttl' ), 'general' );
			add_settings_field( 'fpc_exclusions', __( 'Full Page Cache Exclusions', 'full-page-cache' ), array( __CLASS__, 'field_exclusions' ), 'general' );
			add_settings_field( 'fpc_purge', __( 'Full Page Cache Purge', 'full-page-cache' ), array( __CLASS__, 'field_purge' ), 'general' );
		}
		public static function sanitize_exclusions( $value ) {
			return trim( wp_strip_all_tags( (string) $value ) );
		}
		public static function field_enabled() {
			$val = get_option( 'fpc_enabled', 0 );
			echo '<label><input type="checkbox" name="fpc_enabled" value="1" ' . checked( 1, $val, false ) . ' /> ' . esc_html__( 'Enable full page output caching.', 'full-page-cache' ) . '</label>';
			echo '<p class="description">' . esc_html__( 'Skips logged-in users, admin, AJAX, REST, feeds, search, previews, CLI, non-GET requests, and URLs with ?nocache=1. Disable for development.', 'full-page-cache' ) . '</p>';
		}
		public static function field_ttl() {
			$ttl = (int) get_option( 'fpc_ttl_minutes', 10 );
			echo '<input type="number" min="1" name="fpc_ttl_minutes" value="' . esc_attr( $ttl ) . '" style="width:80px" />';
			echo '<p class="description">' . esc_html__( 'Lifetime for cached HTML.', 'full-page-cache' ) . '</p>';
		}
		public static function field_exclusions() {
			$raw = get_option( 'fpc_exclusions', '' );
			echo '<textarea name="fpc_exclusions" rows="5" class="large-text code" placeholder="/contact\n/blog/page/">' . esc_textarea( $raw ) . '</textarea>';
			echo '<p class="description">' . esc_html__( 'One partial path per line. If REQUEST_URI contains a fragment, that page will not be cached.', 'full-page-cache' ) . '</p>';
		}
		public static function field_purge() {
			$url = wp_nonce_url( admin_url( 'admin-post.php?action=fpc_purge_all' ), 'fpc_purge_all' );
			$count = self::cache_index_count();
			echo '<a class="button" href="' . esc_url( $url ) . '" onclick="return confirm(\'' . esc_js( __( 'Purge all cached pages?', 'full-page-cache' ) ) . '\');">' . esc_html__( 'Purge Full Page Cache', 'full-page-cache' ) . '</a>';
			echo '<p class="description">' . sprintf( esc_html__( '%d cached page entries.', 'full-page-cache' ), intval( $count ) ) . '</p>';
		}

		/* ---------- Polyfills ---------- */
		protected static function starts_with( $haystack, $needle ) {
			if ( function_exists( 'str_starts_with' ) ) return str_starts_with( $haystack, $needle );
			return substr( $haystack, 0, strlen( $needle ) ) === $needle;
		}
		protected static function contains( $haystack, $needle ) {
			if ( function_exists( 'str_contains' ) ) return str_contains( $haystack, $needle );
			return $needle === '' || strpos( $haystack, $needle ) !== false;
		}

		/* ---------- Core logic ---------- */
		protected static function enabled() {
			return (bool) get_option( 'fpc_enabled', 0 );
		}
		protected static function ttl_seconds() {
			$ttl = (int) get_option( 'fpc_ttl_minutes', 10 );
			if ( $ttl <= 0 ) $ttl = 10;
			return $ttl * MINUTE_IN_SECONDS;
		}
		protected static function exclusions() {
			$raw = get_option( 'fpc_exclusions', '' );
			return array_filter( array_map( 'trim', preg_split( '/\r?\n/', $raw ) ) );
		}
		protected static function should_bypass() {
			if ( ! self::enabled() ) return true;
			if ( defined( 'DONOTCACHEPAGE' ) && DONOTCACHEPAGE ) return true;
			if ( defined( 'WP_INSTALLING' ) && WP_INSTALLING ) return true;
			if ( defined( 'WP_CLI' ) && WP_CLI ) return true;
			if ( is_user_logged_in() ) return true;
			if ( is_admin() ) return true;
			if ( wp_doing_ajax() ) return true;
			$uri = $_SERVER['REQUEST_URI'] ?? '';
			if ( ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || self::starts_with( $uri, '/wp-json/' ) ) return true;
			$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
			if ( $method !== 'GET' ) return true;
			if ( isset( $_GET['nocache'] ) || isset( $_GET['preview'] ) ) return true;
			if ( is_search() || is_feed() ) return true;
			if ( function_exists( 'is_404' ) && is_404() ) return true;
			foreach ( self::exclusions() as $frag ) {
				if ( $frag !== '' && self::contains( $uri, $frag ) ) return true;
			}
			return false;
		}
		protected static function cache_key() {
			$host = $_SERVER['HTTP_HOST'] ?? 'host';
			$uri  = $_SERVER['REQUEST_URI'] ?? '/';
			return 'fpc_html_' . md5( $host . '|' . $uri );
		}

		public static function maybe_serve_cache() {
			if ( self::should_bypass() ) return;
			$key    = self::cache_key();
			$cached = get_transient( $key );
			if ( is_array( $cached ) && isset( $cached['body'] ) ) {
				header( 'X-FPC: HIT' );
				$etag = 'W/"' . md5( $cached['body'] ) . '"';
				header( 'ETag: ' . $etag );
				if ( isset( $_SERVER['HTTP_IF_NONE_MATCH'] ) && $_SERVER['HTTP_IF_NONE_MATCH'] === $etag ) {
					header( 'HTTP/1.1 304 Not Modified' );
					exit;
				}
				echo $cached['body'];
				exit;
			}
			header( 'X-FPC: MISS' );
			self::$buffering = true;
			self::$cache_key = $key;
			ob_start();
		}

		public static function maybe_store_cache() {
			if ( ! self::$buffering ) return;
			if ( self::should_bypass() ) { self::$buffering = false; return; }
			$body = ob_get_contents();
			ob_end_flush();
			self::$buffering = false;
			if ( empty( $body ) ) return;
			$status = http_response_code();
			if ( $status !== 200 ) return;
			$ctype = '';
			foreach ( headers_list() as $hdr ) {
				if ( stripos( $hdr, 'content-type:' ) === 0 ) { $ctype = trim( substr( $hdr, 13 ) ); break; }
			}
			if ( $ctype && stripos( $ctype, 'text/html' ) === false ) return;
			$key = self::$cache_key;
			if ( ! $key ) return;
			set_transient( $key, array( 'body' => $body, 'time' => time() ), self::ttl_seconds() );
			self::cache_index_add( $key );
		}

		/* ---------- Index / Purge ---------- */
		protected static function cache_index_add( $key ) {
			$idx = get_option( 'fpc_index', array() );
			if ( ! is_array( $idx ) ) $idx = array();
			if ( ! in_array( $key, $idx, true ) ) {
				$idx[] = $key; update_option( 'fpc_index', $idx, false );
			}
		}
		protected static function cache_index_count() {
			$idx = get_option( 'fpc_index', array() );
			return is_array( $idx ) ? count( $idx ) : 0;
		}
		public static function purge_all() {
			$idx = get_option( 'fpc_index', array() );
			if ( is_array( $idx ) ) {
				foreach ( $idx as $k ) delete_transient( $k );
			}
			update_option( 'fpc_index', array(), false );
		}
		public static function handle_manual_purge() {
			if ( ! current_user_can( 'manage_options' ) ) wp_die( esc_html__( 'Insufficient permissions', 'full-page-cache' ) );
			check_admin_referer( 'fpc_purge_all' );
			self::purge_all();
			wp_safe_redirect( wp_get_referer() ? wp_get_referer() : admin_url( 'options-general.php' ) );
			exit;
		}
		public static function auto_purge( $post_id, $post ) {
			if ( wp_is_post_revision( $post_id ) ) return; self::purge_all();
		}
		public static function auto_purge_simple() { self::purge_all(); }
	}
	FPC_Full_Page_Cache::init();
}
