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

            <?php  if (count($videos) > 0):  ?>
                <h2 class="text-lg sm:text-xl text-white p-2 md:text-2xl bg-gradient-to-r from-[#1f5d2b] to-[#a2b917] lg:text-3xl text-left px-5"><strong>Videos</strong></h2>
            <?php endif; ?>

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

            <?php if (count($postUrls) > 0): ?>
                <h2 class="text-lg sm:text-xl text-white p-2 md:text-2xl bg-gradient-to-r from-[#1f5d2b] to-[#a2b917] lg:text-3xl text-left px-5"><strong>Facebook Posts</strong></h2>
            <?php endif; ?>
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