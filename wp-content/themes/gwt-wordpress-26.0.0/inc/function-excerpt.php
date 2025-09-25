<?php
/**
 * govph exerpt
 *
 * @package govph
 */
// Replaces the excerpt "more" text by a link
function new_excerpt_more($more) {
  global $post;
  /*return '<a class="moretag font-bold" href="'. get_permalink($post->ID) . '"> continue reading : '. get_the_title($post->ID) .' </a>';*/
	return '... <a class="moretag" href="'. get_permalink($post->ID) . '"> Read More </a>';
}
add_filter('excerpt_more', 'new_excerpt_more');