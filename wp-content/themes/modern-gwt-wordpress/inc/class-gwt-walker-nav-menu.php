<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'GWT_Walker_Nav_Menu' ) ) :

	class GWT_Walker_Nav_Menu extends Walker_Nav_Menu {

		/**
		 * Start the submenu level.
		 */
		public function start_lvl( &$output, $depth = 0, $args = null ) {
			$indent = str_repeat("\t", $depth);
			$is_top = ($depth === 0);

			// Positioning for top-level dropdown vs. flyout submenu
			$pos_class = $is_top ? "left-0 top-full" : "left-full top-0";

			// submenu is hidden by default; visibility handled via CSS li:hover > ul
			$output .= "\n{$indent}<ul class=\"absolute {$pos_class} hidden list-none flex-col bg-white shadow-lg z-[999] p-1 rounded-md min-w-[180px]\">\n";
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

			// Classes
			$base_li_classes = 'relative group rounded-md '; // Each item with submenu becomes a "group" container
			$link_base = 'block w-full px-3 py-2 text-sm whitespace-nowrap flex items-center transition-colors duration-150 ease-in-out';

			if ( $depth === 0 ) {
				$li_class = "{$base_li_classes} flex items-center hover:bg-[#a2b917]";
				$link_class = "{$link_base} text-white font-medium hover:text-white";
			} else {
				$li_class = "{$base_li_classes} hover:bg-[#a2b917]";
				$link_class = "{$link_base} text-[#1f5d2b] hover:text-white";
			}

			$output .= "{$indent}<li class=\"{$li_class}\">";

			// Build link attributes
			$atts = [
				'title'  => ! empty( $item->attr_title ) ? $item->attr_title : '',
				'target' => ! empty( $item->target ) ? $item->target : '',
				'rel'    => ! empty( $item->xfn ) ? $item->xfn : '',
				'href'   => ! empty( $item->url ) ? $item->url : '',
				'class'  => $link_class,
			];

			$attr_str = '';
			foreach ( $atts as $attr => $value ) {
				if ( ! empty( $value ) ) {
					$attr_str .= ' ' . $attr . '="' . esc_attr( $value ) . '"';
				}
			}

			$title = apply_filters( 'the_title', $item->title, $item->ID );
			$output .= '<a' . $attr_str . '>' . esc_html( $title );

			// Dropdown arrows
			if ( $has_children ) {
				if ( $depth === 0 ) {
					$output .= ' <svg class="ml-2 h-4 w-4 text-[#000000]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>';
				} else {
					$output .= ' <svg class="ml-2 h-4 w-4 text-[#1f5d2b]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>';
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
		 * Properly set has_children and enforce depth limit.
		 */
		public function display_element( $element, &$children_elements, $max_depth, $depth = 0, $args = [], &$output = '' ) {
			if ( ! $element ) return;

			$id_field = $this->db_fields['id'];
			if ( is_array( $args ) && isset( $args[0] ) && is_object( $args[0] ) ) {
				$args[0]->has_children = ! empty( $children_elements[ $element->$id_field ] );
			}

			// Cap depth to 10
			if ( $depth > 10 ) return;

			parent::display_element( $element, $children_elements, $max_depth, $depth, $args, $output );
		}
	}

endif;
