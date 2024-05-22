<?php
/**
 * Default theme options.
 *
 * @package Newsmark
 */

if (!function_exists('newsmark_get_default_theme_options')):

/**
 * Get default theme options
 *
 * @since 1.0.0
 *
 * @return array Default theme options.
 */
function newsmark_get_default_theme_options() {

    $defaults = array();

    $defaults['select_recent_news_category'] = 0;
    $defaults['select_trending_news_category'] = 0;   
    $defaults['flash_news_title'] = __('Breaking', 'newsmark');

	return $defaults;

}
endif;