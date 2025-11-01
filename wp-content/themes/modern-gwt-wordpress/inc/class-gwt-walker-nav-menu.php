<?php
/**
 * Custom GWT Walker Nav Menu
 * Outputs Tailwind-style markup similar to the provided reference element.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'GWT_Walker_Nav_Menu' ) ) :
class GWT_Walker_Nav_Menu extends Walker_Nav_Menu {
	/**
	 * Starts the list of nested sub-items.
	 */
	public function start_lvl( &$output, $depth = 0, $args = null ) {
		$indent = str_repeat( "\t", max( 0, (int) $depth ) );
		if ( $depth === 0 ) {
			$output .= "\n{$indent}<div class=\"absolute left-0 top-full hidden group-hover:flex flex-col bg-gray-100 z-[46] shadow-md p-3 rounded-md text-subtitle pt-2\">\n";
		} else {
			$output .= "\n{$indent}<div class=\"hidden group-hover:flex flex-col bg-gray-100 shadow-md p-2 rounded-md text-subtitle pt-2\">\n";
		}
	}

	/**
	 * Ends the list of nested sub-items.
	 */
	public function end_lvl( &$output, $depth = 0, $args = null ) {
		$indent = str_repeat( "\t", max( 0, (int) $depth ) );
		$output .= "{$indent}</div>\n";
	}

	/**
	 * Starts the element output.
	 */
	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$args = is_array( $args ) ? (object) $args : $args;
		$has_children = ! empty( $args->has_children );

		// Compute attributes for anchor
		$atts = [];
		$atts['title']  = ! empty( $item->attr_title ) ? $item->attr_title : '';
		$atts['target'] = ! empty( $item->target ) ? $item->target : '';
		$atts['rel']    = ! empty( $item->xfn ) ? $item->xfn : '';
		$atts['href']   = ! empty( $item->url ) ? $item->url : '';

		if ( $depth === 0 ) {
			// Top-level item
			$li_classes = 'relative flex items-center hover:bg-cbc-yellow-green group duration-400 ease-in-out';
			$output .= '<li class="' . $li_classes . '">';
			$output .= '<div class="flex flex-col gap-2">'; // inner wrapper

			if ( $has_children ) {
				$output .= '<div class="fixed w-full h-full top-0 left-0 z-[1]"></div>';
			}

			$output .= '<div class="z-[100]">';

			$atts['class'] = 'px-3 py-1 text-gray-100 whitespace-nowrap text-normal inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out';
			$attr = '';
			foreach ( $atts as $an => $av ) {
				if ( ! empty( $av ) ) { $attr .= ' ' . $an . '="' . esc_attr( $av ) . '"'; }
			}

			$title = apply_filters( 'the_title', $item->title, $item->ID );
			$output .= '<a' . $attr . '>' . esc_html( $title );
			if ( $has_children ) {
				$output .= ' <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"></path></svg>';
			}
			$output .= '</a>';
			$output .= '</div>'; // .z-[100]

			// Submenu container is output in start_lvl when children exist
		} else {
			// Submenu items rendered as rows inside the submenu container
			$output .= '<div class="flex text-gray-100 items-center hover:bg-cbc-yellow-green duration-400 rounded-md ease-in-out text-gray-700">';
			$atts['class'] = 'px-3 py-1 whitespace-nowrap text-normal inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out';
			$attr = '';
			foreach ( $atts as $an => $av ) {
				if ( ! empty( $av ) ) { $attr .= ' ' . $an . '="' . esc_attr( $av ) . '"'; }
			}
			$title = apply_filters( 'the_title', $item->title, $item->ID );
			$output .= '<a' . $attr . '>' . esc_html( $title ) . '</a>';
			$output .= '</div>';
		}
	}

	/**
	 * Ends the element output.
	 */
	public function end_el( &$output, $item, $depth = 0, $args = null ) {
		if ( $depth === 0 ) {
			$output .= '</div>'; // closes inner wrapper
			$output .= '</li>';
		}
	}
}
endif;
