<?php
/**
 * GWT_Walker_Nav_Menu
 *
 * A lightweight WordPress nav menu walker built for Tailwind CSS.
 * Supports multi-level dropdown menus with hover-triggered submenus,
 * keyboard/focus-within accessibility, and active-state highlighting.
 *
 * @package GWT
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'GWT_Walker_Nav_Menu' ) ) :
	class GWT_Walker_Nav_Menu extends Walker_Nav_Menu {
		/**
		 * Start the submenu level.
		 */
		public function start_lvl( &$output, $depth = 0, $args = null ) {
			$indent    = str_repeat( "	", $depth );
			$is_top    = ( $depth === 0 );
			// Top-level dropdown opens below; nested flyouts open to the right.
			$pos_class = $is_top ? 'left-0 top-full' : 'left-full top-0';
			// Hidden by default; shown via CSS li:hover > .gwt-submenu
			$output .= "
{$indent}<ul class=\"gwt-submenu absolute {$pos_class} hidden list-none flex-col bg-white shadow-lg z-[999] p-1 rounded-md min-w-[180px]\">
";
		}
		/**
		 * End the submenu level.
		 */
		public function end_lvl( &$output, $depth = 0, $args = null ) {
			$indent  = str_repeat( "	", $depth );
			$output .= "{$indent}</ul>
";
		}
		/**
		 * Start each menu item.
		 */
		public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
			$args         = is_array( $args ) ? (object) $args : $args;
			$has_children = ! empty( $args->has_children );
			$indent       = str_repeat( "	", $depth );
			// Detect current / ancestor states from WordPress item classes.
			$item_classes = array_map( 'sanitize_html_class', (array) $item->classes );
			$is_current   = in_array( 'current-menu-item', $item_classes, true )
			                || in_array( 'current_page_item', $item_classes, true );
			$is_ancestor  = in_array( 'current-menu-ancestor', $item_classes, true )
			                || in_array( 'current-menu-parent', $item_classes, true )
			                || in_array( 'current_page_parent', $item_classes, true );
			$base_li   = 'relative group rounded-md ';
			$link_base = 'block w-full px-3 py-2 text-sm whitespace-nowrap flex items-center transition-colors duration-150 ease-in-out';
			if ( $depth === 0 ) {
				if ( $is_current ) {
					$li_class = $base_li . 'flex items-center bg-green-700';
				} elseif ( $is_ancestor ) {
					$li_class = $base_li . 'flex items-center bg-green-800 hover:bg-[#a2b917]';
				} else {
					$li_class = $base_li . 'flex items-center hover:bg-[#a2b917]';
				}
				$link_class = $link_base . ' text-white font-medium hover:text-white';
			} else {
				if ( $is_current ) {
					$li_class = $base_li . 'bg-green-700';
				} else {
					$li_class = $base_li . 'hover:bg-[#a2b917]';
				}
				$link_class = $link_base . ' text-[#1f5d2b] hover:text-white';
			}
			$output .= "{$indent}<li class=\"{$li_class}\">";
			$atts = [
				'title'  => ! empty( $item->attr_title ) ? $item->attr_title : '',
				'target' => ! empty( $item->target ) ? $item->target : '',
				'rel'    => ! empty( $item->xfn ) ? $item->xfn : '',
				'href'   => ! empty( $item->url ) ? $item->url : '',
				'class'  => $link_class . ' drop-shadow-md',
			];
			if ( '_blank' === $atts['target'] ) {
				$atts['rel'] = trim( $atts['rel'] . ' noopener noreferrer' );
			}
			if ( $is_current ) {
				$atts['aria-current'] = 'page';
			}
			$attr_str = '';
			foreach ( $atts as $attr => $value ) {
				if ( $value !== '' && $value !== null ) {
					$attr_str .= ' ' . $attr . '="' . esc_attr( $value ) . '"';				}
			}
			$title   = apply_filters( 'the_title', $item->title, $item->ID );
			$output .= '<a' . $attr_str . '>' . esc_html( $title );
			if ( $has_children ) {
				if ( $depth === 0 ) {
					$output .= ' <svg class="ml-2 !mr-0 h-4 w-4 text-[#fbbf24] flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>';
				} else {
					$output .= ' <svg class="ml-2 !mr-0 h-4 w-4 text-[#1f5d2b] flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>';
				}
			}
			$output .= '</a>';
		}
		/**
		 * End each menu item.
		 */
		public function end_el( &$output, $item, $depth = 0, $args = null ) {
			$output .= "</li>
";
		}
		/**
		 * Properly set has_children and enforce depth limit.
		 */
		public function display_element( $element, &$children_elements, $max_depth, $depth = 0, $args = [], &$output = '' ) {
			if ( ! $element ) {
				return;
			}
			$id_field = $this->db_fields['id'];
			if ( is_array( $args ) && isset( $args[0] ) && is_object( $args[0] ) ) {
				$args[0]->has_children = ! empty( $children_elements[ $element->$id_field ] );
			}
			if ( $depth > 10 ) {
				return;
			}
			parent::display_element( $element, $children_elements, $max_depth, $depth, $args, $output );
		}
	}
endif;
