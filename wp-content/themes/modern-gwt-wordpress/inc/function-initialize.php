<?php
/**
 * Set the content width based on the theme's design and stylesheet.
 */
if ( ! isset( $content_width ) )
	$content_width = 640; /* pixels */

if ( ! function_exists( 'gwt_wp_setup' ) ) :
/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which runs
 * before the init hook. The init hook is too late for some features, such as indicating
 * support post thumbnails.
 */
function gwt_wp_setup() {
	/**
	 * Make theme available for translation
	 * Translations can be filed in the /languages/ directory
	 * If you're building a theme based on gwt_wp, use a find and replace
	 * to change 'gwt_wp' to the name of your theme in all the template files
	 */
	load_theme_textdomain( 'gwt_wp', get_template_directory() . '/languages' );

	/**
	 * Add default posts and comments RSS feed links to head
	 */
	add_theme_support( 'automatic-feed-links' );

	/*
	 * Let WordPress manage the document title.
	 * By adding theme support, we declare that this theme does not use a
	 * hard-coded <title> tag in the document head, and expect WordPress to
	 * provide it for us.
	 */
	// add_theme_support( 'title-tag' );

	/*
	 * Enable support for custom logo.
	 *
	 */
	add_theme_support( 'custom-logo', array(
		'height'      => 240,
		'width'       => 240,
		'flex-height' => true,
	) );

	/*
	 * Enable support for Post Thumbnails on posts and pages.
	 *
	 * @link http://codex.wordpress.org/Function_Reference/add_theme_support#Post_Thumbnails
	 */
	add_theme_support( 'post-thumbnails' );
	// set_post_thumbnail_size( 1200, 9999 );

	/*
	 * Switch default core markup for search form, comment form, and comments
	 * to output valid HTML5.
	 */
	add_theme_support( 'html5', array(
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
	) );

	/*
	 * Enable support for Post Formats.
	 *
	 * See: https://codex.wordpress.org/Post_Formats
	 */
	/*
	add_theme_support( 'post-formats', array(
		'aside',
		'image',
		'video',
		'quote',
		'link',
		'gallery',
		'status',
		'audio',
		'chat',
	) );
	*/

	// Indicate widget sidebars can use selective refresh in the Customizer.
	add_theme_support( 'customize-selective-refresh-widgets' );

	// make clickable content
	apply_filters('the_content','make_clickable');

	// changes
	// add_theme_support('custom-background', $args );
	add_editor_style();

	class Off_Canvass_Menu extends Walker_Nav_Menu {

		/**
		 * Start a sub-menu <ul>.
		 * Hidden by default — toggled open via JS on the parent chevron button.
		 */
		public function start_lvl( &$output, $depth = 0, $args = null ) {
			$indent  = str_repeat( "\t", $depth );
			$output .= "\n{$indent}<ul class=\"gwt-mobile-submenu hidden flex-col gap-0.5 pl-3 pt-1 pb-1\">\n";
		}

		/**
		 * End a sub-menu <ul>.
		 */
		public function end_lvl( &$output, $depth = 0, $args = null ) {
			$indent  = str_repeat( "\t", $depth );
			$output .= "{$indent}</ul>\n";
		}

		/**
		 * Output a single menu item <li> + <a>.
		 *
		 * Top-level items with children get a separate chevron <button> that
		 * toggles the sub-menu — keeping the <a> href intact for navigation.
		 * Sub-items get a small green dot indicator via a CSS pseudo-element.
		 */
		public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
			$has_children = ! empty( $args->has_children );
			$indent       = str_repeat( "\t", $depth );

			// ── <li> ────────────────────────────────────────────────────────
			$li_classes   = 'gwt-mobile-menu-item menu-item-' . $item->ID;
			$li_classes  .= $has_children ? ' gwt-has-children' : '';

			$extra_classes = empty( $item->classes ) ? [] : (array) $item->classes;
			$extra_classes = apply_filters( 'nav_menu_css_class', array_filter( $extra_classes ), $item, $args );
			if ( $extra_classes ) {
				$li_classes .= ' ' . implode( ' ', $extra_classes );
			}

			$li_id = apply_filters( 'nav_menu_item_id', 'menu-item-' . $item->ID, $item, $args );
			$li_id = $li_id ? ' id="' . esc_attr( $li_id ) . '"' : '';

			$output .= "{$indent}<li{$li_id} class=\"" . esc_attr( $li_classes ) . "\">\n";

			// ── link wrapper (flex row so chevron sits beside the link) ─────
			if ( $has_children && $depth === 0 ) {
				$output .= "{$indent}\t<div class=\"gwt-mobile-item-row flex items-center gap-1\">\n";
			}

			// ── <a> ─────────────────────────────────────────────────────────
			$atts = [
				'title'  => ! empty( $item->attr_title ) ? $item->attr_title : '',
				'target' => ! empty( $item->target )     ? $item->target     : '',
				'rel'    => ! empty( $item->xfn )        ? $item->xfn        : '',
				'href'   => ! empty( $item->url )        ? $item->url        : '',
			];

			if ( $depth === 0 ) {
				$link_class = 'gwt-mobile-menu-link flex-1 flex items-center rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-800 transition-colors duration-150 hover:bg-[#1f5d2b]/8 hover:text-[#1f5d2b] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#1f5d2b]/30';
			} else {
				$link_class = 'gwt-mobile-menu-link gwt-mobile-sub-link flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-slate-600 transition-colors duration-150 hover:bg-[#a2b917]/10 hover:text-[#1f5d2b] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#a2b917]/30';
			}

			$attr_str = ' class="' . esc_attr( $link_class ) . '"';
			foreach ( $atts as $attr => $value ) {
				if ( ! empty( $value ) ) {
					$attr_str .= ' ' . $attr . '="' . esc_attr( $value ) . '"';
				}
			}

			$before      = is_object( $args ) ? $args->before     : '';
			$link_before = is_object( $args ) ? $args->link_before : '';
			$link_after  = is_object( $args ) ? $args->link_after  : '';
			$after       = is_object( $args ) ? $args->after       : '';

			$title = apply_filters( 'the_title', $item->title, $item->ID );

			$item_output  = $before;
			$item_output .= '<a' . $attr_str . '>';

			// Green dot for sub-items
			if ( $depth > 0 ) {
				$item_output .= '<span class="gwt-mobile-dot shrink-0 inline-block w-1.5 h-1.5 rounded-full bg-[#a2b917]" aria-hidden="true"></span>';
			}

			$item_output .= $link_before . esc_html( $title ) . $link_after;
			$item_output .= '</a>';
			$item_output .= $after;

			// ── Chevron toggle button (top-level parents only) ────────────
			if ( $has_children && $depth === 0 ) {
				$item_output .= '<button type="button"
                    class="gwt-mobile-submenu-toggle shrink-0 flex items-center justify-center w-8 h-8 rounded-lg text-slate-400 transition-colors duration-150 hover:bg-[#1f5d2b]/8 hover:text-[#1f5d2b] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#1f5d2b]/30"
                    aria-expanded="false"
                    aria-label="Toggle submenu for ' . esc_attr( $title ) . '">
                    <svg class="gwt-chevron w-4 h-4 transition-transform duration-200" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                    </svg>
                </button>';
			}

			$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );

			// Close the flex wrapper for top-level parents
			if ( $has_children && $depth === 0 ) {
				$output .= "\n{$indent}\t</div>\n";
			}
		}

		/**
		 * End the <li> element.
		 */
		public function end_el( &$output, $item, $depth = 0, $args = null ) {
			$indent  = str_repeat( "\t", $depth );
			$output .= "{$indent}</li>\n";
		}

		/**
		 * Set has_children on the args object before delegating to parent.
		 */
		public function display_element( $element, &$children_elements, $max_depth, $depth = 0, $args = [], &$output = '' ) {
			if ( ! $element ) return;

			$id_field = $this->db_fields['id'];

			if ( isset( $args[0] ) && is_object( $args[0] ) ) {
				$args[0]->has_children = ! empty( $children_elements[ $element->$id_field ] );
			}

			parent::display_element( $element, $children_elements, $max_depth, $depth, $args, $output );
		}
	}
	
	class Topbar_Nav_Menu extends Walker_Nav_Menu
	{
		function start_lvl( &$output, $depth = 0, $args = array() ) {
			$indent = str_repeat("\t", $depth);
			$output .= "\n$indent<ul class=\"dropdown vertical menu\">\n";
		}

		function end_lvl( &$output, $depth = 0, $args = array() ) {
			$indent = str_repeat("\t", $depth);
			$output .= "$indent</ul>\n";
		}

		function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
			$indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';
			$class_names = $value = '';
			$classes = empty( $item->classes ) ? array() : (array) $item->classes;

			$classes[] = 'select-none menu-item-' . $item->ID;
			$class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args ) );
			$class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';
			
			//added for mega menu
			$mega_menu_id = apply_filters( 'the_title', $item->title, $item->ID );
			$mega_menu_id = strtolower($mega_menu_id);
			$mega_menu_id = str_replace(' ', '_', $mega_menu_id);
			
			$id = apply_filters( 'nav_menu_item_id', 'menu-item-'. $item->ID, $item, $args );
			$id = $id ? ' id="' . esc_attr( $id ) . '"' : '';
			$id = strpos($class_names,'has-megamenu') ? ' id="'. $mega_menu_id .'-menu"' : $id; //added for mega menu

			$output .= $indent . '<li' . $id . $value . $class_names .'>';

			$attributes  = ! empty( $item->attr_title ) ? ' title="'  . esc_attr( $item->attr_title ) .'"' : '';
			$attributes .= ! empty( $item->target )     ? ' target="' . esc_attr( $item->target     ) .'"' : '';
			$attributes .= ! empty( $item->xfn )        ? ' rel="'    . esc_attr( $item->xfn        ) .'"' : '';
			$attributes .= ! empty( $item->url )        ? ' href="'   . esc_attr( $item->url        ) .'"' : '';

			$item_output = $args->before;
			$item_output .= '<a'. $attributes .'>';
			$item_output .= $args->link_before . apply_filters( 'the_title', $item->title, $item->ID ) . $args->link_after;
			$item_output .= '</a>';
			$item_output .= $args->after;
		  
			$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
		}

		function end_el( &$output, $item, $depth = 0, $args = array() ) {
			$output .= "</li>\n";
		}

		function display_element($element, &$children_elements, $max_depth, $depth=0, $args=[], &$output=null)
		{
			$id_field = $this->db_fields['id'];
			if (!empty($children_elements[$element->$id_field])) {
				$element->classes[] = 'has-dropdown';
			}
			Walker_Nav_Menu::display_element($element, $children_elements, $max_depth, $depth, $args, $output);
		}

	}

}
endif; // gwt_wp_setup
add_action( 'after_setup_theme', 'gwt_wp_setup' );