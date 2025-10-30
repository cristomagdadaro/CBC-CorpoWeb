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

	// Determine layout mode once.
	$is_grid_layout = ( isset( $attributes['postLayout'] ) && 'grid' === $attributes['postLayout'] );

	// Reusable renderer to avoid duplicating logic for each post card.
	$render_item = function( $post, $attributes, $container_classes, $image_wrapper_template, $show_image = true, $is_right = false ) use ( $is_grid_layout, &$block_core_latest_posts_excerpt_length ) {
		$post_link = esc_url( get_permalink( $post ) );
		$title     = get_the_title( $post );
		if ( ! $title ) {
			$title = __( '(no title)' );
		}

		$item_markup  = '<div class="' . esc_attr( $container_classes ) . '">';

		if ( $show_image && ! empty( $attributes['displayFeaturedImage'] ) && has_post_thumbnail( $post ) ) {
			$image_classes = 'wp-block-latest-posts__featured-image object-cover object-center h-full w-full';
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
				$featured_image = '<a href="' . esc_url( $post_link ) . '" aria-label="' . esc_attr( $title ) . '">' . $featured_image . '</a>';
			}
			$item_markup .= sprintf( $image_wrapper_template, $featured_image );
		}

		if ( $show_image && has_post_thumbnail( $post ) && isset($attributes['showImageOnHover']) && $attributes['showImageOnHover'] ) {
			$meta = get_post_meta( $post->ID, 'pm_metrics', true );
			$views = intval( $meta['views'] ?? 0 );

			$featured_image = get_the_post_thumbnail(
				$post,
				'thumbnail',
				array(
					'class' => esc_attr( 'object-cover object-center transition-all duration-500 ease-out w-34 max-h-[5.375rem] z-[10] right-0 absolute wp-post-image right-[-10rem] group-hover:right-0 gradient-mask-fade' ),
				)
			);
			$item_markup .= '<div class="flex w-full justify-center group relative gap-2 flex-row-reverse overflow-hidden">' . $featured_image . '<div class="flex flex-col leading-[1rem] p-2 group">';
		}
		else
			$item_markup .= '<div class="flex flex-col w-full justify-center p-2 pr-2 md:pr-4 gap-2"><div class="flex flex-col leading-[1rem]">';

		$item_markup .= '<a class="wp-block-latest-posts__post-title text-normal md:text-lg uppercase !font-sans text-left font-bold !leading-none md:leading-relaxed z-[99] group-hover:[text-shadow:1px_1px_0_white,-1px_1px_0_white,1px_-1px_0_white,-1px_-1px_0_white,0_2px_0_white,2px_0_0_white,-2px_0_0_white,0_-2px_0_white]" href="' . esc_url( $post_link ) . '">' . $title . '</a>';

		$item_markup .= '<div class="flex justify-between text-xs md:text-base">';
		$item_markup .= "<div class='flex flex-col sm:flex-row items-start sm:items-center gap-0 sm:gap-2'>";

		if ( isset( $attributes['displayPostDate'] ) && $attributes['displayPostDate'] ) {
			$item_markup .= sprintf(
				'<time datetime="%1$s" class="wp-block-latest-posts__post-date select-none">%2$s</time>',
				esc_attr( get_the_date( 'c', $post ) ),
				get_the_date( '', $post )
			);
		}

		if (!$is_grid_layout) {
			$meta  = get_post_meta( $post->ID, 'pm_metrics', true );
			$views = intval( $meta['views'] ?? 0 );
			$item_markup .= '<div class="flex items-start sm:items-center gap-1"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eyeglasses" viewBox="0 0 16 16">
	  <path d="M4 6a2 2 0 1 1 0 4 2 2 0 0 1 0-4m2.625.547a3 3 0 0 0-5.584.953H.5a.5.5 0 0 0 0 1h.541A3 3 0 0 0 7 8a1 1 0 0 1 2 0 3 3 0 0 0 5.959.5h.541a.5.5 0 0 0 0-1h-.541a3 3 0 0 0-5.584-.953A2 2 0 0 0 8 6c-.532 0-1.016.208-1.375.547M14 8a2 2 0 1 1-4 0 2 2 0 0 1 4 0"/>
	</svg><span class="text-xs">' . $views . '</span></div>';
		}

		$item_markup .= '</div>';

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

		$item_markup .= '</div></div>';

		if ( isset( $attributes['displayPostContent'] ) && $attributes['displayPostContent']
		     && isset( $attributes['displayPostContentRadio'] ) && 'excerpt' === $attributes['displayPostContentRadio'] ) {
			$trimmed_excerpt = get_the_excerpt( $post );
			if ( str_ends_with( $trimmed_excerpt, ' [&hellip;]' ) ) {
				$excerpt_length = (int) apply_filters( 'excerpt_length', $block_core_latest_posts_excerpt_length );
				if ( $excerpt_length <= $block_core_latest_posts_excerpt_length ) {
					$trimmed_excerpt  = substr( $trimmed_excerpt, 0, -11 );
					$read_more = '<a href="' . esc_url( $post_link ) . '" rel="noopener noreferrer">' . esc_html__( 'Read more' ) . '<span class="screen-reader-text">: ' . esc_html( $title ) . '</span></a>';
					$trimmed_excerpt .= '… ' . $read_more;
				}
			}
			if ( post_password_required( $post ) ) {
				$trimmed_excerpt = __( 'This content is password protected.' );
			}

			$excerpt_base = 'wp-block-latest-posts__post-excerpt !m-0 text-xs md:text-base !leading-none md:!leading-5 entry-content';
			if ($is_grid_layout && $is_right ) {
				$excerpt_visibility = 'hidden';
			} else {
				$excerpt_visibility = 'hidden md:block';
			}
			$item_markup .= '<div class="' . esc_attr( $excerpt_base . ' ' . $excerpt_visibility ) . '">' . $trimmed_excerpt . '</div>';
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

	$list_items_markup = '<div id="left-posts-list" class="flex flex-col gap-2 md:gap-3">';

	$right_list_class = 'flex flex-col gap-2 md:gap-3';
	$right_list_id = 'right-posts-list';
	if ( isset( $block['attrs'] ) && ! empty( $block['attrs']['uniqueID'] ) ) {
		$right_list_id .= $block['attrs']['uniqueID'];
	}

	$total = count( $recent_posts );
	$maxCount = 2;
	$count = 0;
	foreach ( $recent_posts as $post ) {
		if ( $maxCount === $count ) {
			$list_items_markup .= '</div><div id="' . esc_attr( $right_list_id ) . '" class="' . esc_attr( $right_list_class ) . '">';
		}

		$img_wrapper_first = '<div class="left-images overflow-hidden rounded-md shrink-0 w-[9rem] h-full min-h-[60rem] md:w-[12rem] md:h-[9rem] lg:w-[15rem] lg:h-[12rem]">%s</div>';

		$img_wrapper_later_grid = '<div class="overflow-hidden shrink-0 w-[9rem] h-full min-h-[60rem] md:w-[12rem] md:h-[9rem] lg:w-[15rem] lg:h-[12rem] block md:hidden">%s</div>';

		$img_wrapper_later_list = '<div class="overflow-hidden rounded-md shrink-0 w-[9rem] h-full min-h-[60rem] md:w-[12rem] md:h-[9rem] lg:w-[15rem] lg:h-[12rem]">%s</div>';

		$is_first_group = ( $count < $maxCount );


		$base_container = 'relative md:gap-5 w-full h-fit items-stretch rounded-md overflow-hidden bg-white my-auto opacity-0 reveal-on-scroll-300 ';

		if ( $count < $maxCount && $is_grid_layout) {
			$base_container .= 'flex flex-row md:flex-col h-full ';
		}



		if ( $is_grid_layout ) {
			if ( $is_first_group ) {
				$img_wrapper_first = '<div class="overflow-hidden rounded-md w-[9rem] shrink-0 md:shrink-0 md:w-full h-full md:h-[9rem] lg:h-[12rem]">%s</div>';
				$container_classes = $base_container . 'flex items-center hover:border-[#1f5d2b] hover:shadow-lg ';
				$list_items_markup .= $render_item(
					$post,
					$attributes,
					$container_classes,
					$img_wrapper_first,
					true
				);

			} else {
				$container_classes = $base_container . 'flex items-center p-0 ';
				$list_items_markup .= $render_item(
					$post,
					$attributes,
					$container_classes,
					($attributes['displayFeaturedImage'] ? $img_wrapper_first : $img_wrapper_later_grid),
					true,
					true
				);

				if ( $count < $total - 1 && isset( $attributes['postLayout'] ) && 'grid' === $attributes['postLayout'] ) {
					$list_items_markup .= '<div class="border-b-2 mx-4 md:block hidden border-[#a2b917]"></div>';
				}
			}
		} else {
			if ( ! $is_first_group ) {
				$container_classes = $base_container . 'flex border hover:border-[#1f5d2b] md:items-center ';
				$list_items_markup .= $render_item(
					$post,
					$attributes,
					$container_classes . 'hover:shadow-lg p-0',
					$img_wrapper_later_list,
					true,
					true
				);
			} else {
				$container_classes = $base_container . 'flex border hover:border-[#1f5d2b] md:items-center ';
				$list_items_markup .= $render_item(
					$post,
					$attributes,
					$container_classes . 'hover:shadow-lg',
					$img_wrapper_first,
					true
				);
			}
		}

		$count++;
	}

	$list_items_markup .= '</div>';

	remove_filter( 'excerpt_length', 'block_core_latest_posts_get_excerpt_length', 20 );

	if ( $is_grid_layout ) {
		$classes = array( 'wp-block-latest-posts__list','grid','grid-cols-1','md:grid-cols-2','gap-2','md:gap-6' );
	} else {
		$classes = array( 'wp-block-latest-posts__list','flex','flex-col','grid','grid-cols-1','gap-2','md:gap-3');
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

	return '<div id="posts-list-container" ' . $wrapper_attributes . '>' . $list_items_markup . '</div>';
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
