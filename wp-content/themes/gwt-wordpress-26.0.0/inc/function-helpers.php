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

/**
 * Shortcode: [gwt_calendar]
 * Attributes:
 * - mode: auto|grid|list (default auto; auto uses Theme Option govph_calendar_display)
 * - month: 1-12 (optional; default current month)
 * - year: YYYY (optional; default current year)
 * - header: 1|0 show section header (default 1)
 * - max: number of items for list mode (default 30)
 */
if ( ! function_exists( 'gwt_calendar_shortcode' ) ) {
    function gwt_calendar_shortcode( $atts = array(), $content = '', $tag = '' ) {
        $atts = shortcode_atts( array(
            'mode'   => 'auto',
            'month'  => '',
            'year'   => '',
            'header' => '1',
            'max'    => 30,
        ), $atts, 'gwt_calendar' );

        $opt = get_option('govph_options');

        // Parse events: YYYY-MM-DD|Title|URL|Type|Location
        $events = array();
        if ( ! empty( $opt['govph_events'] ) ) {
            $lines = preg_split( "/(\r\n|\n|\r)/", (string)$opt['govph_events'] );
            foreach ( $lines as $line ) {
                $line = trim( (string)$line );
                if ( $line === '' ) continue;
                $parts = array_map( 'trim', explode( '|', $line ) );
                $date  = $parts[0] ?? '';
                $title = $parts[1] ?? '';
                if ( $date === '' || $title === '' ) continue;
                $events[] = array(
                    'date' => $date,
                    'title'=> $title,
                    'url'  => $parts[2] ?? '',
                    'type' => $parts[3] ?? '',
                    'loc'  => $parts[4] ?? '',
                );
            }
        }

        // Parse holidays: YYYY-MM-DD|Name|Scope
        $holidays = array();
        if ( ! empty( $opt['govph_holidays'] ) ) {
            $lines = preg_split( "/(\r\n|\n|\r)/", (string)$opt['govph_holidays'] );
            foreach ( $lines as $line ) {
                $line = trim( (string)$line );
                if ( $line === '' ) continue;
                $parts = array_map( 'trim', explode( '|', $line ) );
                $date = $parts[0] ?? '';
                $name = $parts[1] ?? '';
                if ( $date === '' || $name === '' ) continue;
                $holidays[] = array(
                    'date'  => $date,
                    'name'  => $name,
                    'scope' => $parts[2] ?? '',
                );
            }
        }

        // Determine month/year
        $now = current_time( 'timestamp' );
        $y = intval( $atts['year'] ) ?: intval( date( 'Y', $now ) );
        $m = intval( $atts['month'] );
        if ( $m < 1 || $m > 12 ) { $m = intval( date( 'n', $now ) ); }

        $current = DateTime::createFromFormat( 'Y-m-d', sprintf('%04d-%02d-01', $y, $m) );
        if ( ! $current ) { $current = new DateTime( 'first day of this month' ); }
        $startDow     = (int)$current->format('N');
        $daysInMonth  = (int)$current->format('t');
        $monthLabel   = $current->format('F Y');
        $today        = new DateTime( date('Y-m-d', $now) );

        // Index by date
        $byDate = array();
        foreach ( $events as $e ) { $byDate[ $e['date'] ][] = array( 'type' => 'event', 'title' => $e['title'], 'url' => $e['url'] ); }
        foreach ( $holidays as $h ) { $byDate[ $h['date'] ][] = array( 'type' => 'holiday', 'title' => $h['name'], 'url' => '' ); }

        // Resolve mode
        $mode = strtolower( $atts['mode'] );
        if ( $mode !== 'grid' && $mode !== 'list' ) {
            $mode = ( isset( $opt['govph_calendar_display'] ) && $opt['govph_calendar_display'] === 'list' ) ? 'list' : 'grid';
        }
        $maxItems = max( 1, intval( $atts['max'] ) );

        ob_start();

        if ( $atts['header'] === '1' ) {
            if ( function_exists( 'govph_section_header' ) ) {
                echo govph_section_header( 'Calendar ' . esc_html( $monthLabel ), array( 'id' => 'gwt_calendar_header', 'text_alignment' => 'left' ) );
            } else {
                echo '<h2 class="gwt-calendar-header">' . esc_html( 'Calendar ' . $monthLabel ) . '</h2>';
            }
        }

        if ( $mode === 'list' ) {
            echo '<div class="calendar-list" style="display:flex;flex-direction:column;gap:12px;padding:8px;">';
            $items = array();
            foreach ( $byDate as $date => $entries ) {
                $d = DateTime::createFromFormat('Y-m-d', $date);
                if ( ! $d || $d < $today ) continue;
                foreach ( $entries as $e ) {
                    $items[] = array( 'date' => $date, 'datetime' => $d, 'type' => $e['type'], 'title' => $e['title'], 'url' => $e['url'] );
                }
            }
            usort( $items, function($a, $b){ return $a['datetime'] <=> $b['datetime']; } );
            $count = 0;
            if ( empty( $items ) ) {
                echo '<div style="color:#666;">' . esc_html__( 'No upcoming events or holidays.', 'gwt_wp' ) . '</div>';
            } else {
                foreach ( $items as $it ) {
                    if ( $count++ >= $maxItems ) break;
                    $dlabel = $it['datetime']->format('M d, Y');
                    echo '<div class="card" style="border:1px solid #e5e5e5;padding:12px;border-radius:6px;background:#fff;display:flex;flex-direction:row;gap:12px;align-items:center;">';
                    echo '<div style="min-width:120px;font-weight:bold;color:#006837;">' . esc_html( $dlabel ) . '</div>';
                    echo '<div style="flex:1;">';
                    $label = ( $it['type'] === 'holiday' ? 'Holiday: ' : '' ) . $it['title'];
                    if ( ! empty( $it['url'] ) ) {
                        echo '<a href="' . esc_url( $it['url'] ) . '" target="_blank" rel="noopener" style="font-weight:600;color:#00391a;">' . esc_html( $label ) . '</a>';
                    } else {
                        echo '<div style="font-weight:600;color:#00391a;">' . esc_html( $label ) . '</div>';
                    }
                    echo '</div>';
                    echo '</div>';
                }
            }
            echo '</div>';
        } else {
            echo '<div class="calendar-grid" style="display:grid;grid-template-columns:repeat(7,1fr);gap:6px;padding:8px;">';
            $wd = array('Mon','Tue','Wed','Thu','Fri','Sat','Sun');
            foreach ( $wd as $w ) { echo '<div style="font-weight:bold;text-align:center;background:#f0f4f0;padding:6px;">' . esc_html( $w ) . '</div>'; }
            for ( $i = 1; $i < $startDow; $i++ ) { echo '<div style="padding:8px;border:1px solid #e5e5e5;background:#fafafa;"></div>'; }
            $ym = $current->format('Y-m-');
            for ( $day = 1; $day <= $daysInMonth; $day++ ) {
                $dateStr = $ym . str_pad( (string)$day, 2, '0', STR_PAD_LEFT );
                echo '<div style="padding:8px;border:1px solid #e5e5e5;background:#fff;min-height:80px;">';
                echo '<div style="font-weight:bold;color:#006837;">' . intval($day) . '</div>';
                if ( ! empty( $byDate[$dateStr] ) ) {
                    foreach ( $byDate[$dateStr] as $item ) {
                        $label = ( $item['type'] === 'holiday' ? 'Holiday: ' : '' ) . $item['title'];
                        if ( ! empty( $item['url'] ) ) {
                            echo '<div style="font-size:12px;line-height:1.2;margin-top:4px;"><a href="' . esc_url( $item['url'] ) . '" target="_blank" rel="noopener">' . esc_html( $label ) . '</a></div>';
                        } else {
                            echo '<div style="font-size:12px;line-height:1.2;margin-top:4px;">' . esc_html( $label ) . '</div>';
                        }
                    }
                }
                echo '</div>';
            }
            echo '</div>';
        }

        return ob_get_clean();
    }

    function gwt_register_calendar_shortcode() {
        add_shortcode( 'gwt_calendar', 'gwt_calendar_shortcode' );
    }
    add_action( 'init', 'gwt_register_calendar_shortcode' );
}
