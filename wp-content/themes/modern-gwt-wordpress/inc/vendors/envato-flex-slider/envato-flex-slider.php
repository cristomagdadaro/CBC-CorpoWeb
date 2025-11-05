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
	// Build a proper query and render slides in a single pass.
	$query = new WP_Query(array(
		'post_type'      => 'slider-image',
		'posts_per_page' => -1,
		'no_found_rows'  => true,
	));

	if (!$query->have_posts()) {
		wp_reset_postdata();
		return '';
	}

	$slides = array();
	$i = 0;
	while ($query->have_posts()) : $query->the_post();
		$img_url = get_the_post_thumbnail_url(null, 'full');
		if (!$img_url) { continue; }

		$thumb_id  = get_post_thumbnail_id();
		$img_title = get_post_meta($thumb_id, '_wp_attachment_image_alt', true);
		if (!$img_title) { $img_title = get_the_title(); }

		$slide_link = function_exists('slider_link_get_meta_box_data') ? slider_link_get_meta_box_data(get_the_ID()) : '';
		$caption    = get_the_title();

		$active_class = ($i === 0) ? ' is-active' : '';
		$img_tag = '<img src="' . esc_url($img_url) . '" class="orbit-image object-cover object-center w-full h-full aspect-[3/1] max-h-[50%]" alt="' . esc_attr($img_title) . '">';

		$slide_html  = '<li class="orbit-slide' . $active_class . ' relative">';
		$slide_html .= '<figure class="orbit-figure">';
		$slide_html .= !empty($slide_link) ? ('<a href="' . esc_url($slide_link) . '">' . $img_tag . '</a>') : $img_tag;
		$slide_html .= '<div class="absolute inset-0 flex justify-center">';
		$slide_html .= '<div class="hidden absolute inset-y-0 left-0 w-1/2 bg-gradient-to-r from-[#1f5d2b] via-transparent to-transparent opacity-25"></div>';
		$slide_html .= '<div class="hidden absolute inset-y-0 right-0 w-1/2 bg-gradient-to-l from-[#a2b917] via-transparent to-transparent opacity-25"></div>';
		$slide_html .= '<figcaption class="orbit-caption absolute bottom-0 w-full text-center text-white bg-black bg-opacity-0 py-2 sm:text-lg md:text-xl lg:text-2xl text-normal leading-tight">' . esc_html($caption) . '</figcaption>';
		$slide_html .= '</div>';
		$slide_html .= '<div class="hidden orbit-slide-number absolute top-0 left-0 z-10 text-white p-2"><span>' . ($i + 1) . '</span> of <span>%TOTAL%</span></div>';
		$slide_html .= '</figure>';
		$slide_html .= '</li>';

		$slides[] = $slide_html;
		$i++;
	endwhile;
	wp_reset_postdata();

	$rendered = count($slides);
	if ($rendered === 0) {
		return '';
	}

	// Replace %TOTAL% placeholders with actual total rendered slides
	$slides = array_map(function($html) use ($rendered) {
		return str_replace('%TOTAL%', (string) $rendered, $html);
	}, $slides);

	// Give the orbit a unique id so controls/bullets can reference it if needed
	$orbit_id = 'orbit-' . uniqid();

	$slider  = '<div id="' . esc_attr($orbit_id) . '" class="orbit" role="region" aria-label="Banner Slider" data-orbit data-options="animInFromLeft:fade-in; animInFromRight:fade-in; animOutToLeft:fade-out; animOutToRight:fade-out;">';
	$slider .= '<div class="orbit-wrapper">';

	// Use Foundation 6 recommended structure: controls in a dedicated container before the list
	if ($rendered > 1) {
		$slider .= '<div class="orbit-controls">';
		$slider .= '<button class="orbit-previous" type="button" aria-controls="' . esc_attr($orbit_id) . '" aria-label="Previous slide"><span class="show-for-sr">Previous Slide</span>&#9664;&#xFE0E;</button>';
		$slider .= '<button class="orbit-next" type="button" aria-controls="' . esc_attr($orbit_id) . '" aria-label="Next slide"><span class="show-for-sr">Next Slide</span>&#9654;&#xFE0E;</button>';
		$slider .= '</div>';
	}

	$slider .= '<ul class="orbit-container">';
	$slider .= implode('', $slides);
	$slider .= '</ul>';
	$slider .= '</div>';

	if ($rendered > 1) {
		$slider .= '<nav class="orbit-bullets">';
		for ($x = 0; $x < $rendered; $x++) {
			$class = ($x === 0) ? 'is-active' : '';
			$slider .= '<button class="' . $class . '" data-slide="' . $x . '"><span class="show-for-sr">Slide ' . ($x + 1) . '</span>' . ($x === 0 ? '<span class="show-for-sr" data-slide-active-label>Current Slide</span>' : '') . '</button>';
		}
		$slider .= '</nav>';
	}

	$slider .= '</div>';

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