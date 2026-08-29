<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class XIN_Nav_Walker extends Walker_Nav_Menu {
	public function start_lvl( &$output, $depth = 0, $args = null ) {
		$output .= '<ul class="dropdown-menu">';
	}

	public function end_lvl( &$output, $depth = 0, $args = null ) {
		$output .= '</ul>';
	}

	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$classes   = empty( $item->classes ) ? array() : (array) $item->classes;
		$has_child = in_array( 'menu-item-has-children', $classes, true );

		$li_class = 'nav-item';
		if ( $has_child && 0 === $depth ) {
			$li_class .= ' dropdown';
		}
		foreach ( array( 'current-menu-item', 'current-menu-parent', 'current_page_item', 'current-menu-ancestor' ) as $state ) {
			if ( in_array( $state, $classes, true ) ) {
				$li_class .= ' ' . $state;
			}
		}

		$link_class = 0 === $depth ? 'nav-link' : 'dropdown-item';
		$attrs      = '';

		if ( $has_child && 0 === $depth ) {
			$link_class .= ' dropdown-toggle';
			$attrs       = ' data-bs-toggle="dropdown" aria-expanded="false"';
		}

		$output .= sprintf(
			'<li class="%1$s"><a class="%2$s" href="%3$s"%4$s>%5$s</a>',
			esc_attr( $li_class ),
			esc_attr( $link_class ),
			esc_url( $item->url ),
			$attrs,
			esc_html( $item->title )
		);
	}

	public function end_el( &$output, $item, $depth = 0, $args = null ) {
		$output .= '</li>';
	}
}

class XIN_Offcanvas_Walker extends Walker_Nav_Menu {
	public function start_lvl( &$output, $depth = 0, $args = null ) {
		$output .= '<ul class="sub-menu">';
	}

	public function end_lvl( &$output, $depth = 0, $args = null ) {
		$output .= '</ul>';
	}

	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$output .= sprintf(
			'<li><a href="%1$s">%2$s</a>',
			esc_url( $item->url ),
			esc_html( $item->title )
		);
	}

	public function end_el( &$output, $item, $depth = 0, $args = null ) {
		$output .= '</li>';
	}
}
