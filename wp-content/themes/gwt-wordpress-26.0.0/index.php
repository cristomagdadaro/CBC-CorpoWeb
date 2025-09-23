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

            // Calendar: use shortcode for reusable, override-capable rendering
            echo do_shortcode('[gwt_calendar mode="auto" header="1" max="30"]');
            ?>
        </div>
        <!-- start content -->


        <!-- end content -->
        <?php
				if(is_active_sidebar('left-sidebar')){
					govph_displayoptions( 'govph_sidebar_left' );
				}
				?>
        <?php
				// Always render right sidebar (width classes now account for forced sidebar)
				govph_displayoptions( 'govph_sidebar_right' );
				?>

    </div>
</div>


<?php add_shortcode('custom_post_loop', 'custom_post_layout'); govph_displayoptions( 'govph_panel_bottom' ); ?>

<?php get_footer(); ?>