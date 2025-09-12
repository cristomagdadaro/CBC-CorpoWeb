<?php
/**
 * @package GWT
 * @since Government Website Template 2.0
 */
?>

<div class="post-box rounded">
	<article id="post-<?php the_ID(); ?>" <?php post_class('secondary rounded'); ?>>
		<div class="rounded flex relative flex-row text-gray-900 text-sm">
			<div class="rounded relative h-auto w-full flex items-center justify-center overflow-hidden bg-[#FDFCFD] border">
				<div class="md:p-3 p-1 w-full">
					<header>
						<?php if ( 'post' == get_post_type() ) : ?>
							<div class="entry-meta text-shadow-white">
								<?php gwt_wp_posted_on(); ?>
							</div>
						<?php endif; ?>
						<h2 class="entry-title text-left font-bold sm:text-lg text-md text-shadow-white"><a href="<?php the_permalink(); ?>"  rel="bookmark"><?php the_title(); ?></a></h2>

					</header>

					<?php if ( is_search() ) : // Only display Excerpts for Search ?>
						<div class="entry-summary leading-1">
							<?php //the_excerpt(); ?>
						</div>
					<?php else : ?>
						<div class="entry-content lg:block md:block hidden leading-1">
							<?php the_excerpt(); ?>
							<?php
							wp_link_pages( array(
								'before' => '<div class="page-links">' . __( 'Pages:', 'gwt_wp' ),
								'after'  => '</div>',
							) );
							?>
						</div>
					<?php endif; ?>

					<!-- footer entry-meta -->
					<footer class="entry-meta">
						<?php if ( 'post' == get_post_type() ) : // Hide category and tag text for pages on Search ?>
						<?php endif; ?>
					</footer>
				</>
			</div>
		</div>

	</article>
</div>