<?php
/**
 * The template part for displaying single posts
 *
 * @package GWT
 * @since Government Website Template 2.0
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	
	<!-- entry-content -->
	<div class="entry-content">
		<div class="entry-meta my-1 flex flex-col leading-[0.8rem] mb-3">
			<?php gwt_wp_posted_on(); ?>
			<p></p>
		</div>
		<?php the_content(); ?>
		<?php
			wp_link_pages( array(
				'before' => '<div class="page-links">' . __( 'Pages:', 'gwt_wp' ),
				'after'  => '</div>',
			) );
		?>
	</div>
	
</article>
