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
if ( ! function_exists( 'cbc_get_device_type' ) ) {
    /**
     * Basic server-side device type detection using User-Agent.
     * Returns: phone | tablet | desktop
     * Can be filtered via 'cbc_device_type'.
     */
    function cbc_get_device_type(){
        $ua = strtolower( $_SERVER['HTTP_USER_AGENT'] ?? '' );
        $device = 'desktop';
        if ( $ua ) {
            // Tablet first (so iPad & known tablets are not classified as phone)
            if ( preg_match( '/(ipad|tablet|kindle|silk|playbook|nexus 7|nexus 9|nexus 10|xoom|sm\-t|gt\-p|lenovo tab|mediapad|galaxy tab)/i', $ua ) ) {
                $device = 'tablet';
            } elseif ( preg_match( '/(mobi|iphone|ipod|android.*mobile|blackberry|opera mini|windows phone)/i', $ua ) ) {
                $device = 'phone';
            }
        }
        return apply_filters( 'cbc_device_type', $device, $ua );
    }
}

if ( ! function_exists( 'gwt_latest_posts_shortcode' ) ) {
    function gwt_latest_posts_shortcode( $atts = array(), $content = '', $tag = '' ) {
        // Support legacy attribute name 'excerptLength' if used incorrectly.
        if ( isset( $atts['excerptLength'] ) && ! isset( $atts['excerpt_length'] ) ) {
            $atts['excerpt_length'] = $atts['excerptLength'];
        }
        $atts = shortcode_atts( array(
            'posts'           => 4,       // number of posts
            'order'           => 'desc',  // asc|desc
            'orderby'         => 'date',  // date|title|modified|rand
            'excerpt_length'  => 26,      // words OR 'auto'
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

        // Auto excerpt length logic (phone/tablet/desktop) if user passes 'auto' or 0.
        if ( $atts['excerpt_length'] === 'auto' || $atts['excerpt_length'] === 0 || $atts['excerpt_length'] === '0' ) {
            $device = cbc_get_device_type();
            $length_map = apply_filters( 'gwt_latest_posts_device_excerpt_lengths', array(
                'phone'   => 15,
                'tablet'  => 22,
                'desktop' => 30,
            ), $device );
            if ( is_array( $length_map ) && isset( $length_map[ $device ] ) ) {
                $atts['excerpt_length'] = (int) $length_map[ $device ];
            } else {
                $atts['excerpt_length'] = 26; // fallback
            }
        }

        // Ensure numeric after potential mapping
        $atts['excerpt_length'] = is_numeric( $atts['excerpt_length'] ) ? (int) $atts['excerpt_length'] : 26;

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
            'mode'        => 'auto',      // auto|grid|list
            'month'       => '',          // 1..12
            'year'        => '',          // YYYY
            'header'      => '1',         // 1|0
            'max'         => 30,          // list cap
            'show'        => 'both',      // both|events|holidays
            'range'       => 'month',     // month|all
            'include_past'=> '0',         // 0|1 (applies when range=all in list mode)
        ), $atts, 'gwt_calendar' );

        // Read from plugin (falls back to theme inside plugin)
        $cbc = apply_filters('cbc_calendar_options', []);

        // Parse events & holidays
        $events = array();
        if ( ! empty( $cbc['events'] ) ) {
            $lines = preg_split( "/(\r\n|\n|\r)/", (string)$cbc['events'] );
            foreach ( $lines as $line ) {
                $line = trim( (string)$line ); if ( $line === '' ) continue;
                $parts = array_map( 'trim', explode( '|', $line ) );
                $date0  = $parts[0] ?? '';
                if ( $date0 === '' ) continue;
                // Optional date_to as second token if it matches YYYY-MM-DD
                $maybe_dt = $parts[1] ?? '';
                $has_dt = ( $maybe_dt !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $maybe_dt) );
                $date1 = $has_dt ? $maybe_dt : '';
                $title = $parts[ $has_dt ? 2 : 1 ] ?? '';
                if ( $title === '' ) continue;
                $url   = $parts[ $has_dt ? 3 : 2 ] ?? '';
                $type  = $parts[ $has_dt ? 4 : 3 ] ?? '';
                $loc   = $parts[ $has_dt ? 5 : 4 ] ?? '';
                $events[] = array( 'date_from'=>$date0, 'date_to'=>$date1, 'title'=>$title, 'url'=>$url, 'type'=>$type, 'loc'=>$loc, 'raw_parts'=>$parts );
            }
        }
        $holidays = array();
        if ( ! empty( $cbc['holidays'] ) ) {
            $lines = preg_split( "/(\r\n|\n|\r)/", (string)$cbc['holidays'] );
            foreach ( $lines as $line ) {
                $line = trim( (string)$line ); if ( $line === '' ) continue;
                $parts = array_map( 'trim', explode( '|', $line ) );
                $date = $parts[0] ?? '';
                $name = $parts[1] ?? '';
                if ( $date === '' || $name === '' ) continue;
                // Third token may be URL or a flag like 'announce'. Only treat as URL if it looks like one.
                $p2 = $parts[2] ?? '';
                $url = ( $p2 && preg_match('#^(https?://|/)#i', $p2) ) ? $p2 : '';
                $holidays[] = array( 'date'=>$date, 'name'=>$name, 'url'=>$url, 'raw_parts'=>$parts );
            }
        }

        // Determine display mode: plugin default or shortcode attr only; ignore query overrides
        $mode = strtolower($atts['mode']);
        if ($mode !== 'grid' && $mode !== 'list') {
            $stored = isset( $cbc['calendar_display'] ) ? (string)$cbc['calendar_display'] : 'grid';
            $mode   = ( $stored === 'list' ) ? 'list' : 'grid';
        }

        // Determine filter: show and range strictly from shortcode attrs
        $show  = in_array($atts['show'], array('both','events','holidays'), true) ? $atts['show'] : 'both';
        $range = in_array($atts['range'], array('month','all'), true) ? $atts['range'] : 'month';
        $include_past = $atts['include_past'] === '1';

        // Month context
        $now = current_time( 'timestamp' );
        $y = intval( $atts['year'] ) ?: intval( date( 'Y', $now ) );
        $m = intval( $atts['month'] ); if ( $m < 1 || $m > 12 ) { $m = intval( date( 'n', $now ) ); }
        $current = DateTime::createFromFormat( 'Y-m-d', sprintf('%04d-%02d-01', $y, $m) );
        if ( ! $current ) { $current = new DateTime( 'first day of this month' ); }
        $startDow     = (int)$current->format('N');
        $daysInMonth  = (int)$current->format('t');
        $monthLabel   = $current->format('F Y');
        $today        = new DateTime( date('Y-m-d', $now ) );

        ob_start();
        if ( $atts['header'] === '1' ) {
            if ( function_exists( 'govph_section_header' ) ) {
                echo govph_section_header( 'Calendar ' . esc_html( $monthLabel ), array( 'id' => 'gwt_calendar_header', 'text_alignment' => 'left' ) );
            } else {
                echo '<h2 class="gwt-calendar-header">' . esc_html( 'Calendar ' . $monthLabel ) . '</h2>';
            }
        }

        // Build items list for list mode
        if ( $mode === 'list' ) {
            $items = array();
            $monthStart = DateTime::createFromFormat('Y-m-d', sprintf('%04d-%02d-01', intval($current->format('Y')), intval($current->format('m'))));
            $monthEnd   = DateTime::createFromFormat('Y-m-d', sprintf('%04d-%02d-%02d', intval($current->format('Y')), intval($current->format('m')), $daysInMonth));

            if ($show === 'both' || $show === 'events') {
                foreach ( $events as $e ) {
                    $df = DateTime::createFromFormat('Y-m-d', $e['date_from']); if ( ! $df ) continue;
                    $dt = ($e['date_to'] && preg_match('/^\d{4}-\d{2}-\d{2}$/', $e['date_to'])) ? DateTime::createFromFormat('Y-m-d', $e['date_to']) : null;
                    if ($dt && $dt <= $df) { $dt = null; } // treat equal/earlier as single-day
                    $inMonth = ($df <= $monthEnd) && ( ($dt?$dt:$df) >= $monthStart );
                    if (! $inMonth) continue;
                    if ($range === 'month' && ($df > $monthEnd || ($dt?$dt:$df) < $monthStart)) continue;
                    if ($range === 'all' && !$include_past && ($dt?$dt:$df) < $today) continue;
                    $items[] = array( 'date'=>$df, 'date_to'=>$dt, 'type'=>'event', 'title'=>$e['title'], 'url'=>$e['url'] );
                }
            }
            if ($show === 'both' || $show === 'holidays') {
                foreach ( $holidays as $h ) {
                    $d = DateTime::createFromFormat('Y-m-d', $h['date']); if ( ! $d ) continue;
                    if ($range === 'month' && ($d < $monthStart || $d > $monthEnd)) continue;
                    if ($range === 'all' && !$include_past && $d < $today) continue;
                    $items[] = array( 'date'=>$d, 'date_to'=>null, 'type'=>'holiday', 'title'=>$h['name'], 'url'=> ($h['url'] ?? '') );
                }
            }

            usort( $items, function($a,$b){ return $a['date'] <=> $b['date']; } );
            if ( $atts['max'] > 0 ) { $items = array_slice( $items, 0, intval($atts['max']) ); }

            echo '<div class="calendar-list" style="display:flex;flex-direction:column;gap:12px;padding:8px;">';
            if ( empty( $items ) ) {
                echo '<div style="color:#666;">' . esc_html__( 'No items found.', 'gwt_wp' ) . '</div>';
            } else {
                foreach ( $items as $it ) {
                    $labelDate = $it['date']->format('M d, Y');
                    if ($it['date_to'] instanceof DateTime) {
                        $sameMonth = ($it['date']->format('Y-m') === $it['date_to']->format('Y-m'));
                        $labelDate = $sameMonth
                            ? $it['date']->format('M d') . '–' . $it['date_to']->format('d, Y')
                            : $it['date']->format('M d, Y') . ' – ' . $it['date_to']->format('M d, Y');
                    }
                    echo '<div class="card" style="border:1px solid #e5e5e5;padding:12px;border-radius:6px;background:#fff;display:flex;flex-direction:row;gap:12px;align-items:center;">';
                    echo '<div style="min-width:160px;font-weight:bold;color:#006837;">' . esc_html( $labelDate ) . '</div>';
                    echo '<div style="flex:1;">';
                    $label = $it['title'];
                    if ( ! empty( $it['url'] ) ) {
                        echo '<a href="' . esc_url( $it['url'] ) . '" target="_blank" rel="noopener" style="color:#00391a;">' . esc_html( $label ) . '</a>';
                    } else {
                        echo '<div style="color:#00391a;">' . esc_html( $label ) . '</div>';
                    }
                    echo '</div>';
                    echo '</div>';
                }
            }
            echo '</div>';
        } else {
            // Grid mode: render date ranges within day cells using colspan
            // Build per-day map for single-day items AND track which days are covered by ranges
            $byDate = array();
            $rangesByWeek = array(); // Track range events per week

            if ($show === 'both' || $show === 'events') {
                foreach ( $events as $e ) {
                    $hasDt = ($e['date_to'] && preg_match('/^\d{4}-\d{2}-\d{2}$/', $e['date_to']));
                    $isRange = false;
                    if ($hasDt) {
                        $dfTmp = DateTime::createFromFormat('Y-m-d', $e['date_from']);
                        $dtTmp = DateTime::createFromFormat('Y-m-d', $e['date_to']);
                        $isRange = ($dfTmp && $dtTmp && $dtTmp > $dfTmp);
                    }
                    if (!$isRange) {
                        // Single day event
                        $byDate[$e['date_from']][] = array( 'type'=>'event', 'title'=>$e['title'], 'url'=>$e['url'] );
                    }
                }
            }
            if ($show === 'both' || $show === 'holidays') {
                foreach ( $holidays as $h ) {
                    $byDate[$h['date']][] = array( 'type'=>'holiday', 'title'=>$h['name'], 'url'=> ($h['url'] ?? '') );
                }
            }

            // Compute weeks covering the month
            $firstOfMonth = DateTime::createFromFormat('Y-m-d', $current->format('Y-m-01'));
            $firstDow = (int)$firstOfMonth->format('N'); // 1..7 (Mon..Sun)
            $weekStart = clone $firstOfMonth; $weekStart->modify('-' . ($firstDow - 1) . ' days');
            $weeks = array();
            $totalCells = ($firstDow - 1) + $daysInMonth; $weekCount = (int)ceil($totalCells / 7);
            for ($w=0; $w < $weekCount; $w++){
                $ws = clone $weekStart; $ws->modify('+' . ($w*7) . ' days');
                $we = clone $ws; $we->modify('+6 days');
                $weeks[] = array('start'=>$ws,'end'=>$we);
            }

            // Precompute range events per week with their date spans
            $rangesByWeek = array_fill(0, count($weeks), array());
            if ($show === 'both' || $show === 'events') {
                foreach ($events as $e){
                    $df = DateTime::createFromFormat('Y-m-d', $e['date_from']);
                    $dt = ($e['date_to'] && preg_match('/^\d{4}-\d{2}-\d{2}$/', $e['date_to'])) ? DateTime::createFromFormat('Y-m-d', $e['date_to']) : null;
                    if (!($df && $dt && $dt > $df)) continue; // require strict range

                    foreach ($weeks as $idx => $wk){
                        $segStart = max($df, $wk['start']);
                        $segEnd   = min($dt, $wk['end']);
                        if ($segStart > $segEnd) continue; // no overlap this week

                        $rangesByWeek[$idx][] = array(
                            'start' => $segStart,
                            'end'   => $segEnd,
                            'title' => $e['title'],
                            'url'   => $e['url'],
                        );
                    }
                }
            }

            // Render as HTML table with colspan in day cells
            echo '<table class="calendar-grid reveal-on-scroll-300 opacity-0" style="width:100%;border-collapse:separate;border-spacing:6px;margin-bottom: 0">';

            // Header row
            echo '<thead><tr>';
            foreach ( array('Mon','Tue','Wed','Thu','Fri','Sat','Sun') as $w ) {
                echo '<th style="font-weight:bold;text-align:center;background:#f0f4f0;padding:6px;">' . esc_html( $w ) . '</th>';
            }
            echo '</tr></thead>';

            // Inline styles
            echo '<style>.gwt-week-band{background:#e6f4ea;border: 1px solid #00a32a; margin-bottom: 3px; border-radius:6px;padding:4px 8px;font-size:12px;line-height:1.2;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}</style>';

            echo '<tbody id="calendar-tbody">';
            // Iterate weeks: one row per week with grouped overlapping events in single cells
            foreach ($weeks as $wi => $wk){
                echo '<tr>';

                // Group overlapping ranges into clusters
                $clusters = array(); // Each cluster contains overlapping ranges
                $processed = array();

                foreach ($rangesByWeek[$wi] as $idx => $range) {
                    if (isset($processed[$idx])) continue;

                    $cluster = array($range);
                    $processed[$idx] = true;
                    $clusterStartDay = (int)$range['start']->format('N') - 1;
                    $clusterEndDay = (int)$range['end']->format('N') - 1;

                    // Find all ranges that overlap with this cluster
                    $foundOverlap = true;
                    while ($foundOverlap) {
                        $foundOverlap = false;
                        foreach ($rangesByWeek[$wi] as $idx2 => $range2) {
                            if (isset($processed[$idx2])) continue;

                            $r2StartDay = (int)$range2['start']->format('N') - 1;
                            $r2EndDay = (int)$range2['end']->format('N') - 1;

                            // Check if range2 overlaps with current cluster bounds
                            if ($r2StartDay <= $clusterEndDay && $r2EndDay >= $clusterStartDay) {
                                $cluster[] = $range2;
                                $processed[$idx2] = true;
                                // Expand cluster bounds
                                $clusterStartDay = min($clusterStartDay, $r2StartDay);
                                $clusterEndDay = max($clusterEndDay, $r2EndDay);
                                $foundOverlap = true;
                            }
                        }
                    }

                    $clusters[] = array(
                        'ranges' => $cluster,
                        'startDay' => $clusterStartDay,
                        'endDay' => $clusterEndDay
                    );
                }

                // Sort clusters by start day
                usort($clusters, function($a, $b) { return $a['startDay'] <=> $b['startDay']; });

                // Track which days have been rendered
                $dayRendered = array_fill(0, 7, false);

                // Render clusters and regular cells
                $currentDay = 0;
                foreach ($clusters as $cluster) {
                    $startDay = $cluster['startDay'];
                    $endDay = $cluster['endDay'];
                    $colspan = $endDay - $startDay + 1;

                    // Render empty cells before this cluster
                    for ($d = $currentDay; $d < $startDay; $d++) {
                        if (!$dayRendered[$d]) {
                            $cur = clone $wk['start']; $cur->modify('+'.$d.' days');
                            $inMonth = ($cur->format('Y-m') === $current->format('Y-m'));
                            $dateStr = $cur->format('Y-m-d');

                            echo '<td style="padding:8px;border:1px solid #e5e5e5;background:'. ($inMonth?'#fff':'#fafafa') .';min-height:80px;vertical-align:top;width:14.28%;">';
                            echo '<div style="font-weight:bold;color:#006837;opacity:'. ($inMonth? '1':'0.5') .';">' . intval($cur->format('j')) . '</div>';
                            if ( $inMonth && ! empty( $byDate[$dateStr] ) ) {
                                foreach ( $byDate[$dateStr] as $item ) {
                                    $label = $item['title'];
                                    if ( ! empty( $item['url'] ) ) {
                                        echo '<div style="font-size:12px;line-height:1.2;margin-top:4px;"><a href="' . esc_url( $item['url'] ) . '" target="_blank" rel="noopener">' . esc_html( $label ) . '</a></div>';
                                    } else {
                                        echo '<div style="font-size:12px;line-height:1.2;margin-top:4px;">' . esc_html( $label ) . '</div>';
                                    }
                                }
                            }
                            echo '</td>';
                            $dayRendered[$d] = true;
                        }
                    }

                    // Render cluster cell with colspan containing nested table for stacked events
                    echo '<td colspan="' . $colspan . '" style="padding:0;min-height:80px;vertical-align:center;width:'. (14.28 * $colspan) .'%;">';

                    // Nested table with colspan columns to match the outer colspan
                    echo '<table style="width:100%;border-collapse:collapse;table-layout:fixed;border:none; margin:0; padding:0;">';
                    echo '<colgroup>';
                    for ($c = 0; $c < $colspan; $c++) {
                        echo '<col style="width:' . (100 / $colspan) . '%;" />';
                    }
                    echo '</colgroup>';
                    echo '<tbody style="border:none;">';

                    // Render each event as a row spanning the appropriate columns
                    foreach ($cluster['ranges'] as $range) {
                        $rangeStartDay = (int)$range['start']->format('N') - 1;
                        $rangeEndDay = (int)$range['end']->format('N') - 1;
                        $rangeColspan = $rangeEndDay - $rangeStartDay + 1;

                        // Calculate offset within cluster
                        $offsetCols = $rangeStartDay - $startDay;
                        $remainingCols = $colspan - $offsetCols - $rangeColspan;

                        $startDayNum = intval($range['start']->format('j'));
                        $endDayNum = intval($range['end']->format('j'));
                        $dateRange = $startDayNum . '-' . $endDayNum;

                        echo '<tr style="border:none; background-color:#fff;">';

                        // Empty cells before the event
                        if ($offsetCols > 0) {
                            echo '<td colspan="' . $offsetCols . '" style="padding:0;border:none;background:transparent;"></td>';
                        }

                        // Event cell
                        echo '<td colspan="' . $rangeColspan . '" style="padding:2px;border:none;background:transparent;">';
                        echo '<div class="gwt-week-band">';
                        $displayTitle = $dateRange . ': ' . $range['title'];
                        if ( ! empty($range['url']) ){
                            echo '<a href="'. esc_url($range['url']) .'" target="_blank" rel="noopener" style="font-weight:100;color:#20603d;text-decoration:none;">'. esc_html($displayTitle) .'</a>';
                        } else {
                            echo '<span style="font-weight:100;color:#20603d;">'. esc_html($displayTitle) .'</span>';
                        }
                        echo '</div>';
                        echo '</td>';

                        // Empty cells after the event
                        if ($remainingCols > 0) {
                            echo '<td colspan="' . $remainingCols . '" style="padding:0;border:none;background:transparent;"></td>';
                        }

                        echo '</tr>';
                    }

                    // Add single-day events that fall within this cluster
                    for ($d = $startDay; $d <= $endDay; $d++) {
                        $cur = clone $wk['start']; $cur->modify('+'.$d.' days');
                        $dateStr = $cur->format('Y-m-d');
                        $inMonth = ($cur->format('Y-m') === $current->format('Y-m'));

                        if ($inMonth && !empty($byDate[$dateStr])) {
                            // Calculate offset for this day within the cluster
                            $dayOffset = $d - $startDay;

                            foreach ($byDate[$dateStr] as $item) {
                                $dayNum = intval($cur->format('j'));
                                echo '<tr style="border:none; background-color:#fff;">';

                                // Empty cells before this day
                                if ($dayOffset > 0) {
                                    echo '<td colspan="' . $dayOffset . '" style="padding:0;border:none;background:transparent;"></td>';
                                }

                                // Single-day event cell
                                echo '<td style="padding:2px;border:none;background:transparent;">';
                                echo '<div class="gwt-week-band">';
                                $label = $dayNum . ': ' . $item['title'];
                                if ( ! empty( $item['url'] ) ) {
                                    echo '<a href="' . esc_url( $item['url'] ) . '" target="_blank" rel="noopener" style="font-weight:100;color:#20603d;text-decoration:none;">' . esc_html( $label ) . '</a>';
                                } else {
                                    echo '<span style="font-weight:100;color:#20603d;">' . esc_html( $label ) . '</span>';
                                }
                                echo '</div>';
                                echo '</td>';

                                // Empty cells after this day
                                $remainingCols = $colspan - $dayOffset - 1;
                                if ($remainingCols > 0) {
                                    echo '<td colspan="' . $remainingCols . '" style="padding:0;border:none;background:transparent;"></td>';
                                }

                                echo '</tr>';
                            }
                        }
                    }

                    echo '</tbody>';
                    echo '</table>';

                    echo '</td>';

                    // Mark cluster days as rendered
                    for ($d = $startDay; $d <= $endDay; $d++) {
                        $dayRendered[$d] = true;
                    }
                    $currentDay = $endDay + 1;
                }

                // Render remaining empty cells
                for ($d = $currentDay; $d < 7; $d++) {
                    if (!$dayRendered[$d]) {
                        $cur = clone $wk['start']; $cur->modify('+'.$d.' days');
                        $inMonth = ($cur->format('Y-m') === $current->format('Y-m'));
                        $dateStr = $cur->format('Y-m-d');

                        echo '<td style="padding:8px;border:1px solid #e5e5e5;background:'. ($inMonth?'#fff':'#fafafa') .';min-height:80px;vertical-align:top;width:14.28%;">';
                        echo '<div style="font-weight:bold;color:#006837;opacity:'. ($inMonth? '1':'0.5') .';">' . intval($cur->format('j')) . '</div>';
                        if ( $inMonth && ! empty( $byDate[$dateStr] ) ) {
                            foreach ( $byDate[$dateStr] as $item ) {
                                $label = $item['title'];
                                if ( ! empty( $item['url'] ) ) {
                                    echo '<div style="font-size:12px;line-height:1.2;margin-top:4px;"><a href="' . esc_url( $item['url'] ) . '" target="_blank" rel="noopener">' . esc_html( $label ) . '</a></div>';
                                } else {
                                    echo '<div style="font-size:12px;line-height:1.2;margin-top:4px;">' . esc_html( $label ) . '</div>';
                                }
                            }
                        }
                        echo '</td>';
                    }
                }

                echo '</tr>';
            }
            echo '</tbody>';
            echo '</table>';
        }

        return ob_get_clean();
    }
    add_action( 'init', function(){ add_shortcode( 'gwt_calendar', 'gwt_calendar_shortcode' ); } );
}

// -----------------------------------------------------------------------------
// Shortcode: [gwt_announcements]
// Renders announcements managed by the CBC Calendar & Announcements plugin/theme.
// Attributes:
//  - limit: max number of items (default 5; 0 = all)
//  - layout: list|cards (default list)
//  - show_image: 1|0 (default 1)
//  - link_title: 1|0 (default 1)
//  - header: text to display above the list (default empty)
//  - target: _self|_blank (default _blank)
// -----------------------------------------------------------------------------
if ( ! function_exists( 'gwt_announcements_shortcode' ) ) {
    function gwt_announcements_shortcode( $atts = array(), $content = '', $tag = '' ) {
        $atts = shortcode_atts( array(
            'limit'      => 5,
            'layout'     => 'list',    // list|cards|ticker
            'show_image' => '1',       // 1|0
            'link_title' => '1',       // 1|0
            'header'     => '',        // optional heading text
            'target'     => '_blank',  // _self|_blank
        ), $atts, 'gwt_announcements' );

        // Always fetch via the same source as gwt_calendar
        $cbc = apply_filters('cbc_calendar_options', []);
        $text = isset($cbc['announcements']) ? (string)$cbc['announcements'] : '';

        // Parse lines: message|url|image
        $items = array();
        if ( $text !== '' ) {
            $lines = preg_split('/\r\n|\r|\n/', $text);
            foreach ( $lines as $line ) {
                $line = trim( (string)$line ); if ( $line === '' ) continue;
                $parts = array_map( 'trim', explode('|', $line));
                $msg = $parts[0] ?? '';
                if ( $msg === '' ) continue;
                $url = $parts[1] ?? '';
                $img = $parts[2] ?? '';
                $items[] = array('message'=>$msg, 'url'=>$url, 'image'=>$img);
            }
        }

        if ( $atts['limit'] > 0 ) {
            $items = array_slice( $items, 0, intval($atts['limit']) );
        }

        $layout = ($atts['layout'] === 'cards' || $atts['layout'] === 'ticker') ? $atts['layout'] : 'list';
        $show_image = ($atts['show_image'] === '1');
        $link_title = ($atts['link_title'] === '1');
        $target = ($atts['target'] === '_self') ? '_self' : '_blank';

        ob_start();
        // Optional header
        if ( $atts['header'] !== '' ) {
            if ( function_exists('govph_section_header') ) {
                echo govph_section_header( $atts['header'], array( 'id' => 'gwt_announcements_header', 'text_alignment' => 'center' , 'classes' => 'drop-shadow font-semibold md:font-bold text-white p-2 bg-gradient-to-r text-center px-5 !text-sm md:!text-lg') );
            } else {
                echo '<h2 class="gwt-announcements-header">' . esc_html( $atts['header'] ) . '</h2>';
            }
        }

        if ( empty($items) ) {
            echo '<div class="gwt-announcements-empty" style="color:#666;">' . esc_html__( 'No announcements at the moment.', 'gwt_wp' ) . '</div>';
            return ob_get_clean();
        }

        if ( $layout === 'cards' ) {
            echo '<div class="gwt-announcements-cards" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:12px;">';
            foreach ( $items as $it ) {
                echo '<div class="card" style="border:1px solid #e5e5e5;border-radius:6px;overflow:hidden;background:#fff;display:flex;flex-direction:column;">';
                if ( $show_image && ! empty($it['image']) ) {
                    echo '<div class="card-image" style="aspect-ratio:16/9;overflow:hidden;background:#f5f5f5;">'
                       . '<img src="' . esc_url( $it['image'] ) . '" alt="" style="width:100%;height:100%;object-fit:cover;" />'
                       . '</div>';
                }
                echo '<div class="card-content" style="padding:12px;">';
                if ( $link_title && ! empty($it['url']) ) {
                    echo '<a href="' . esc_url( $it['url'] ) . '" target="' . esc_attr($target) . '" rel="noopener" style="color:#00391a;">' . esc_html( $it['message'] ) . '</a>';
                } else {
                    echo '<div style="color:#00391a;">' . esc_html( $it['message'] ) . '</div>';
                }
                echo '</div>';
                echo '</div>';
            }
            echo '</div>';
        } elseif ( $layout === 'ticker' ) {
            // Inject CSS once per page using constant guard
            if ( ! defined('CBC_ANNOUNCEMENTS_TICKER_CSS') ) {
                define('CBC_ANNOUNCEMENTS_TICKER_CSS', true);
                echo '<style id="cbc-announcements-ticker-css">'
                . '.scrolling-container{position:relative;overflow:hidden;width:100%;}'
                . '.scrolling-track{display:flex!important;flex-wrap:nowrap!important;align-items:center;gap:1rem;animation:cbc-marquee-linear 35s linear infinite;white-space:nowrap;will-change:transform;}'
                . '.scrolling-content{flex:0 0 auto;display:flex;align-items:center;gap:8px;min-width:300px;padding:0.5rem 1rem;white-space:nowrap;}'
                . '.scrolling-content .thumb{width:28px;height:28px;flex:0 0 28px;border-radius:4px;overflow:hidden;background:#f2f2f2;}'
                . '.scrolling-content .thumb img{width:100%;height:100%;object-fit:cover;display:block;}'
                . '.scrolling-container:hover .scrolling-track{animation-play-state:paused;}'
                . '@keyframes cbc-marquee-linear{0%{transform:translateX(0);}100%{transform:translateX(-50%);} }'
                . '@media (max-width:640px){.scrolling-content{min-width:220px;padding:0.5rem 0.75rem;}}'
                . '@media (prefers-reduced-motion:reduce){.scrolling-track{animation-duration:0s;animation-iteration-count:1;transform:none;}}'
                . '</style>';
            }

            // Ticker markup: duplicate items for seamless loop
            echo '<div class="scrolling-container reveal-on-scroll-300 opacity-0" id="cbc-announcements-ticker">';
            echo '<div class="scrolling-track" data-basehtml="">';
            for ( $dup = 0; $dup < 1; $dup++ ) {
                foreach ( $items as $it ) {
                    echo '<div class="scrolling-content">';
                    if ( $show_image && ! empty($it['image']) ) {
                        echo '<span class="thumb"><img src="' . esc_url($it['image']) . '" alt="" loading="lazy" /></span>';
                    }
                    if ( $link_title && ! empty($it['url']) ) {
                        echo '<a href="' . esc_url($it['url']) . '" target="' . esc_attr($target) . '" rel="noopener" style="color:#00391a;">' . esc_html($it['message']) . '</a>';
                    } else {
                        echo '<span style="color:#00391a;">' . esc_html($it['message']) . '</span>';
                    }
                    echo '</div>';
                }
            }
            echo '</div>';
            echo '</div>';

            // Lightweight dynamic script: set animation duration based on total width; ensure enough clones
            echo '<script>(function(){try{var c=document.getElementById("cbc-announcements-ticker");if(!c){c=document.querySelector(".scrolling-container");}if(!c)return;var t=c.querySelector(".scrolling-track");if(!t)return;if(window.matchMedia&&window.matchMedia("(prefers-reduced-motion: reduce)").matches){t.style.animation="none";return;}function ensureClones(){var base=t.getAttribute("data-basehtml");if(!base){base=t.innerHTML;t.setAttribute("data-basehtml",base);}var maxExtra=4;var clones=0;t.innerHTML=base+base;while(t.scrollWidth < c.clientWidth*2 && clones < maxExtra){t.innerHTML+=base;clones++;}}function setDuration(){ensureClones();var w=t.scrollWidth;var speed=(window.innerWidth<640?40:60);var dur=w/speed;t.style.animationDuration=dur.toFixed(2)+"s";}setDuration();window.addEventListener("resize",function(){clearTimeout(window._cbcTickerTO);window._cbcTickerTO=setTimeout(setDuration,150);});}catch(e){/* noop */}})();</script>';
        } else {
            // list layout
            echo '<ul class="gwt-announcements-list" style="list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:10px;">';
            foreach ( $items as $it ) {
                echo '<li class="gwt-announcement" style="display:flex;gap:10px;align-items:flex-start;">';
                if ( $show_image && ! empty($it['image']) ) {
                    echo '<div class="thumb" style="width:64px;height:64px;flex:0 0 64px;border-radius:4px;overflow:hidden;background:#f5f5f5;">'
                       . '<img src="' . esc_url( $it['image'] ) . '" alt="" style="width:100%;height:100%;object-fit:cover;" />'
                       . '</div>';
                }
                echo '<div class="body" style="min-width:0;">';
                if ( $link_title && ! empty($it['url']) ) {
                    echo '<a href="' . esc_url( $it['url'] ) . '" target="' . esc_attr($target) . '" rel="noopener" style="color:#00391a;">' . esc_html( $it['message'] ) . '</a>';
                } else {
                    echo '<div style="color:#00391a;">' . esc_html( $it['message'] ) . '</div>';
                }
                echo '</div>';
                echo '</li>';
            }
            echo '</ul>';
        }

        return ob_get_clean();
    }
    add_action( 'init', function(){ add_shortcode( 'gwt_announcements', 'gwt_announcements_shortcode' ); } );
}

