<?php
/**
 * GWT_Walker_Nav_Menu
 *
 * A configurable, accessible WordPress nav menu walker built for
 * Tailwind CSS. Supports multi-level dropdown menus with smooth
 * hover transitions, keyboard navigation, and ARIA attributes.
 *
 * @package GWT
 * @version 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'GWT_Walker_Nav_Menu' ) ) :

	class GWT_Walker_Nav_Menu extends Walker_Nav_Menu {

		/**
		 * Default configuration.
		 * Override any key via the constructor's $custom_config parameter.
		 *
		 * @var array<string, string>
		 */
		protected array $config = [

			// ── Top-level items (depth 0) ─────────────────────────────────
			'top_li_base'          => 'relative flex items-center rounded-md transition-colors duration-200',
			'top_bg_default'       => 'bg-transparent',
			'top_bg_hover'         => 'hover:bg-[#a2b917]',
			'top_bg_active'        => 'bg-[#a2b917]',
			'top_bg_parent'        => 'bg-[#1a472a]/80',

			'top_link_base'        => 'flex items-center justify-between w-full px-4 py-2.5 whitespace-nowrap transition-all duration-150',
			'top_font_family'      => 'font-sans',
			'top_font_size'        => 'text-sm',
			'top_font_weight'      => 'font-medium',
			'top_text_color'       => 'text-[#fefcf8]',
			'top_text_hover'       => 'hover:text-white',
			'top_text_active'      => 'text-white',
			'top_arrow_color'      => 'text-[#fbbf24]',

			// ── Submenu items (depth 1+) ──────────────────────────────────
			'sub_li_base'          => 'transition-colors duration-150',
			'sub_bg_default'       => 'bg-transparent',
			'sub_bg_hover'         => 'hover:bg-[#a2b917]',
			'sub_bg_active'        => 'bg-[#f0fdf4]',

			'sub_link_base'        => 'flex items-center justify-between w-full px-4 py-2.5 whitespace-nowrap rounded-md mx-1 transition-all duration-150',
			'sub_font_family'      => 'font-sans',
			'sub_font_size'        => 'text-sm',
			'sub_font_weight'      => 'font-medium',
			'sub_text_color'       => 'text-[#166534]',
			'sub_text_hover'       => 'hover:text-[#14532d]',
			'sub_text_active'      => 'font-semibold',
			'sub_arrow_color'      => 'text-[#86efac]',
			'sub_arrow_hover'      => 'group-hover:text-[#166534]',

			// ── Submenu container ─────────────────────────────────────────
			'submenu_bg'           => 'bg-white',
			'submenu_border'       => 'border border-gray-100',
			'submenu_shadow'       => 'shadow-xl',
			'submenu_rounded'      => 'rounded-lg',
			'submenu_padding'      => 'py-1.5',
			'submenu_min_width'    => 'min-w-[220px]',
		];

		/**
		 * @param array<string, string> $custom_config Key-value pairs to override defaults.
		 */
		public function __construct( array $custom_config = [] ) {
			$this->config = array_merge( $this->config, $custom_config );
		}

		// ─────────────────────────────────────────────────────────────────────
		// Walker overrides
		// ─────────────────────────────────────────────────────────────────────

		/**
		 * Outputs the opening tag for a submenu <ul>.
		 *
		 * @param string   $output  Used to append additional content (passed by reference).
		 * @param int      $depth   Depth of menu item.
		 * @param stdClass $args    An object of wp_nav_menu() arguments.
		 */
		public function start_lvl( &$output, $depth = 0, $args = null ): void {
			$indent = str_repeat( "\t", $depth );

			if ( $depth === 0 ) {
				// Drops down below the parent item.
				$position  = 'left-0 top-full mt-1';
				$bridge    = "before:absolute before:top-[-8px] before:inset-x-0 before:h-2 before:content-['']";
				$animation = 'translate-y-2 group-hover:translate-y-0';
			} else {
				// Flies out to the right of the parent item.
				$position  = 'left-full top-0 ml-1';
				$bridge    = "before:absolute before:left-[-12px] before:inset-y-0 before:w-3 before:content-['']";
				$animation = 'translate-x-2 group-hover:translate-x-0';
			}

			$visibility = 'opacity-0 invisible group-hover:opacity-100 group-hover:visible';
			$transition  = 'transition-all duration-200 ease-out';

			$container = $this->classes(
				'submenu_bg',
				'submenu_border',
				'submenu_shadow',
				'submenu_rounded',
				'submenu_padding',
				'submenu_min_width'
			);

			$output .= "\n{$indent}<ul "
			           . 'role="menu" '
			           . "class=\"absolute {$position} {$bridge} {$visibility} {$transition} {$animation} z-50 list-none {$container}\">"
			           . "\n";
		}

		/**
		 * Outputs the closing </ul> tag for a submenu.
		 *
		 * @param string   $output  Used to append additional content (passed by reference).
		 * @param int      $depth   Depth of menu item.
		 * @param stdClass $args    An object of wp_nav_menu() arguments.
		 */
		public function end_lvl( &$output, $depth = 0, $args = null ): void {
			$output .= str_repeat( "\t", $depth ) . "</ul>\n";
		}

		/**
		 * Outputs the opening tag for a menu item, including its anchor.
		 *
		 * @param string   $output  Used to append additional content (passed by reference).
		 * @param WP_Post  $item    Menu item data object.
		 * @param int      $depth   Depth of menu item.
		 * @param stdClass $args    An object of wp_nav_menu() arguments.
		 * @param int      $id      Current item ID.
		 */
		public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ): void {
			$args         = is_array( $args ) ? (object) $args : $args;
			$has_children = ! empty( $args->has_children );
			$indent       = str_repeat( "\t", $depth );

			$item_classes  = (array) $item->classes;
			$is_current    = $this->item_has_class( $item_classes, 'current-menu-item', 'current_page_item' );
			$is_parent     = $this->item_has_class( $item_classes, 'current-menu-parent', 'current_page_parent' );
			$is_ancestor   = $this->item_has_class( $item_classes, 'current-menu-ancestor' );

			// ── <li> ──────────────────────────────────────────────────────
			$li_classes = ['relative'];

			if ( $has_children ) {
				$li_classes[] = 'group';
			}

			if ( $depth === 0 ) {
				$li_classes[] = $this->config['top_li_base'];
				$li_classes[] = $is_current
					? $this->config['top_bg_active']
					: $this->config['top_bg_default'] . ' ' . $this->config['top_bg_hover'];

				if ( $is_parent || $is_ancestor ) {
					$li_classes[] = $this->config['top_bg_parent'];
				}
			} else {
				$li_classes[] = $this->config['sub_li_base'];
				$li_classes[] = $is_current
					? $this->config['sub_bg_active']
					: $this->config['sub_bg_default'] . ' ' . $this->config['sub_bg_hover'];
			}

			$li_attr = sprintf(
				' class="%s" role="none"',
				esc_attr( $this->flatten( $li_classes ) )
			);

			$output .= "{$indent}<li{$li_attr}>";

			// ── <a> ───────────────────────────────────────────────────────
			if ( $depth === 0 ) {
				$link_classes = [
					$this->config['top_link_base'],
					$this->config['top_font_family'],
					$this->config['top_font_size'],
					$this->config['top_font_weight'],
					$this->config['top_text_color'],
					$this->config['top_text_hover'],
					$is_current ? $this->config['top_text_active'] : '',
				];
			} else {
				$link_classes = [
					$this->config['sub_link_base'],
					$this->config['sub_font_family'],
					$this->config['sub_font_size'],
					$this->config['sub_font_weight'],
					$this->config['sub_text_color'],
					$this->config['sub_text_hover'],
					$is_current ? $this->config['sub_text_active'] . ' ' . $this->config['sub_bg_active'] : '',
				];
			}

			$title = apply_filters( 'the_title', $item->title, $item->ID );

			$atts = $this->build_link_atts( $item, $link_classes, $is_current, $has_children );

			$output .= '<a' . $this->atts_to_string( $atts ) . '>'
			           . '<span>' . esc_html( $title ) . '</span>'
			           . ( $has_children ? $this->render_arrow( $depth ) : '' )
			           . '</a>';
		}

		/**
		 * Outputs the closing </li> tag for a menu item.
		 *
		 * @param string   $output  Used to append additional content (passed by reference).
		 * @param WP_Post  $item    Menu item data object.
		 * @param int      $depth   Depth of menu item.
		 * @param stdClass $args    An object of wp_nav_menu() arguments.
		 */
		public function end_el( &$output, $item, $depth = 0, $args = null ): void {
			$output .= "</li>\n";
		}

		/**
		 * Traverses elements to create list from elements.
		 * Overridden to properly set `has_children` on the args object.
		 *
		 * @param object $element           Data object.
		 * @param array  $children_elements List of elements to continue traversing.
		 * @param int    $max_depth         Max depth to traverse.
		 * @param int    $depth             Depth of current element.
		 * @param array  $args              An array of arguments.
		 * @param string $output            Used to append additional content (passed by reference).
		 */
		public function display_element( $element, &$children_elements, $max_depth, $depth = 0, $args = [], &$output = '' ): void {
			if ( ! $element ) {
				return;
			}

			$id_field = $this->db_fields['id'];

			// $args is passed as an array where index 0 holds the args object.
			if ( isset( $args[0] ) && is_object( $args[0] ) ) {
				$args[0]->has_children = ! empty( $children_elements[ $element->$id_field ] );
			}

			parent::display_element( $element, $children_elements, $max_depth, $depth, $args, $output );
		}

		// ─────────────────────────────────────────────────────────────────────
		// Private helpers
		// ─────────────────────────────────────────────────────────────────────

		/**
		 * Retrieve one or more config values joined as a class string.
		 *
		 * @param string ...$keys Config keys to look up.
		 * @return string
		 */
		private function classes( string ...$keys ): string {
			$result = [];
			foreach ( $keys as $key ) {
				if ( ! empty( $this->config[ $key ] ) ) {
					$result[] = $this->config[ $key ];
				}
			}
			return implode( ' ', $result );
		}

		/**
		 * Flatten an array of class strings, removing empty values.
		 *
		 * @param array<string> $classes
		 * @return string
		 */
		private function flatten( array $classes ): string {
			return implode( ' ', array_filter( array_map( 'trim', $classes ) ) );
		}

		/**
		 * Check whether a menu item carries any of the given CSS classes.
		 *
		 * @param array<string> $item_classes Classes already on the item.
		 * @param string        ...$needles   Classes to look for.
		 * @return bool
		 */
		private function item_has_class( array $item_classes, string ...$needles ): bool {
			foreach ( $needles as $needle ) {
				if ( in_array( $needle, $item_classes, true ) ) {
					return true;
				}
			}
			return false;
		}

		/**
		 * Build the anchor element's attribute array.
		 *
		 * @param WP_Post       $item         Menu item.
		 * @param array<string> $link_classes CSS classes for the link.
		 * @param bool          $is_current   Whether this is the active page.
		 * @param bool          $has_children Whether a submenu exists.
		 * @return array<string, string>
		 */
		private function build_link_atts( WP_Post $item, array $link_classes, bool $is_current, bool $has_children ): array {
			$atts = [
				'href'  => ! empty( $item->url ) ? $item->url : '#',
				'class' => $this->flatten( $link_classes ),
				'role'  => 'menuitem',
			];

			if ( ! empty( $item->attr_title ) ) {
				$atts['title'] = $item->attr_title;
			}

			if ( ! empty( $item->target ) ) {
				$atts['target'] = $item->target;
				// Enforce safe external link behaviour.
				if ( '_blank' === $item->target ) {
					$atts['rel'] = trim( ( $item->xfn ?? '' ) . ' noopener noreferrer' );
				}
			} elseif ( ! empty( $item->xfn ) ) {
				$atts['rel'] = $item->xfn;
			}

			if ( $is_current ) {
				$atts['aria-current'] = 'page';
			}

			if ( $has_children ) {
				$atts['aria-haspopup'] = 'true';
				$atts['aria-expanded'] = 'false'; // toggled by JS if needed
			}

			return $atts;
		}

		/**
		 * Convert an attribute array to an HTML attribute string.
		 *
		 * @param array<string, string> $atts
		 * @return string
		 */
		private function atts_to_string( array $atts ): string {
			$html = '';
			foreach ( $atts as $attr => $value ) {
				if ( $value !== '' && $value !== null ) {
					$html .= ' ' . $attr . '="' . esc_attr( $value ) . '"';
				}
			}
			return $html;
		}

		/**
		 * Render the appropriate chevron arrow SVG for a parent menu item.
		 *
		 * @param int $depth Current menu depth.
		 * @return string
		 */
		private function render_arrow( int $depth ): string {
			if ( $depth === 0 ) {
				// Down chevron — rotates 180° on hover.
				return sprintf(
					'<svg class="ml-2 h-4 w-4 flex-shrink-0 %s group-hover:rotate-180 transition-transform duration-200" '
					. 'xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" '
					. 'stroke-width="2.5" stroke="currentColor" aria-hidden="true">'
					. '<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>'
					. '</svg>',
					esc_attr( $this->config['top_arrow_color'] )
				);
			}

			// Right chevron — nudges right on hover.
			return sprintf(
				'<svg class="ml-2 h-4 w-4 flex-shrink-0 %s %s group-hover:translate-x-0.5 transition-all duration-200" '
				. 'xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" '
				. 'stroke-width="2" stroke="currentColor" aria-hidden="true">'
				. '<path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>'
				. '</svg>',
				esc_attr( $this->config['sub_arrow_color'] ),
				esc_attr( $this->config['sub_arrow_hover'] )
			);
		}
	}

endif;