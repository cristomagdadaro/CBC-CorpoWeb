<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

if ( file_exists( __DIR__ . '/wp-config-local.php' ) ) {
	require_once __DIR__ . '/wp-config-local.php';
}

if ( ! function_exists( 'cbc_config_env' ) ) {
	/**
	 * Read configuration from environment variables with a fallback.
	 *
	 * @param string $key Environment variable name.
	 * @param mixed  $default Fallback value when the variable is unset.
	 * @return mixed
	 */
	function cbc_config_env( string $key, $default = '' ) {
		$value = getenv( $key );

		if ( false !== $value && '' !== $value ) {
			return $value;
		}

		if ( isset( $_ENV[ $key ] ) && '' !== $_ENV[ $key ] ) {
			return $_ENV[ $key ];
		}

		if ( isset( $_SERVER[ $key ] ) && '' !== $_SERVER[ $key ] ) {
			return $_SERVER[ $key ];
		}

		return $default;
	}
}

if ( ! function_exists( 'cbc_config_truthy' ) ) {
	/**
	 * Convert common string values into a boolean.
	 */
	function cbc_config_truthy( string $value ): bool {
		return in_array( strtolower( trim( $value ) ), array( '1', 'true', 'yes', 'on' ), true );
	}
}

if ( ! function_exists( 'cbc_config_trailing_slash_url' ) ) {
	/**
	 * Normalize URLs to WordPress's expected trailing slash format.
	 */
	function cbc_config_trailing_slash_url( string $url ): string {
		return rtrim( $url, '/' ) . '/';
	}
}

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', cbc_config_env( 'WP_DB_NAME', 'database_name_here' ) );

/** Database username */
define( 'DB_USER', cbc_config_env( 'WP_DB_USER', 'username_here' ) );

/** Database password */
define( 'DB_PASSWORD', cbc_config_env( 'WP_DB_PASSWORD', 'password_here' ) );

/** Database hostname */
define( 'DB_HOST', cbc_config_env( 'WP_DB_HOST', 'localhost' ) );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

if ( ! defined( 'WP_CLI' ) ) {
	$forwarded_proto = strtolower( trim( (string) cbc_config_env( 'HTTP_X_FORWARDED_PROTO', '' ) ) );
	$https_enabled   = cbc_config_truthy( (string) cbc_config_env( 'WP_FORCE_HTTPS', '' ) )
		|| 'https' === $forwarded_proto
		|| 'on' === strtolower( (string) ( $_SERVER['HTTPS'] ?? '' ) );

	if ( $https_enabled ) {
		$_SERVER['HTTPS']          = 'on';
		$_SERVER['REQUEST_SCHEME'] = 'https';
	}

	$site_url = cbc_config_trailing_slash_url( (string) cbc_config_env( 'WP_SITEURL', 'http://127.0.0.1:8000' ) );
	$home_url = cbc_config_trailing_slash_url( (string) cbc_config_env( 'WP_HOME', $site_url ) );

	define( 'WP_SITEURL', $site_url );
	define( 'WP_HOME', $home_url );
}

/**#@+
 * Authentication unique keys and salts.
 *
 * Generate real values outside the tracked repository and inject them through
 * environment variables or an untracked wp-config-local.php.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         cbc_config_env( 'WP_AUTH_KEY', 'put your unique phrase here' ) );
define( 'SECURE_AUTH_KEY',  cbc_config_env( 'WP_SECURE_AUTH_KEY', 'put your unique phrase here' ) );
define( 'LOGGED_IN_KEY',    cbc_config_env( 'WP_LOGGED_IN_KEY', 'put your unique phrase here' ) );
define( 'NONCE_KEY',        cbc_config_env( 'WP_NONCE_KEY', 'put your unique phrase here' ) );
define( 'AUTH_SALT',        cbc_config_env( 'WP_AUTH_SALT', 'put your unique phrase here' ) );
define( 'SECURE_AUTH_SALT', cbc_config_env( 'WP_SECURE_AUTH_SALT', 'put your unique phrase here' ) );
define( 'LOGGED_IN_SALT',   cbc_config_env( 'WP_LOGGED_IN_SALT', 'put your unique phrase here' ) );
define( 'NONCE_SALT',       cbc_config_env( 'WP_NONCE_SALT', 'put your unique phrase here' ) );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', cbc_config_truthy( (string) cbc_config_env( 'WP_DEBUG', 'false' ) ) );

/* Add any custom values between this line and the "stop editing" line. */
define( 'CBC_AI_RECAPTCHA_SITE_KEY', cbc_config_env( 'CBC_AI_RECAPTCHA_SITE_KEY', cbc_config_env( 'RECAPTCHA_SITE_KEY', '' ) ) );
define( 'CBC_AI_RECAPTCHA_SECRET', cbc_config_env( 'CBC_AI_RECAPTCHA_SECRET', cbc_config_env( 'RECAPTCHA_SECRET_KEY', '' ) ) );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
