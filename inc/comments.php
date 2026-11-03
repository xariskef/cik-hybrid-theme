<?php
/**
 * Comments switch. Comments are off by default (theme setting "Disable
 * comments"): no spam to moderate, no personal data to store, and a smaller
 * attack surface. Turning the setting off restores normal WordPress behaviour.
 *
 * @package Cik_Hybrid
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether the theme setting disables comments. On when nothing has been saved.
 *
 * @return bool
 */
function cik_hybrid_comments_disabled(): bool {
	return '1' === cik_hybrid_setting( 'disable_comments' );
}

/**
 * Hooks everything that turns comments off. Runs on `init` after ACF has
 * loaded (acf/init fires at init priority 5), so the setting can be read from either source.
 */
function cik_hybrid_disable_comments_setup(): void {
	if ( ! cik_hybrid_comments_disabled() ) {
		return;
	}

	add_filter( 'comments_open', '__return_false', 20 );
	add_filter( 'pings_open', '__return_false', 20 );
	add_filter( 'comments_array', '__return_empty_array', 20 );
	add_filter( 'get_comments_number', '__return_zero', 20 );
	add_filter( 'feed_links_show_comments_feed', '__return_false' );
	add_filter( 'rest_endpoints', 'cik_hybrid_remove_comment_endpoints' );
	add_filter( 'xmlrpc_methods', 'cik_hybrid_remove_pingback_methods' );
	add_filter( 'wp_headers', 'cik_hybrid_remove_pingback_header' );

	add_action( 'init', 'cik_hybrid_remove_comment_support', 100 );
	add_action( 'admin_menu', 'cik_hybrid_remove_comments_admin_menu' );
	add_action( 'admin_init', 'cik_hybrid_redirect_comments_admin' );
	add_action( 'admin_bar_menu', 'cik_hybrid_remove_comments_admin_bar', 999 );
	add_action( 'wp_dashboard_setup', 'cik_hybrid_remove_comments_dashboard_widget' );
}
add_action( 'init', 'cik_hybrid_disable_comments_setup', 6 );

/**
 * Removes the comments REST routes. The rest of the REST API stays, the block editor needs it.
 *
 * @param array<string, mixed> $endpoints Registered routes.
 * @return array<string, mixed>
 */
function cik_hybrid_remove_comment_endpoints( array $endpoints ): array {
	unset( $endpoints['/wp/v2/comments'], $endpoints['/wp/v2/comments/(?P<id>[\d]+)'] );

	return $endpoints;
}

/**
 * Removes the XML-RPC pingback methods.
 *
 * @param array<string, string> $methods XML-RPC methods.
 * @return array<string, string>
 */
function cik_hybrid_remove_pingback_methods( array $methods ): array {
	unset( $methods['pingback.ping'], $methods['pingback.extensionsList'] );

	return $methods;
}

/**
 * Drops the X-Pingback response header.
 *
 * @param array<string, string> $headers Response headers.
 * @return array<string, string>
 */
function cik_hybrid_remove_pingback_header( array $headers ): array {
	unset( $headers['X-Pingback'] );

	return $headers;
}

/**
 * Removes comment and trackback support from every post type.
 */
function cik_hybrid_remove_comment_support(): void {
	foreach ( get_post_types() as $post_type ) {
		if ( post_type_supports( $post_type, 'comments' ) ) {
			remove_post_type_support( $post_type, 'comments' );
			remove_post_type_support( $post_type, 'trackbacks' );
		}
	}
}

/**
 * Hides the Comments screen from the admin menu.
 */
function cik_hybrid_remove_comments_admin_menu(): void {
	remove_menu_page( 'edit-comments.php' );
}

/**
 * Sends direct visits to the Comments screen to the dashboard.
 */
function cik_hybrid_redirect_comments_admin(): void {
	global $pagenow;

	if ( 'edit-comments.php' === $pagenow ) {
		wp_safe_redirect( admin_url() );
		exit;
	}
}

/**
 * Removes the comments bubble from the admin bar.
 *
 * @param WP_Admin_Bar $admin_bar Admin bar instance.
 */
function cik_hybrid_remove_comments_admin_bar( WP_Admin_Bar $admin_bar ): void {
	$admin_bar->remove_node( 'comments' );
}

/**
 * Removes the "Recent Comments" dashboard widget (older installs and plugins add it).
 */
function cik_hybrid_remove_comments_dashboard_widget(): void {
	remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
}
