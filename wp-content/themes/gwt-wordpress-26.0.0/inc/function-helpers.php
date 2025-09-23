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

// -----------------------------------------------------------------------------
// Shortcode: [gwt_latest_posts]
// Uses the core latest-posts block renderer under the hood for consistent markup.
// Falls back to the theme template part if the core function is unavailable.
//  Parameters:
//  posts: number of posts (default 4)
//  order: asc|desc (default desc)
//  orderby: date|title|modified|rand (default date)
//  excerpt_length: words (default 26)
//  show_image: 1|0 (default 1)
//  image_size: thumbnail|medium|large|full (default large)
//  link_image: 1|0 (default 1)
//  show_author: 1|0 (default 0)
//  show_date: 1|0 (default 0)
//  content: none|excerpt|full (default excerpt)
//  post_layout: list|grid (default list)
//  columns: number of columns for grid (default 2)
//  category_ids: comma-separated category IDs
//  author: author ID
// -----------------------------------------------------------------------------
if ( ! function_exists( 'gwt_latest_posts_shortcode' ) ) {
    function gwt_latest_posts_shortcode( $atts = array(), $content = '', $tag = '' ) {
        $atts = shortcode_atts( array(
            'posts'           => 4,       // number of posts
            'order'           => 'desc',  // asc|desc
            'orderby'         => 'date',  // date|title|modified|rand
            'excerpt_length'  => 26,      // words
            'show_image'      => '1',     // 1|0
            'image_size'      => 'large', // thumbnail|medium|large|full
            'link_image'      => '1',     // 1|0
            'show_author'     => '0',     // 1|0
            'show_date'       => '0',     // 1|0
            'content'         => 'excerpt', // none|excerpt|full
            'post_layout'     => 'list',  // list|grid
            'columns'         => 2,       // used only in grid
            'category_ids'    => '',      // comma-separated category IDs
            'author'          => '',      // author ID
        ), $atts, 'gwt_latest_posts' );

        // Normalize attributes for core renderer
        $attributes = array(
            'postsToShow'               => max( 1, intval( $atts['posts'] ) ),
            'order'                     => strtolower( $atts['order'] ) === 'asc' ? 'asc' : 'desc',
            'orderBy'                   => in_array( strtolower( $atts['orderby'] ), array( 'date','title','modified','rand' ), true ) ? strtolower( $atts['orderby'] ) : 'date',
            'excerptLength'             => max( 0, intval( $atts['excerpt_length'] ) ),
            'displayFeaturedImage'      => $atts['show_image'] === '1',
            'featuredImageSizeSlug'     => sanitize_key( $atts['image_size'] ),
            'addLinkToFeaturedImage'    => $atts['link_image'] === '1',
            'displayAuthor'             => $atts['show_author'] === '1',
            'displayPostDate'           => $atts['show_date'] === '1',
            'displayPostContent'        => $atts['content'] !== 'none',
            'displayPostContentRadio'   => $atts['content'] === 'full' ? 'full_post' : 'excerpt',
            'postLayout'                => $atts['post_layout'] === 'grid' ? 'grid' : 'list',
            'columns'                   => max( 1, intval( $atts['columns'] ) ),
        );

        // Categories mapping: comma-separated IDs to array of [ [ 'id' => int ], ... ]
        if ( ! empty( $atts['category_ids'] ) ) {
            $ids = array_filter( array_map( 'absint', explode( ',', $atts['category_ids'] ) ) );
            if ( ! empty( $ids ) ) {
                $attributes['categories'] = array_map( function( $id ){ return array( 'id' => $id ); }, $ids );
            }
        }

        // Author filter
        if ( $atts['author'] !== '' ) {
            $attributes['selectedAuthor'] = intval( $atts['author'] );
        }

        // Render using core block pipeline with full block context to avoid block-supports issues
        if ( function_exists( 'render_block' ) ) {
            return render_block( array(
                'blockName'    => 'core/latest-posts',
                'attrs'        => $attributes,
                'innerBlocks'  => array(),
                'innerHTML'    => '',
                'innerContent' => array(),
            ) );
        } elseif ( function_exists( 'do_blocks' ) ) {
            $comment = '<!-- wp:latest-posts ' . wp_json_encode( $attributes ) . ' /-->';
            return do_blocks( $comment );
        }

        // Fallback: use theme template part
        ob_start();
        $latest_posts_args = array(
            'posts_per_page' => $attributes['postsToShow'],
            'post_type'      => 'post',
            'category_name'  => '',
            'card_reveal'    => true,
            'container_class'=> 'pb-5',
        );
        $tpl = locate_template( 'inc/partials/latest-posts.php', false, false );
        if ( $tpl ) {
            include $tpl;
        }
        return ob_get_clean();
    }

    // Ensure our shortcode registration takes precedence.
    function gwt_register_latest_posts_shortcode() {
        remove_shortcode( 'gwt_latest_posts' );
        add_shortcode( 'gwt_latest_posts', 'gwt_latest_posts_shortcode' );
    }
    add_action( 'init', 'gwt_register_latest_posts_shortcode' );
}

if ( ! function_exists( 'cbc_force_right_sidebar' ) ) {
    /**
     * Whether to force the right sidebar to render and be considered active
     * even when the widget area is empty. Filterable.
     */
    function cbc_force_right_sidebar() {
        return apply_filters( 'cbc_force_right_sidebar', true );
    }
}
