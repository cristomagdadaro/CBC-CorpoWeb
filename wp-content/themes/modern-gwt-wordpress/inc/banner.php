<?php
if ( is_home() || is_front_page() ):
    echo do_shortcode('[cbc_slider id="3460"]');
endif;

$tempHeader = '';
if ( is_front_page() ) :
    $tempHeader = 'Announcement';
    echo do_shortcode( '[gwt_announcements header="'.$tempHeader.'" limit="5" layout="ticker" show_image="1" link_title="1" target="_blank"]' );
endif;

if ( ! ( is_home() || is_front_page() ) ):
    include_once( 'breadcrumbs.php' );
endif; ?>
