<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'GWT_Walker_Nav_Menu' ) ) :

	class GWT_Walker_Nav_Menu extends Walker_Nav_Menu {

		/**
		 * Configuration variables - Edit these to customize appearance
		 */
		private $config = [
			// Top level (depth 0) styling
			'top_bg_default'           => 'bg-transparent',
			'top_bg_hover'             => 'hover:bg-[#a2b917]',
			'top_bg_active'            => 'bg-[#a2b917]',
			'top_bg_parent'            => 'bg-[#1a472a]/80',
			'top_text_color'           => 'text-[#fefcf8]',
			'top_text_hover'           => 'hover:text-white',
			'top_font_family'          => 'font-sans',
			'top_font_size'            => 'text-sm',
			'top_font_weight'          => 'font-medium',
			'top_arrow_color'          => 'text-[#fbbf24]',

			// Submenu (depth 1+) styling
			'sub_bg_default'           => 'bg-transparent',
			'sub_bg_hover'             => 'hover:bg-[#a2b917]',
			'sub_bg_active'            => 'bg-[#f0fdf4]',
			'sub_text_color'           => 'text-[#166534]',
			'sub_text_hover'           => 'hover:text-[#14532d]',
			'sub_font_family'          => 'font-sans',
			'sub_font_size'            => 'text-sm',
			'sub_font_weight'          => 'font-medium',
			'sub_arrow_color'          => 'text-[#86efac]',
			'sub_arrow_hover'          => 'group-hover:text-[#166534]',

			// Submenu container styling
			'submenu_shadow'           => 'shadow-xl',
			'submenu_border'           => 'border-gray-100',
			'submenu_bg'               => 'bg-white',
		];

		/**
		 * Allow config override via constructor
		 */
		public function __construct( $custom_config = [] ) {
			$this->config = array_merge( $this->config, $custom_config );
		}

		/**
		 * Helper to build class string from config keys
		 */
		private function get_classes( $keys ) {
			$classes = [];
			foreach ( $keys as $key ) {
				if ( isset( $this->config[$key] ) && ! empty( $this->config[$key] ) ) {
					$classes[] = $this->config[$key];
				}
			}
			return implode( ' ', $classes );
		}

		/**
		 * Start the submenu level.
		 */
		public function start_lvl( &$output, $depth = 0, $args = null ) {
			$indent = str_repeat("\t", $depth);
			$is_top = ($depth === 0);

			if ( $is_top ) {
				$pos_class = "left-0 top-full mt-1";
				$bridge = "before:absolute before:top-[-8px] before:left-0 before:right-0 before:h-[8px] before:content-[''] before:z-[-1]";
				$anim = "opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 ease-out transform origin-top-left group-hover:translate-y-0 translate-y-2";
			} else {
				$pos_class = "left-full top-0 ml-1";
				$bridge = "before:absolute before:left-[-12px] before:top-0 before:bottom-0 before:w-[12px] before:content-[''] before:z-[-1]";
				$anim = "opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 ease-out transform origin-top-left group-hover:translate-x-0 translate-x-2";
			}

			$container_classes = $this->get_classes( ['submenu_bg', 'submenu_shadow'] );
			$border_class = $this->config['submenu_border'];

			$output .= "\n{$indent}<ul class=\"absolute {$pos_class} {$bridge} {$anim} list-none flex-col {$container_classes} z-50 py-1.5 rounded-lg min-w-[220px] border {$border_class}\">\n";
		}

		/**
		 * End the submenu level.
		 */
		public function end_lvl( &$output, $depth = 0, $args = null ) {
			$indent = str_repeat("\t", $depth);
			$output .= "{$indent}</ul>\n";
		}

		/**
		 * Start each menu item.
		 */
		public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
			$args = is_array( $args ) ? (object) $args : $args;
			$has_children = ! empty( $args->has_children );

			$indent = str_repeat("\t", $depth);

			$is_current = in_array( 'current-menu-item', (array) $item->classes, true )
			              || in_array( 'current_page_item', (array) $item->classes, true );
			$is_parent = in_array( 'current-menu-parent', (array) $item->classes, true )
			             || in_array( 'current_page_parent', (array) $item->classes, true );

			// Build LI classes
			$li_classes = ['relative'];
			if ( $has_children ) {
				$li_classes[] = 'group';
			}

			if ( $depth === 0 ) {
				$li_classes[] = 'flex items-center rounded-md transition-colors duration-200';
				$li_classes[] = $is_current ? $this->config['top_bg_active'] : $this->config['top_bg_default'] . ' ' . $this->config['top_bg_hover'];
				if ( $is_parent ) $li_classes[] = $this->config['top_bg_parent'];
			} else {
				$li_classes[] = 'transition-colors duration-150';
				$li_classes[] = $is_current ? $this->config['sub_bg_active'] : $this->config['sub_bg_default'] . ' ' . $this->config['sub_bg_hover'];
			}

			$li_class = implode( ' ', array_filter( $li_classes ) );

			// Build Link classes
			$link_base = 'block w-full px-4 py-2.5 whitespace-nowrap flex items-center justify-between transition-all duration-150';

			if ( $depth === 0 ) {
				$link_classes = array_filter([
					$link_base,
					$this->config['top_font_family'],
					$this->config['top_font_size'],
					$this->config['top_font_weight'],
					$this->config['top_text_color'],
					$this->config['top_text_hover'],
					$is_current ? 'text-white' : '',
				]);
			} else {
				$link_classes = array_filter([
					$link_base,
					$this->config['sub_font_family'],
					$this->config['sub_font_size'],
					$this->config['sub_font_weight'],
					$this->config['sub_text_color'],
					$this->config['sub_text_hover'],
					'rounded-md mx-1',
					$is_current ? 'font-semibold ' . $this->config['sub_bg_active'] : '',
				]);
			}

			$link_class = implode( ' ', $link_classes );

			$output .= "{$indent}<li class=\"{$li_class}\">";

			$atts = [
				'title'  => ! empty( $item->attr_title ) ? $item->attr_title : '',
				'target' => ! empty( $item->target ) ? $item->target : '',
				'rel'    => ! empty( $item->xfn ) ? $item->xfn : '',
				'href'   => ! empty( $item->url ) ? $item->url : '',
				'class'  => $link_class,
				'aria-current' => $is_current ? 'page' : '',
			];

			$attr_str = '';
			foreach ( $atts as $attr => $value ) {
				if ( ! empty( $value ) ) {
					$attr_str .= ' ' . $attr . '="' . esc_attr( $value ) . '"';
				}
			}

			$title = apply_filters( 'the_title', $item->title, $item->ID );
			$output .= '<a' . $attr_str . '><span>' . esc_html( $title ) . '</span>';

			// Arrows
			if ( $has_children ) {
				if ( $depth === 0 ) {
					$arrow_color = $this->config['top_arrow_color'];
					$output .= ' <svg class="ml-2 h-4 w-4 ' . $arrow_color . ' group-hover:rotate-180 transition-transform duration-200" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>';
				} else {
					$arrow_color = $this->config['sub_arrow_color'];
					$arrow_hover = $this->config['sub_arrow_hover'];
					$output .= ' <svg class="ml-2 h-4 w-4 ' . $arrow_color . ' ' . $arrow_hover . ' group-hover:translate-x-0.5 transition-all duration-200" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>';
				}
			}

			$output .= '</a>';
		}

		/**
		 * End each menu item.
		 */
		public function end_el( &$output, $item, $depth = 0, $args = null ) {
			$output .= "</li>\n";
		}

		/**
		 * Properly set has_children.
		 */
		public function display_element( $element, &$children_elements, $max_depth, $depth = 0, $args = [], &$output = '' ) {
			if ( ! $element ) return;

			$id_field = $this->db_fields['id'];
			if ( is_array( $args ) && isset( $args[0] ) && is_object( $args[0] ) ) {
				$args[0]->has_children = ! empty( $children_elements[ $element->$id_field ] );
			}

			parent::display_element( $element, $children_elements, $max_depth, $depth, $args, $output );
		}
	}

endif;