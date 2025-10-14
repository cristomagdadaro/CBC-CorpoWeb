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
define('AUTH_KEY',         '$_08Ek@s@s3?)DBdY<-3+,)60[9c/){29 +uQEv+KO%^82>iR+zVDy-}y::H=XN@');
define('SECURE_AUTH_KEY',  'l$&++01-*!NY.}et[R<PmS1 |-)J^jbwxS;:~5G7XOkgd=x9`x9]PXwC1E Ry&?z');
define('LOGGED_IN_KEY',    'E,-&.KBa1$WR wsdr<f|JGpg~d#>TmW/Z3,R7/@-Jgc=WH)SPnX+ozkAAn UM!|f');
define('NONCE_KEY',        'n}b=pmXS!:w9Jo&=?PeJCR-u3}M(m[i,z7+Z$?AXu6jqIF@Kr0@Uwqsih}s3[L$f');
define('AUTH_SALT',        ';BV_yegDF1Rum6oKZyJL2m7dn~%2j5`K`o#Pnwh} Pz(Sdqy}nt40EFQ#Y)4OtH+');
define('SECURE_AUTH_SALT', 'Rno;%|!7TYZ~9OF=TX+/;hluOb-j&g<0RB>JO|Zb|*+:,?+W-,`YmrMNiG!EKa(/');
define('LOGGED_IN_SALT',   ':+nIgf@|JI(|I|@-2gs3vI]6E}aw @AF+_s(>Bh7-&jWc*5hf[qo6>pm`0ja{J3R');
define('NONCE_SALT',       'a|mrgyxi1%bafYYP>up_7AYem%{uRH$J9{/<moa,Wy0GcZsjyWx)`D|%nf1Xo~oa');

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
define( 'WP_DEBUG', true );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
