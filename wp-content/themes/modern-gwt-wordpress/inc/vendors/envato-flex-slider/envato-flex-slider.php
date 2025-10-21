<?php
/*
Plugin Name: Envato FlexSlider
Plugin URI:
Description: A simple plugin that integrates FlexSlider (http://flex.madebymufffin.com/) with WordPress using custom post types!
Author: Joe Casabona
Version: 0.5
Author URI: http://www.casabona.org
*/

/*Some Set-up*/
define('EFS_PATH', get_template_directory_uri() . '/inc/vendors/' . basename( dirname(__FILE__) ) . '/' );
define('EFS_NAME', "Envato FlexSlider");
define ("EFS_VERSION", "0.5");

/*Files to Include*/
require_once('slider-img-type.php');

function efs_get_slider(){
	$efs_query = "post_type=slider-image";
	query_posts($efs_query);

	global $post_id;
	$count = 0;
	$slider = '<div class="orbit" role="region" aria-label="Banner Slider" data-orbit data-options="animInFromLeft:fade-in; animInFromRight:fade-in; animOutToLeft:fade-out; animOutToRight:fade-out;">
                <ul class="orbit-container">';
	if (have_posts()) :

		$x = 1;
		while (have_posts()) : the_post();
			$count++;
		endwhile;

		while (have_posts()) : the_post();
			$img = get_the_post_thumbnail($post_id, 'large', array( 'class' => 'orbit-image object-cover object-center w-full h-full aspect-[3/1]' ));

			$slide_link = slider_link_get_meta_box_data(get_the_ID());
			$caption = get_the_title();

			if ($x > $count) {
				$x = 1;
			}
			$active_class = ($x === 1) ? ' is-active' : '';
			$slider .= '<li class="orbit-slide' . $active_class . ' relative">' . $img . '
		    <a href="' . $slide_link . '">
		    <div class="absolute inset-0 flex justify-center">
		        <div class="hidden absolute inset-y-0 left-0 w-1/2 bg-gradient-to-r from-[#1f5d2b] via-transparent to-transparent opacity-25"></div>
		        <div class="hidden absolute inset-y-0 right-0 w-1/2 bg-gradient-to-l from-[#a2b917] via-transparent to-transparent opacity-25"></div>
		        <figcaption class="orbit-caption absolute bottom-0 w-full text-center text-white bg-black bg-opacity-0 py-2 sm:text-lg md:text-xl lg:text-2xl text-normal leading-tight">' . $caption . '</figcaption>
		    </div>
		    </a>
		    <div class="hidden orbit-slide-number absolute top-0 left-0 z-10 text-white p-2">
		        <span>' . $x . '</span> of <span>' . $count . '</span>
		    </div>
			</li>';



			$x++;
		endwhile;

		if($count > 1) {
			$slider .= '<button class="orbit-previous"><span class="show-for-sr">Previous Slide</span>&#9664;&#xFE0E;</button>
						<button class="orbit-next"><span class="show-for-sr">Next Slide</span>&#9654;&#xFE0E;</button>';
		}
	endif;
	wp_reset_query();

	if($count > 1) {
		$slider .= '</ul>
        <nav class="orbit-bullets">';

		for ($x=0; $x < $count ; $x++) {
			$class = ($x == 0) ? 'is-active' : '' ;
			$slider .= '<button class="'.$class.'" data-slide="'.$x.'"><span class="show-for-sr">Current Slide</span></button>';
		}

		$slider .= '</div></nav>';
	} else {
		$slider .= '</ul></div>';
	}
	return $slider;
}


/**add the shortcode for the slider- for use in editor**/
function efs_insert_slider($atts, $content=null) {
	$slider= efs_get_slider();
	return $slider;
}
add_shortcode('ef_slider', 'efs_insert_slider');

/**add template tag- for use in themes**/
function efs_slider() {
	print efs_get_slider();
}

?>