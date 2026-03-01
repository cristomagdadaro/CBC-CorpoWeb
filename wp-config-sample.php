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
 * @link https://wordpress.org/documentation/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'DA-CBC' );

/** Database username — CHANGE for production: use a dedicated DB user, never 'root' */
define( 'DB_USER', 'da_cbc_user' );

/** Database password — CHANGE for production: use a strong, unique password */
define( 'DB_PASSWORD', 'CHANGE_ME_BEFORE_DEPLOY' );

/** Database hostname */
define( 'DB_HOST', '192.168.36.10' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/** Force SSL for admin */
define( 'FORCE_SSL_ADMIN', true );

/** Disable file editing from the dashboard */
define( 'DISALLOW_FILE_EDIT', true );

if ( !defined('WP_CLI') ) {
    define( 'WP_SITEURL', 'https://' . $_SERVER['HTTP_HOST'] );
    define( 'WP_HOME',    'https://' . $_SERVER['HTTP_HOST'] );
}



/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
/**
 * IMPORTANT: These salts were exposed in a public Git repository.
 * They have been regenerated. Visit https://api.wordpress.org/secret-key/1.1/salt/
 * to generate fresh values before deploying to production.
 */
define( 'AUTH_KEY',         'Vk9$r!xZpL3mT#wBqY7&hNfD2cJ8sKgE0uAoRiW5jXeP4nMdFaUyCbHvGtQzSlO' );
define( 'SECURE_AUTH_KEY',  'Hw2@pN8kLm5vRzXg3#yCtFbJ0KsQeAoUd7&iWjMrEnTlYxSfDqGhPaVuBcZ9!O6' );
define( 'LOGGED_IN_KEY',    'Qj4#nRzKpW7sYmL0vFdBgX2hCeT$8lNaU5kOiAoSxVfGtJbMrEcDwHqP3&yZ9u!' );
define( 'NONCE_KEY',        'Tk6&mXwPfZ3rLjN8$vBhYsKdC0eAoQ5gUiRnWtFpSxJcMbDaEyHqGlV2#u9O7!' );
define( 'AUTH_SALT',        'Sn3!hWzMfR8kPbY5$gCjXeT0vLaKdOiAoQ7lUxFpNtJcBrEwSyHqGmDV6&u2Z9#' );
define( 'SECURE_AUTH_SALT', 'Ym7#kXzLfW2rPjN5$vBhTsKdC8eAoQ0gUiRnStFpMxJcDbEaYyHqGlV3&u9O6!' );
define( 'LOGGED_IN_SALT',   'Fk8&rNzXpW5sMmL2$vYdBgT0hCeAoQ3lUiKnJtFpSxRcBbDwEyHqGaV7#u9O6!' );
define( 'NONCE_SALT',       'Gj9!nRzKpW4sTmL7$vFdBhX2eCeAoQ8lUiYnMtFpSxJcBrDwEkHqGlV5&u3O6#' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
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
 * @link https://wordpress.org/documentation/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */
set_time_limit(300);
define('CBC_AI_RECAPTCHA_SITE_KEY', '6LeeNP4rAAAAAFMRR66j025aflgqj2YaTkjbKLSw');
define('CBC_AI_RECAPTCHA_SECRET', '6LeeNP4rAAAAAMQqqlWPUrqmKJLKQJD12PhjcxR1');
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
