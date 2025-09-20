<?php
/**
 * Theme helper functions
 */

if ( ! function_exists( 'gwt_render_share_embed' ) ) {
    function gwt_render_share_embed( $post_id = null ) {
        if ( $post_id ) {
            $post = get_post( $post_id );
            if ( ! $post ) return;
            setup_postdata( $GLOBALS['post'] =& $post );
        } else {
            global $post;
            if ( ! isset( $post ) ) return;
        }
        $partial = locate_template( 'inc/partials/share-embed.php' );
        if ( $partial ) {
            include $partial;
        }
        if ( $post_id ) {
            wp_reset_postdata();
        }
    }
}

if ( ! function_exists( 'gwt_share_embed_shortcode' ) ) {
    function gwt_share_embed_shortcode( $atts ) {
        $atts = shortcode_atts( array( 'id' => null ), $atts, 'gwt_share_embed' );
        ob_start();
        gwt_render_share_embed( $atts['id'] );
        return ob_get_clean();
    }
    add_shortcode( 'gwt_share_embed', 'gwt_share_embed_shortcode' );
}
