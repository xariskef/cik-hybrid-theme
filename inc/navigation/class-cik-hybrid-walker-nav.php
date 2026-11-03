<?php
/**
 * Header menu walker with accessible dropdown toggles.
 *
 * @package Cik_Hybrid
 */

defined( 'ABSPATH' ) || exit;

/**
 * Keeps core's markup and filters, but puts each parent link next to a
 * <button aria-expanded> that controls its sub-menu. Links stay real links:
 * the button, not the parent link, opens the dropdown (works for touch,
 * keyboard and screen readers, and the parent page stays reachable).
 */
class Cik_Hybrid_Walker_Nav extends Walker_Nav_Menu {

	/**
	 * ID of the parent item whose sub-menu is about to open.
	 *
	 * @var int
	 */
	private int $parent_id = 0;

	/**
	 * Starts a sub-menu list.
	 *
	 * @param string        $output Used to append additional content (passed by reference).
	 * @param int           $depth  Depth of menu item.
	 * @param stdClass|null $args   An object of wp_nav_menu() arguments.
	 */
	public function start_lvl( &$output, $depth = 0, $args = null ): void {
		$output .= sprintf( '<ul id="%s" class="sub-menu">', esc_attr( 'cik-submenu-' . $this->parent_id ) );
	}

	/**
	 * Ends a sub-menu list.
	 *
	 * @param string        $output Used to append additional content (passed by reference).
	 * @param int           $depth  Depth of menu item.
	 * @param stdClass|null $args   An object of wp_nav_menu() arguments.
	 */
	public function end_lvl( &$output, $depth = 0, $args = null ): void {
		$output .= '</ul>';
	}

	/**
	 * Starts the element output.
	 *
	 * @param string        $output            Used to append additional content (passed by reference).
	 * @param WP_Post       $data_object       Menu item data object.
	 * @param int           $depth             Depth of menu item.
	 * @param stdClass|null $args              An object of wp_nav_menu() arguments.
	 * @param int           $current_object_id Optional. ID of the current menu item.
	 */
	public function start_el( &$output, $data_object, $depth = 0, $args = null, $current_object_id = 0 ): void {
		$item_html = '';
		parent::start_el( $item_html, $data_object, $depth, $args, $current_object_id );

		$classes = (array) $data_object->classes;
		if ( ! in_array( 'menu-item-has-children', $classes, true ) ) {
			$output .= $item_html;
			return;
		}

		$this->parent_id = (int) $data_object->ID;

		// Core's <li> attributes are escaped, so the first ">" closes the opening tag.
		$split = strpos( $item_html, '>' );
		if ( false === $split ) {
			$output .= $item_html;
			return;
		}

		$button = sprintf(
			'<button type="button" class="cik-menu__toggle" aria-expanded="false" aria-controls="%1$s" aria-label="%2$s">%3$s</button>',
			esc_attr( 'cik-submenu-' . $this->parent_id ),
			/* translators: %s: menu item title. */
			esc_attr( sprintf( __( 'Show submenu for %s', 'cik-hybrid' ), wp_strip_all_tags( $data_object->title ) ) ),
			cik_hybrid_get_icon( 'chevron-down' )
		);

		$output .= substr( $item_html, 0, $split + 1 )
			. '<div class="cik-menu__row">'
			. substr( $item_html, $split + 1 )
			. $button
			. '</div>';
	}
}

/**
 * Fallback for the primary location when no menu is assigned: top-level pages.
 *
 * @param array<string, mixed> $args wp_nav_menu() arguments.
 */
function cik_hybrid_primary_menu_fallback( array $args ): void {
	$pages = get_pages(
		array(
			'parent'       => 0,
			'hierarchical' => false,
			'number'       => 6,
			'sort_column'  => 'menu_order,post_title',
		)
	);

	echo '<ul class="' . esc_attr( $args['menu_class'] ?? 'cik-menu' ) . '">';
	printf(
		'<li class="menu-item"><a href="%s">%s</a></li>',
		esc_url( home_url( '/' ) ),
		esc_html__( 'Home', 'cik-hybrid' )
	);
	foreach ( $pages as $page ) {
		if ( (int) get_option( 'page_on_front' ) === $page->ID ) {
			continue;
		}
		printf(
			'<li class="menu-item"><a href="%s">%s</a></li>',
			esc_url( get_permalink( $page ) ),
			esc_html( get_the_title( $page ) )
		);
	}
	echo '</ul>';
}
