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
include_once( 'inc/banner.php' );
?>
<?php govph_displayoptions( 'govph_panel_top' ); ?>
    <div class="container-main relative overflow-hidden" role="document">
        <div id="main-content" class="row">
            <!-- start content -->
            <div id="content"
                 class="text-justify overflow-hidden gap-4 <?php govph_displayoptions( 'govph_content_position' ); ?>columns"
                 style="display: flex; flex-direction: column; justify-content: space-between;"
                 role="main">
                <div>
                    <?php echo govph_section_header( 'Subscribe Now!', [ 'id' => 'popular_posts_header'] ); ?>
                    <aside class="widget callout border-none secondary widget_block">
                        <?php echo  do_shortcode('[newsletter_subscribe]'); ?>
                    </aside>
                </div>
                <div class="hidden grid grid-cols-1 md:grid-cols-2 grid-rows-1 gap-3 relative box-border">
                    <div>
                        <?php echo govph_section_header('Vision', ['id' => 'vision_header']); ?>
                        <aside class="widget callout border-none secondary widget_block reveal-on-scroll-500 opacity-0">
                            <div class="panel h-fit bg-transparent" style="max-height: 100vh;">
                                <figure class="text-center"><blockquote><p>A prosperous, secure, and sustainable food future - one crop at a time.</p></blockquote></figure>
                            </div>
                        </aside>
                    </div>
                    <div>
                        <?php echo govph_section_header('Mission', ['id' => 'mission_header']); ?>
                        <aside class="widget callout border-none secondary widget_block reveal-on-scroll-500 opacity-0">
                            <div class="panel h-fit bg-transparent" style="max-height: 100vh;">
                                <figure class="text-center"><blockquote><p>To improve the productivity and competitiveness of priority commodities through providing biotechnology capacity building services, fostering collaboration within the agricultural research community, and driving the development and utilization of modern and inclusive biotechnology for crop improvement, contributing to a resilient and prosperous agricultural landscape.</p></blockquote></figure>
                            </div>
                        </aside>
                    </div>
                </div>
            </div>
            <!-- end content -->
            <?php
            if ( is_active_sidebar( 'left-sidebar' ) ) {
                govph_displayoptions( 'govph_sidebar_left' );
            }
            ?>
            <?php
            govph_displayoptions( 'govph_sidebar_right' );
            ?>

        </div>
    </div>
</div>

<?php add_shortcode( 'custom_post_loop', 'custom_post_layout' );
govph_displayoptions( 'govph_panel_bottom' ); ?>

<?php get_footer(); ?>