<?php
if ( is_home() || is_front_page() ):
    echo function_exists( 'cbc_slider_render_default_html' ) ? cbc_slider_render_default_html() : '';
endif;

$tempHeader = '';
if ( is_front_page() ) :
    $tempHeader = 'Announcement';
    echo do_shortcode( '[gwt_announcements header="'.$tempHeader.'" limit="5" layout="ticker" show_image="1" link_title="1" target="_blank"]' );
endif;

if ( ! ( is_home() || is_front_page() ) ):
    include_once( 'breadcrumbs.php' );
endif; ?>
