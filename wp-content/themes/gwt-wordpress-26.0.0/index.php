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

            // Build Announcements, Events, Holidays from plugin (fallback to theme via plugin)
            $cbc = apply_filters('cbc_calendar_options', []);

            // Manual announcements (Message|URL|ImageURL)
            $manualAnnouncements = [];
            if (!empty($cbc['announcements'])) {
                $lines = preg_split("/(\r\n|\n|\r)/", (string)$cbc['announcements']);
                foreach ($lines as $line) {
                    $line = trim((string)$line);
                    if ($line === '') continue;
                    $parts = array_map('trim', explode('|', $line, 3));
                    $manualAnnouncements[] = [
                        'text' => $parts[0] ?? '',
                        'url'  => $parts[1] ?? '',
                        'img'  => $parts[2] ?? '',
                    ];
                }
            }

            // Events (YYYY-MM-DD|Title|URL|Type|Location|announce?)
            $events = [];
            if (!empty($cbc['events'])) {
                $lines = preg_split("/(\r\n|\n|\r)/", (string)$cbc['events']);
                foreach ($lines as $line) {
                    $line = trim((string)$line);
                    if ($line === '') continue;
                    $parts = array_map('trim', explode('|', $line));
                    $date = $parts[0] ?? '';
                    $title = $parts[1] ?? '';
                    if (!$date || !$title) continue;
                    $url = $parts[2] ?? '';
                    $type = $parts[3] ?? '';
                    $loc  = $parts[4] ?? '';
                    // Detect announce flag in any trailing part
                    $announceFlag = false;
                    if (count($parts) > 5) {
                        for ($i=5; $i<count($parts); $i++) {
                            if (strtolower(trim($parts[$i])) === 'announce') { $announceFlag = true; break; }
                        }
                    }
                    // Some users may append announce directly as the 5th part (location)
                    if (!$announceFlag && strtolower(trim($loc)) === 'announce') { $announceFlag = true; $loc = ''; }
                    $events[] = [ 'date'=>$date, 'title'=>$title, 'url'=>$url, 'type'=>$type, 'loc'=>$loc, 'announce'=>$announceFlag ];
                }
            }

            // Holidays (YYYY-MM-DD|Name|Scope|announce?)
            $holidays = [];
            if (!empty($cbc['holidays'])) {
                $lines = preg_split("/(\r\n|\n|\r)/", (string)$cbc['holidays']);
                foreach ($lines as $line) {
                    $line = trim((string)$line);
                    if ($line === '') continue;
                    $parts = array_map('trim', explode('|', $line));
                    $date = $parts[0] ?? '';
                    $name = $parts[1] ?? '';
                    if (!$date || !$name) continue;
                    $scope = $parts[2] ?? '';
                    $announceFlag = false;
                    if (count($parts) > 2) {
                        for ($i=2; $i<count($parts); $i++) {
                            if (strtolower(trim($parts[$i])) === 'announce') { $announceFlag = true; break; }
                        }
                    }
                    if (!$announceFlag && strtolower(trim($scope)) === 'announce') { $announceFlag = true; $scope = ''; }
                    $holidays[] = [ 'date'=>$date, 'name'=>$name, 'scope'=>$scope, 'announce'=>$announceFlag ];
                }
            }

            // Build upcoming items for ticker (next 14 days) but only those explicitly flagged with announce
            $today = new DateTime('today');
            $cutoff = (new DateTime('today'))->modify('+14 days');
            $upcoming = [];
            foreach ($events as $e) {
                if (empty($e['announce'])) continue;
                $d = DateTime::createFromFormat('Y-m-d', $e['date']);
                if ($d && $d >= $today && $d <= $cutoff) {
                    $label = $d->format('M d') . ': ' . $e['title'];
                    $upcoming[] = [ 'text' => $label, 'url' => $e['url'] ?? '', 'img' => '' ];
                }
            }
            foreach ($holidays as $h) {
                if (empty($h['announce'])) continue;
                $d = DateTime::createFromFormat('Y-m-d', $h['date']);
                if ($d && $d >= $today && $d <= $cutoff) {
                    $label = $d->format('M d') . ': ' . $h['name'];
                    $upcoming[] = [ 'text' => $label, 'url' => '', 'img' => '' ];
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
                        $text = esc_html($a['text'] ?? '');
                        $url  = !empty($a['url']) ? esc_url($a['url']) : '';
                        $img  = !empty($a['img']) ? esc_url($a['img']) : '';
                        echo '<div class="scrolling-content" style="display:flex;align-items:center;gap:8px;min-width:300px;padding:0.5rem 1rem;">';
                        if ($img) {
                            echo '<img src="' . $img . '" alt="" style="height:24px;width:auto;border-radius:3px;object-fit:cover;" loading="lazy" />';
                        }
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