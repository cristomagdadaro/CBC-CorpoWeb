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

        </div>
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