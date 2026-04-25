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
    <?php do_action( 'before_sidebar' ); ?>
    <?php if ( is_active_sidebar( 'right-sidebar' ) ) {
        dynamic_sidebar( 'right-sidebar' );
    } ?>
    <?php if ( !is_front_page()) :
    echo govph_section_header( 'Popular Posts', [ 'id' => 'popular_posts_header'] ); ?>
    <aside class="widget callout border-none secondary widget_block reveal-on-scroll-500 opacity-0">
        <?php echo do_shortcode( '[pm_popular_posts cache_minutes="0" titles_only="1"]' ); ?>
    </aside>

    <?php echo govph_section_header( 'Subscribe Now!', [ 'id' => 'popular_posts_header'] ); ?>
    <aside class="widget callout border-none secondary widget_block">
        <?php echo  do_shortcode('[newsletter_subscribe]'); ?>
    </aside>
    <?php endif; ?>
</aside>
