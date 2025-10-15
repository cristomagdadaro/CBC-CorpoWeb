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
define( 'DB_NAME', 'da-cbc' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

if ( !defined('WP_CLI') ) {
	$_SERVER['REQUEST_SCHEME'] = 'http';
	define( 'WP_SITEURL', $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] );
	define( 'WP_HOME',    $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] );
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
define( 'AUTH_KEY',         'MRx4qpdLKbkxyHqRYh65zysjcBd67eWCAGkPyUgOD94Zqblhq9haDX4kEi2c1FWK' );
define( 'SECURE_AUTH_KEY',  'T7cwpSD5samE8nuAqzhlrYpQpRst1hJOTOxW12Iv3nX7mQaOgAZBg5i6EM11TvVa' );
define( 'LOGGED_IN_KEY',    'xTKVqEqdmjuP8L4V7M059ixw75Za7hv91FGkx4hnof3E23Qx2jYUcJcwYLHXuv80' );
define( 'NONCE_KEY',        'flYteHTMnLpcEA6wHM0w7nPV9y627HCUXueJQWeuLQakxLgwFIfaX8uFNDDusX6o' );
define( 'AUTH_SALT',        'LO0BESi65hXZz7JnAmOLccBbaduonTJkqoz4Md3S8ibAxUrLG2DzglEH9TQhi4JR' );
define( 'SECURE_AUTH_SALT', 'pBH2FD5eYp7aAviJhyHuSwISRp36tTvExfIqjeBKtbg9d78vsKCSc1y9sTxdT4UQ' );
define( 'LOGGED_IN_SALT',   'yFIy4KlKK4vRUoB5bgr9UPgfeN44P0XAgR950igySMGPyxO0d3YNJtufHN9OtChT' );
define( 'NONCE_SALT',       'qgEsR32m2ModikOM3jbrChvV6X4N7zpYg7H4rTaggaqn06CJyHuq4GeaJOaiRGbg' );

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
define( 'WP_DEBUG', true);

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
