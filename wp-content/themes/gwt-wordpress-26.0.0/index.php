<?php
/**
 * The main template file
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 *
 * @package GWT
 * @since Government Website Template 2.0
 */

get_header(); 
include_once('inc/banner.php');
?>
<?php govph_displayoptions( 'govph_panel_top' ); ?>
<div class="container-main relative overflow-hidden" role="document">
    <div id="main-content" class="row">

        <div id="content" class="text-justify overflow-hidden gap-4 <?php govph_displayoptions( 'govph_content_position' ); ?>columns"
             style="display: flex; flex-direction: column; justify-content: space-between;"
            role="main">

            <?php

            // Build Announcements, Events, Holidays from Theme Options
            $opt = get_option('govph_options');

            // Manual announcements (Message|URL)
            $manualAnnouncements = [];
            if (!empty($opt['govph_announcements'])) {
                $lines = preg_split("/(\r\n|\n|\r)/", $opt['govph_announcements']);
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (!$line) continue;
                    $parts = array_map('trim', explode('|', $line, 2));
                    $manualAnnouncements[] = [
                        'text' => $parts[0],
                        'url'  => $parts[1] ?? ''
                    ];
                }
            }

            // Events (YYYY-MM-DD|Title|URL|Type|Location)
            $events = [];
            if (!empty($opt['govph_events'])) {
                $lines = preg_split("/(\r\n|\n|\r)/", $opt['govph_events']);
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (!$line) continue;
                    $parts = array_map('trim', explode('|', $line));
                    $date = $parts[0] ?? '';
                    $title = $parts[1] ?? '';
                    if (!$date || !$title) continue;
                    $url = $parts[2] ?? '';
                    $type = $parts[3] ?? '';
                    $loc  = $parts[4] ?? '';
                    $events[] = [
                        'date' => $date,
                        'title'=> $title,
                        'url'  => $url,
                        'type' => $type,
                        'loc'  => $loc,
                    ];
                }
            }

            // Holidays (YYYY-MM-DD|Name|Scope)
            $holidays = [];
            if (!empty($opt['govph_holidays'])) {
                $lines = preg_split("/(\r\n|\n|\r)/", $opt['govph_holidays']);
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (!$line) continue;
                    $parts = array_map('trim', explode('|', $line));
                    $date = $parts[0] ?? '';
                    $name = $parts[1] ?? '';
                    if (!$date || !$name) continue;
                    $scope = $parts[2] ?? '';
                    $holidays[] = [
                        'date' => $date,
                        'name' => $name,
                        'scope'=> $scope,
                    ];
                }
            }

            // Build upcoming items for ticker (next 14 days)
            $today = new DateTime('today');
            $cutoff = (new DateTime('today'))->modify('+14 days');
            $upcoming = [];
            foreach ($events as $e) {
                $d = DateTime::createFromFormat('Y-m-d', $e['date']);
                if ($d && $d >= $today && $d <= $cutoff) {
                    $label = $d->format('M d') . ': ' . $e['title'];
                    $upcoming[] = [ 'text' => $label, 'url' => $e['url'] ?? '' ];
                }
            }
            foreach ($holidays as $h) {
                $d = DateTime::createFromFormat('Y-m-d', $h['date']);
                if ($d && $d >= $today && $d <= $cutoff) {
                    $label = $d->format('M d') . ': ' . $h['name'];
                    $upcoming[] = [ 'text' => $label, 'url' => '' ];
                }
            }

            $announcements = array_merge($manualAnnouncements, $upcoming);

            // Render Announcements Ticker if any
            if (count($announcements) > 0) {
                echo govph_section_header('Announcements', ['id' => 'announcements_header', 'text_alignment' => "left"]);
                echo '<div class="scrolling-container"><div class="scrolling-track">';
                // Duplicate once for continuous effect
                for ($i = 0; $i < 2; $i++) {
                    foreach ($announcements as $a) {
                        $text = esc_html($a['text']);
                        $url = !empty($a['url']) ? esc_url($a['url']) : '';
                        echo '<div class="scrolling-content" style="display:flex;align-items:center;min-width:300px;padding:0.5rem 1rem;">';
                        if ($url) {
                            echo '<a href="' . $url . '" target="_blank" rel="noopener" style="color:#006837;text-decoration:underline;">' . $text . '</a>';
                        } else {
                            echo '<span>' . $text . '</span>';
                        }
                        echo '</div>';
                    }
                }
                echo '</div></div>';
            }

            // Calendar UI data prep for current month
            $current = new DateTime('first day of this month');
            $startDow = (int)$current->format('N'); // 1 (Mon) - 7 (Sun)
            $daysInMonth = (int)$current->format('t');
            $monthLabel = $current->format('F Y');

            // Index events/holidays by date
            $byDate = [];
            foreach ($events as $e) { $byDate[$e['date']][] = ['type' => 'event', 'title' => $e['title'], 'url' => $e['url']]; }
            foreach ($holidays as $h) { $byDate[$h['date']][] = ['type' => 'holiday', 'title' => $h['name'], 'url' => '']; }

            echo govph_section_header('Calendar '. esc_html($monthLabel) , ['id' => 'center_chief_header', 'text_alignment' => "left"]);

            echo '<div class="calendar-grid" style="display:grid;grid-template-columns:repeat(7,1fr);gap:6px;padding:8px;">';
            // Weekday headers (Mon-Sun)
            $wd = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
            foreach ($wd as $w) { echo '<div style="font-weight:bold;text-align:center;background:#f0f4f0;padding:6px;">' . esc_html($w) . '</div>'; }
            // Empty slots before first day
            for ($i=1; $i<$startDow; $i++) { echo '<div style="padding:8px;border:1px solid #e5e5e5;background:#fafafa;"></div>'; }
            // Days
            for ($day=1; $day <= $daysInMonth; $day++) {
                $dateStr = $current->format('Y-m-') . str_pad((string)$day, 2, '0', STR_PAD_LEFT);
                echo '<div style="padding:8px;border:1px solid #e5e5e5;background:#fff;min-height:80px;">';
                echo '<div style="font-weight:bold;color:#006837;">' . $day . '</div>';
                if (!empty($byDate[$dateStr])) {
                    foreach ($byDate[$dateStr] as $item) {
                        $label = ($item['type']==='holiday' ? 'Holiday: ' : '') . $item['title'];
                        if (!empty($item['url'])) {
                            echo '<div style="font-size:12px;line-height:1.2;margin-top:4px;"><a href="' . esc_url($item['url']) . '" target="_blank" rel="noopener">' . esc_html($label) . '</a></div>';
                        } else {
                            echo '<div style="font-size:12px;line-height:1.2;margin-top:4px;">' . esc_html($label) . '</div>';
                        }
                    }
                }
                echo '</div>';
            }
            echo '</div>';

            // Build videos from Theme Options if provided
            $videos = [];
            $option = get_option('govph_options');
            if (!empty($option['govph_featured_videos'])) {
                $lines = preg_split("/(\r\n|\n|\r)/", $option['govph_featured_videos']);
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (!$line) { continue; }
                    $parts = array_map('trim', explode('|', $line, 3));
                    $url = $parts[0] ?? '';
                    if ($url && filter_var($url, FILTER_VALIDATE_URL) && strpos($url, 'facebook.com') !== false) {
                        $encoded = urlencode($url);
                        $src = "https://www.facebook.com/plugins/video.php?height=314&href={$encoded}&show_text=false&width=560&t=0";
                        $videos[] = [
                            'src' => $src,
                            'title' => $parts[1] ?? '',
                            'description' => $parts[2] ?? '',
                        ];
                    }
                }
            }

            function limit_words( $string, $limit = 10 ) {
                $words = explode( ' ', $string );
                if ( count( $words ) > $limit ) {
                    return implode( ' ', array_slice( $words, 0, $limit ) ) . '...';
                } else {
                    return $string;
                }
            }
            ?>

            <?php  if (count($videos) > 0):
                echo govph_section_header('Videos', ['id' => 'videos_header', 'text_alignment' => "left"]);
            endif; ?>

            <div class="grid grid-cols-3 gap-2 lg:gap-5">
                <?php foreach ( $videos as $video ) : ?>
                    <div class="relative flex flex-col gap-2 w-auto h-fit reveal-on-scroll">
                        <div class="relative w-full shadow" style="padding-bottom: 56.25%;">
                            <iframe class="absolute top-0 left-0 w-full h-full border-none rounded"
                                    src="<?php echo esc_url( $video['src'] ); ?>"
                                    style="border:none;overflow:hidden; margin: 0 auto;"
                                    scrolling="no"
                                    frameborder="0"
                                    allowfullscreen="true"
                                    allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share">
                            </iframe>
                        </div>
                        <div>
                            <h3 class="font-semibold text-sm whitespace-nowrap overflow-hidden overflow-ellipsis text-[#006837]"><?php echo esc_html( $video['title'] ); ?></h3>
                            <p class="text-sm leading-[1.1rem]"><?php echo esc_html( limit_words( $video['description'], 20 ) ); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>


            <?php
            // Load Facebook post URLs from Theme Options (Appearance > Theme Options), one per line.
            $option = get_option('govph_options');
            $postUrls = [];
            if (!empty($option['govph_facebook_posts'])) {
                $lines = preg_split("/(\r\n|\n|\r)/", $option['govph_facebook_posts']);
                foreach ($lines as $line) {
                    $url = trim($line);
                    if ($url && filter_var($url, FILTER_VALIDATE_URL) && strpos($url, 'facebook.com') !== false) {
                        $postUrls[] = $url;
                    }
                }
            }

            $iframeWidth = "300";
            $iframeHeight = "300";

            // This width is passed to Facebook to determine the post's internal layout.
            $facebookPostWidth = "100";

            $animationDuration = "60s";
            $gapBetweenPosts = "1rem";
            ?>

            <?php if (count($postUrls) > 0):
                echo govph_section_header('Facebook Posts', ['id' => 'facebook_posts_header', 'text_alignment' => "left"]);
            endif; ?>
            <div class="scrolling-container">
                <div class="scrolling-track">
                    <?php
                    for ($i = 0; $i < 2; $i++):
                        foreach ($postUrls as $url):
                            $encodedUrl = urlencode($url);
                            $iframeSrc = "https://www.facebook.com/plugins/post.php?href={$encodedUrl}&show_text=false&width={$facebookPostWidth}";
                            ?>
                            <div class="scrolling-content">
                                <iframe
                                        src="<?php echo $iframeSrc; ?>"
                                        width="<?php echo $iframeWidth; ?>"
                                        height="<?php echo $iframeHeight; ?>"
                                        style="border:none;overflow:hidden"
                                        scrolling="no"
                                        frameborder="0"
                                        allowfullscreen="true"
                                        allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share">
                                </iframe>
                            </div>
                        <?php
                        endforeach;
                    endfor;
                    ?>
                </div>
            </div>
        </div>
        <!-- start content -->


        <!-- end content -->
        <?php
				if(is_active_sidebar('left-sidebar')){
					govph_displayoptions( 'govph_sidebar_left' );
				}
				?>
        <?php
				if(is_active_sidebar('right-sidebar')){
					govph_displayoptions( 'govph_sidebar_right' );
				}
				?>

    </div>
</div>


<?php add_shortcode('custom_post_loop', 'custom_post_layout'); govph_displayoptions( 'govph_panel_bottom' ); ?>

<?php get_footer(); ?>