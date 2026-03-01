<?php
/**
 * The Template for displaying all single posts.
 *
 * @package GWT
 * @since Government Website Template 2.0
 */

get_header();
include_once( get_template_directory() . '/inc/banner.php' );
?>
<?php govph_displayoptions( 'govph_panel_top' ); ?>

<div id="main-content" class="container-main" role="document">
    <div class="row">
        <div id="content" class="<?php govph_displayoptions( 'govph_content_position' ); ?>columns"
            role="main">
            <!-- for Version 1 -->
             <div class="large-12 container-main">
                <header>
	                <?php while ( have_posts() ) : the_post(); ?>
                        <h1 class="entry-title sm:text-2xl text-lg font-bold text-[#000000] relative pl-6">
                            <span class="absolute left-0 top-0 bottom-0 w-2 bg-gradient-to-b from-[#1f5d2b] to-[#a2b917]"></span>
                            <?php the_title(); ?>
                        </h1>
	                <?php endwhile; // end of the loop. ?>
                </header>
            </div>
            <!-- End for Version 1 -->
            <?php 
				while( have_posts() ) : the_post();
				
				get_template_part('template-parts/content', 'single');
				
				endwhile; //end of the loop 
			?>
        </div><!-- end content -->

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

    </div><!-- end row -->
</div><!-- end main -->

<?php govph_displayoptions( 'govph_panel_bottom' ); ?>

<?php get_footer(); ?>