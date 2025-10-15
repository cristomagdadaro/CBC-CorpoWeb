<?php
/**
 * The Sidebar containing the main widget areas.
 *
 * @package gwt_wp
 */
?>
<aside id="sidebar-right"
       class="mt-5 sm:mt-0 <?php govph_displayoptions( 'govph_sidebar_position_right' ); ?>columns"
       role="complementary">
    <?php
    $swap = false;
    if ( is_front_page()) :
        $swap = is_front_page(); ?>


    <?php  echo govph_section_header( 'News and Updates', [ 'id' => 'news_posts_header', 'swap' => $swap ] ); ?>
    <aside class="widget callout border-none secondary widget_block reveal-on-scroll-500 opacity-0">
        <?php echo do_shortcode('[gwt_latest_posts posts="4"  excerpt_length="0" show_date="1" show_image="0" show_author="0" post_layout="list"]'); ?>
    </aside>

    <?php else:
    echo govph_section_header( 'Popular Posts', [ 'id' => 'popular_posts_header', 'swap' => $swap ] ); ?>
    <aside class="widget callout border-none secondary widget_block reveal-on-scroll-500 opacity-0">
        <?php echo do_shortcode( '[pm_popular_posts cache_minutes="0" titles_only="1"]' ); ?>
    </aside>
    <?php endif; ?>

    <aside class="widget callout border-none secondary widget_block">
        <div class="flex flex-col items-center gap-2">
            <div class="grid grid-cols-2 gap-4 items-center">
                <a href="https://privacy.gov.ph/transparency-seal/" class="flex justify-center">
                    <img
                            decoding="async"
                            id="tp-seal"
                            src="/wp-content/themes/gwt-wordpress-26.0.0/images/transparency-seal-160x160.png"
                            alt="transparency seal logo"
                            title="Transparency Seal"
                            class="w-32 h-32 object-contain md:w-40 md:h-40"
                    >
                </a>
                <a href="https://www.foi.gov.ph/" class="flex justify-center">
                    <img
                            decoding="async"
                            id="foi-logo"
                            src="/wp-content/themes/gwt-wordpress-26.0.0/images/foi-logo-160x160.png"
                            alt="freedom of information logo"
                            title="Freedom of Information"
                            class="w-32 h-32 object-contain md:w-40 md:h-40"
                    >
                </a>
            </div>

        </div>
    </aside>

    <?php do_action( 'before_sidebar' ); ?>
    <?php if ( is_active_sidebar( 'right-sidebar' ) ) {
        dynamic_sidebar( 'right-sidebar' );
    } ?>
</aside>
