<?php
/**
 * Server-side rendering of the `core/latest-posts` block.
 *
 * @package WordPress
 */

/**
 * The excerpt length set by the Latest Posts core block
 * set at render time and used by the block itself.
 *
 * @var int
 */
global $block_core_latest_posts_excerpt_length;
$block_core_latest_posts_excerpt_length = 0;

/**
 * Callback for the excerpt_length filter used by
 * the Latest Posts block at render time.
 *
 * @return int Returns the global $block_core_latest_posts_excerpt_length variable
 *             to allow the excerpt_length filter respect the Latest Block setting.
 */
function block_core_latest_posts_get_excerpt_length() {
	global $block_core_latest_posts_excerpt_length;
	return $block_core_latest_posts_excerpt_length;
}

/**
 * Renders the `core/latest-posts` block on server.
 *
 * @param array $attributes The block attributes.
 *
 * @return string Returns the post content with latest posts added.
 */
function render_block_core_latest_posts( $attributes ) {
	global $post, $block_core_latest_posts_excerpt_length;

	$args = array(
		'posts_per_page'      => $attributes['postsToShow'],
		'post_status'         => 'publish',
		'order'               => $attributes['order'],
		'orderby'             => $attributes['orderBy'],
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	);

	$block_core_latest_posts_excerpt_length = $attributes['excerptLength'];
	add_filter( 'excerpt_length', 'block_core_latest_posts_get_excerpt_length', 20 );

	if ( ! empty( $attributes['categories'] ) ) {
		$args['category__in'] = array_column( $attributes['categories'], 'id' );
	}
	if ( isset( $attributes['selectedAuthor'] ) ) {
		$args['author'] = $attributes['selectedAuthor'];
	}

	$query        = new WP_Query();
	$recent_posts = $query->query( $args );

	if ( isset( $attributes['displayFeaturedImage'] ) && $attributes['displayFeaturedImage'] ) {
		update_post_thumbnail_cache( $query );
	}

	// Reusable renderer to avoid duplicating logic for each post card.
	$render_item = function( $post, $attributes, $container_classes, $image_wrapper_template ) use ( &$block_core_latest_posts_excerpt_length ) {
		$post_link = esc_url( get_permalink( $post ) );
		$title     = get_the_title( $post );
		if ( ! $title ) {
			$title = __( '(no title)' );
		}

		$item_markup  = '<div class="' . esc_attr( $container_classes ) . '">';

		if ( ! empty( $attributes['displayFeaturedImage'] ) && has_post_thumbnail( $post ) ) {
			$image_classes = 'wp-block-latest-posts__featured-image object-cover object-top hover:brightness-75 hover:scale-105 duration-300 m-0 w-full h-full';
			if ( isset( $attributes['featuredImageAlign'] ) ) {
				$image_classes .= ' align' . $attributes['featuredImageAlign'];
			}
			$featured_image = get_the_post_thumbnail(
				$post,
				$attributes['featuredImageSizeSlug'],
				array(
					'class' => esc_attr( $image_classes ),
				)
			);
			if ( ! empty( $attributes['addLinkToFeaturedImage'] ) ) {
				$featured_image = sprintf(
					'<a href="%1$s" aria-label="%2$s">%3$s</a>',
					esc_url( $post_link ),
					esc_attr( $title ),
					$featured_image
				);
			}
			$item_markup .= sprintf( $image_wrapper_template, $featured_image );
		}

		$item_markup .= '<div class="flex flex-col h-full justify-center"><div class="flex flex-col leading-[1rem]">';
		$item_markup .= sprintf(
			'<a class="wp-block-latest-posts__post-title text-left font-bold sm:text-lg text-md" href="%1$s">%2$s</a>',
			esc_url( $post_link ),
			$title
		);

		if ( isset( $attributes['displayAuthor'] ) && $attributes['displayAuthor'] ) {
			$author_display_name = get_the_author_meta( 'display_name', $post->post_author );
			/* translators: byline. %s: current author. */
			$byline = sprintf( __( 'by %s' ), $author_display_name );
			if ( ! empty( $author_display_name ) ) {
				$item_markup .= sprintf(
					'<div class="wp-block-latest-posts__post-author text-xs">%1$s</div>',
					$byline
				);
			}
		}

		if ( isset( $attributes['displayPostDate'] ) && $attributes['displayPostDate'] ) {
			$item_markup .= sprintf(
				'<time datetime="%1$s" class="wp-block-latest-posts__post-date text-xs">%2$s</time>',
				esc_attr( get_the_date( 'c', $post ) ),
				get_the_date( '', $post )
			);
		}

		$item_markup .= '</div>';

		if ( isset( $attributes['displayPostContent'] ) && $attributes['displayPostContent']
		     && isset( $attributes['displayPostContentRadio'] ) && 'excerpt' === $attributes['displayPostContentRadio'] ) {
			$trimmed_excerpt = get_the_excerpt( $post );
			if ( str_ends_with( $trimmed_excerpt, ' [&hellip;]' ) ) {
				$excerpt_length = (int) apply_filters( 'excerpt_length', $block_core_latest_posts_excerpt_length );
				if ( $excerpt_length <= $block_core_latest_posts_excerpt_length ) {
					$trimmed_excerpt  = substr( $trimmed_excerpt, 0, -11 );
					$trimmed_excerpt .= sprintf(
						__( '… <a href="%1$s" rel="noopener noreferrer">Read more<span class="screen-reader-text">: %2$s</span></a>' ),
						esc_url( $post_link ),
						esc_html( $title )
					);
				}
			}
			if ( post_password_required( $post ) ) {
				$trimmed_excerpt = __( 'This content is password protected.' );
			}
			$item_markup .= sprintf(
				'<div class="wp-block-latest-posts__post-excerpt entry-content block leading-[1.1rem] text-sm">%1$s</div>',
				$trimmed_excerpt
			);
		}

		if ( isset( $attributes['displayPostContent'] ) && $attributes['displayPostContent']
		     && isset( $attributes['displayPostContentRadio'] ) && 'full_post' === $attributes['displayPostContentRadio'] ) {
			$post_content = html_entity_decode( $post->post_content, ENT_QUOTES, get_option( 'blog_charset' ) );
			if ( post_password_required( $post ) ) {
				$post_content = __( 'This content is password protected.' );
			}
			$item_markup .= sprintf(
				'<div class="wp-block-latest-posts__post-full-content">%1$s</div>',
				wp_kses_post( $post_content )
			);
		}

		$item_markup .= '</div></div>';

		return $item_markup;
	};

	$list_items_markup = '<div class="flex flex-col justify-between gap-1 md:gap-5">';

	$total = count( $recent_posts );
	$count = 0;
	foreach ( $recent_posts as $post ) {
		if ( 3 === $count ) {
			$list_items_markup .= '</div><div class="flex flex-col ';

			if (isset( $attributes['postLayout'] ) && 'grid' === $attributes['postLayout'])
				$list_items_markup .= 'gap-1 md:gap-5">';
			else
				$list_items_markup .= 'gap-1 lg:gap-0 lg:border">';
		}

		if ( $count < 3 || isset( $attributes['postLayout'] ) && 'grid' === $attributes['postLayout']) {
			$list_items_markup .= $render_item(
				$post,
				$attributes,
				'relative flex gap-2 md:gap-5 w-full items-stretch overflow-x-auto border p-1 md:p-3 bg-[#FDFCFD] rounded h-fit',
				'<div class="overflow-hidden rounded min-w-[10rem] md:min-w-[10rem] lg:min-w-[11rem] h-full">%s</div>'
			);
		} else {
			$list_items_markup .= $render_item(
				$post,
				$attributes,
				'relative flex gap-2 md:gap-5 w-full items-stretch overflow-x-auto border p-1 md:p-3 lg:border-none bg-[#FDFCFD] rounded h-fit lg:h-full',
				'<div class="overflow-hidden rounded min-w-[10rem] md:min-w-[10rem] lg:min-w-[11rem] h-full lg:hidden md:block ">%s</div>'
			);
			if ( $count < $total - 1 && isset( $attributes['postLayout'] ) && 'grid' !== $attributes['postLayout']) {
				$list_items_markup .= '<div class="border-b-4 border-[#215f27] mx-1 md:mx-3 lg:block hidden"></div>';
			}
		}

		$count++;
	}

	$list_items_markup .= '</div>';

	remove_filter( 'excerpt_length', 'block_core_latest_posts_get_excerpt_length', 20 );

	$classes = array( 'wp-block-latest-posts__list gap-5 flex lg:flex-row flex-col' . isset( $attributes['postLayout'] ) && 'grid' === $attributes['postLayout'] ? 'gap-1 md:gap-5' : 'gap-1 md:gap-5 flex lg:flex-row flex-col' );
	if ( isset( $attributes['postLayout'] ) && 'grid' === $attributes['postLayout'] ) {
		$classes[] = 'is-grid';
	}
	if ( isset( $attributes['columns'] ) && 'grid' === $attributes['postLayout'] ) {
		$classes[] = 'columns-' . $attributes['columns'];
	}
	if ( isset( $attributes['displayPostDate'] ) && $attributes['displayPostDate'] ) {
		$classes[] = 'has-dates';
	}
	if ( isset( $attributes['displayAuthor'] ) && $attributes['displayAuthor'] ) {
		$classes[] = 'has-author';
	}
	if ( isset( $attributes['style']['elements']['link']['color']['text'] ) ) {
		$classes[] = 'has-link-color';
	}

	$wrapper_attributes = get_block_wrapper_attributes( array( 'class' => implode( ' ', $classes ) ) );

	return sprintf(
		'<div %1$s>%2$s</div>',
		$wrapper_attributes,
		$list_items_markup
	);
}

/**
 * Registers the `core/latest-posts` block on server.
 */
function register_block_core_latest_posts() {
	register_block_type_from_metadata(
		__DIR__ . '/latest-posts',
		array(
			'render_callback' => 'render_block_core_latest_posts',
		)
	);
}
add_action( 'init', 'register_block_core_latest_posts' );

/**
 * Handles outdated versions of the `core/latest-posts` block by converting
 * attribute `categories` from a numeric string to an array with key `id`.
 *
 * This is done to accommodate the changes introduced in #20781 that sought to
 * add support for multiple categories to the block. However, given that this
 * block is dynamic, the usual provisions for block migration are insufficient,
 * as they only act when a block is loaded in the editor.
 *
 * TODO: Remove when and if the bottom client-side deprecation for this block
 * is removed.
 *
 * @param array $block A single parsed block object.
 *
 * @return array The migrated block object.
 */
function block_core_latest_posts_migrate_categories( $block ) {
	if (
		'core/latest-posts' === $block['blockName'] &&
		! empty( $block['attrs']['categories'] ) &&
		is_string( $block['attrs']['categories'] )
	) {
		$block['attrs']['categories'] = array(
			array( 'id' => absint( $block['attrs']['categories'] ) ),
		);
	}

	return $block;
}
add_filter( 'render_block_data', 'block_core_latest_posts_migrate_categories' );
