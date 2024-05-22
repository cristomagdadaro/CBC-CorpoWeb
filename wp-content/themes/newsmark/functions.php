<?php
/**
 * Theme functions and definitions
 *
 * @package Newsmark
 */
if ( ! function_exists( 'newsmark_enqueue_styles' ) ) :
	/**
	 * @since 0.1
	 */
	function newsmark_enqueue_styles() {
		wp_enqueue_style( 'newsup-style-parent', get_template_directory_uri() . '/style.css' );
		wp_enqueue_style( 'newsmark-style', get_stylesheet_directory_uri() . '/style.css', array( 'newsup-style-parent' ), '1.0' );
		wp_enqueue_style('bootstrap', get_template_directory_uri() . '/css/bootstrap.css');
		wp_dequeue_style( 'newsup-default',get_template_directory_uri() .'/css/colors/default.css');
		wp_enqueue_style( 'newsmark-default-css', get_stylesheet_directory_uri()."/css/colors/default.css" );
		if(is_rtl()){
		wp_enqueue_style( 'newsup_style_rtl', trailingslashit( get_template_directory_uri() ) . 'style-rtl.css' );
	    }
		
	}

endif;
add_action( 'wp_enqueue_scripts', 'newsmark_enqueue_styles', 9999 );

function newsmark_theme_setup() {

//Load text domain for translation-ready
load_theme_textdomain('newsmark', get_stylesheet_directory() . '/languages');

require( get_stylesheet_directory() . '/hooks/hooks.php' );
require( get_stylesheet_directory() . '/customizer-default.php' );
require( get_stylesheet_directory() . '/frontpage-options.php' );
add_theme_support( 'title-tag' );
add_theme_support( 'automatic-feed-links' );


// custom header Support
			$args = array(
			'default-image'		=>  get_stylesheet_directory_uri() .'/images/head-back.jpg',
			'width'			=> '1600',
			'height'		=> '600',
			'flex-height'		=> false,
			'flex-width'		=> false,
			'header-text'		=> true,
			'default-text-color'	=> '#143745'
		);
		add_theme_support( 'custom-header', $args );
} 
add_action( 'after_setup_theme', 'newsmark_theme_setup' );

add_action( 'customize_register', 'newsmark_customize_remove_register', 1000 );
function newsmark_customize_remove_register($wp_customize) {

  $wp_customize->remove_control('tabbed_section_title');

  $wp_customize->remove_control('latest_tab_title');

  $wp_customize->remove_control('popular_tab_title');

  $wp_customize->remove_control('trending_tab_title');

  $wp_customize->remove_control('select_trending_tab_news_category');
}


function newsmark_widgets_init() {
	
	$newsup_footer_column_layout = esc_attr(get_theme_mod('newsup_footer_column_layout',3));
	
	$newsup_footer_column_layout = 12 / $newsup_footer_column_layout;
	
	register_sidebar( array(
		'name'          => esc_html__( 'Sidebar Widget Area', 'newsmark' ),
		'id'            => 'sidebar-1',
		'description'   => '',
		'before_widget' => '<div id="%1$s" class="mg-widget %2$s">',
		'after_widget'  => '</div>',
		'before_title'  => '<div class="mg-wid-title"><h6 class="wtitle">',
		'after_title'   => '</h6></div>',
	) );


	register_sidebar( array(
		'name'          => esc_html__( 'Footer Widget Area', 'newsmark' ),
		'id'            => 'footer_widget_area',
		'description'   => '',
		'before_widget' => '<div id="%1$s" class="col-md-'.$newsup_footer_column_layout.' rotateInDownLeft animated mg-widget %2$s">',
		'after_widget'  => '</div>',
		'before_title'  => '<h6>',
		'after_title'   => '</h6>',
	) );

}
add_action( 'widgets_init', 'newsmark_widgets_init' );