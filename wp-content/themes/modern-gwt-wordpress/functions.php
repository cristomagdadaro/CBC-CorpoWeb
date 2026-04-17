<?php
/**
 * gwt_wp functions and definitions
 *
 * @package gwt_wp
 */

/**
 * Template Initialize
 */
require get_template_directory() . '/inc/function-initialize.php';

/**
 * Load custom nav menu walker (Tailwind-style markup)
 */
require get_template_directory() . '/inc/class-gwt-walker-nav-menu.php';

function gwt_register_menu() {
	register_nav_menu('primary', __('Primary Menu', 'gwt'));
}
add_action('after_setup_theme', 'gwt_register_menu');

/**
 * Register widgetized area
 */
require get_template_directory() . '/inc/function-widget.php';

/**
 * Breadcrumbs
 */
require get_template_directory() . '/inc/function-breadcrumbs.php';

/**
 * Govph Excerpt
 */
require get_template_directory() . '/inc/function-excerpt.php';

/**
 * Enqueue scripts and styles
 */
require get_template_directory() . '/inc/function-enqueue_scripts.php';

/**
 * Disable comment functions
 */
require get_template_directory() . '/inc/function-disable_comments.php';

/**
 * GovPH default widgets
 */
require get_template_directory() . '/inc/govph-widget.php';

/**
 * Default sidebar contents
 */
require get_template_directory() . '/inc/sidebar.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Custom functions that act independently of the theme templates.
 */
require get_template_directory() . '/inc/extras.php';

/**
 * Customizer additions.
 */
// require get_template_directory() . '/inc/customizer.php';

/**
 * Load Jetpack compatibility file.
 */
// require get_template_directory() . '/inc/jetpack.php';

/**
 * Theme Options Page.
 */
require get_template_directory() . '/inc/function-options.php';

/**
 * Theme helpers
 */
require get_template_directory() . '/inc/function-helpers.php';

/**
 * Priority Commodities Carousel Shortcode
 */
require get_template_directory() . '/inc/shortcode-priority-commodities-carousel.php';

/**
 * Event Hall Carousel Shortcode
 */
require get_template_directory() . '/inc/shortcode-event-halls-carousel.php';

/**
 * Organizational Shortcode
 */
require get_template_directory() . '/inc/shortcode-gov-org.php';

/**
 * Core Programs Shortcode
 */
require get_template_directory() . '/inc/shortcode-core-programs.php';

/**
 * Our Impact Shortcode
 */
require get_template_directory() . '/inc/shortcode-our-impact.php';

/**
 * Custom Post Types
 */
// require get_template_directory() . '/inc/custom-post-types.php';


/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * Envato Flexslider
 */
require get_template_directory() . '/inc/vendors/envato-flex-slider/envato-flex-slider.php';

/**
 * Disable rest api for users additions.
 */
require get_template_directory() . '/inc/function-disable_api.php';

/**
 * GWT only works in WordPress 4.4 or later.
 */
if ( version_compare( $GLOBALS['wp_version'], '4.4-alpha', '<' ) ) {
	require get_template_directory() . '/inc/back-compat.php';
}

function block_frames() {
	header( 'X-FRAME-OPTIONS: SAMEORIGIN' );
}
add_action( 'send_headers', 'block_frames', 10 );


/**
 * Enable classic widgets or disabled gutenberg style for widgets.
 */
require get_template_directory() . '/inc/function-enable-classic-widgets.php';

/**
 * Enable classic posts or disabled gutenberg style for posts.
 */
require get_template_directory() . '/inc/function-enable-classic-posts.php';

function enqueue_particles_js() {
	// Load particles.js library
	wp_enqueue_script(
		'particles-js',
		get_stylesheet_directory_uri() . '/js/particles.min.js',
		array(),
		null,
		true
	);

	// Load your custom init script
	wp_enqueue_script(
		'particles-init',
		get_stylesheet_directory_uri() . '/js/particles-init.js',
		array('particles-js'),
		null,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'enqueue_particles_js' );

/**
 * Facebook Domain Verification Meta Tag
 * Add the Facebook domain verification meta tag to the head section of the site.
*/
add_action('wp_head', function() {
	echo '<meta name="facebook-domain-verification" content="i5w8r6xe2xh7kdwq4e3dx22g2ij28v">' . "\n";
});

/**
 * Facebook App ID Meta Tag
 * Add the Facebook App ID meta tag to the head section of the site.
*/
add_action('wp_head', function() {
	echo '<meta property="fb:app_id" content="807761025286157">' . "\n";
});


/**
 * Enqueue slide animation helper
 */
function gwt_enqueue_slide_anim(){
	wp_enqueue_script(
		'gwt-slide-anim',
		get_stylesheet_directory_uri() . '/js/slide-anim.js',
		array(),
		null,
		true
	);
}
add_action('wp_enqueue_scripts','gwt_enqueue_slide_anim');

/**
 * Lightweight page cache for anonymous visitors (helps Site Health detect page caching).
 *
 * Only enable this caching when WP_DEBUG is false. During development (WP_DEBUG === true)
 * the cache is disabled so changes to widgets/templates are reflected immediately.
 */
if ( defined('WP_DEBUG') && ! WP_DEBUG ) {
	if ( ! function_exists( 'cbc_page_cache_is_eligible' ) ) {
		function cbc_page_cache_is_eligible() {
			if ( is_user_logged_in() || is_admin() ) return false;
			$method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
			if ( strtoupper($method) !== 'GET' ) return false;
			if ( ! empty($_GET['nocache']) ) return false;
			// Let login/register/preview and feeds bypass caching
			if ( is_feed() || is_trackback() || is_preview() ) return false;
			return true;
		}
	}

	if ( ! function_exists( 'cbc_page_cache_key' ) ) {
		function cbc_page_cache_key() {
			$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
			$uri  = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
			$scheme = ( is_ssl() ? 'https://' : 'http://' );
			$url = $scheme . $host . $uri;
			$version = intval( get_option('cbc_page_cache_version', 1) );
			return 'cbc_pg_' . md5( $url . '|' . $version );
		}
	}

	if ( ! function_exists( 'cbc_page_cache_is_cacheable_status' ) ) {
		function cbc_page_cache_is_cacheable_status() {
			$code = function_exists('http_response_code') ? intval(http_response_code()) : 200;
			return ($code >= 200 && $code < 300);
		}
	}

	if ( ! function_exists( 'cbc_page_cache_maybe_serve' ) ) {
		function cbc_page_cache_maybe_serve() {
			if ( ! cbc_page_cache_is_eligible() ) return;
			$ttl = intval( apply_filters('cbc_page_cache_ttl', 600) ); // 10 minutes
			$key = cbc_page_cache_key();
			$cached = get_transient( $key );
			if ( is_array($cached) && isset($cached['body'], $cached['ts']) ) {
				$age = max(0, time() - intval($cached['ts']));
				header('Cache-Control: public, max-age=' . $ttl);
				header('Age: ' . $age);
				header('X-Cache: HIT');
				echo $cached['body'];
				exit;
			}
			// MISS: Start buffering and save on output
			header('Cache-Control: public, max-age=' . $ttl);
			header('X-Cache: MISS');
			ob_start( function($buffer) use ($key, $ttl) {
				if ( cbc_page_cache_is_cacheable_status() && ! empty($buffer) ) {
					set_transient( $key, [ 'body' => $buffer, 'ts' => time() ], $ttl );
				}
				return $buffer;
			});
		}
		add_action( 'template_redirect', 'cbc_page_cache_maybe_serve', 0 );
	}

	if ( ! function_exists( 'cbc_page_cache_bump_version' ) ) {
		function cbc_page_cache_bump_version() {
			$ver = intval( get_option('cbc_page_cache_version', 1) );
			update_option('cbc_page_cache_version', $ver + 1, false);
		}
		add_action('save_post', 'cbc_page_cache_bump_version');
		add_action('deleted_post', 'cbc_page_cache_bump_version');
		add_action('trashed_post', 'cbc_page_cache_bump_version');
		add_action('switch_theme', 'cbc_page_cache_bump_version');
	}
}
?>