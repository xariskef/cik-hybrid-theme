<?php
/**
 * Theme supports, menus, image sizes and editor styles.
 *
 * @package Cik_Hybrid
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers theme features.
 */
function cik_hybrid_setup(): void {
	load_theme_textdomain( 'cik-hybrid', CIK_HYBRID_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'align-wide' );
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' )
	);
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 64,
			'width'       => 240,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	add_image_size( 'cik-hybrid-card', 768, 480, true );
	add_image_size( 'cik-hybrid-hero', 1600, 900, true );

	register_nav_menus(
		array(
			'primary' => __( 'Primary menu', 'cik-hybrid' ),
			'footer'  => __( 'Footer menu', 'cik-hybrid' ),
		)
	);

	// The editor loads the same Tailwind build as the front end, so blocks look identical.
	add_theme_support( 'editor-styles' );
	$stylesheet = cik_hybrid_built_stylesheet();
	if ( null !== $stylesheet ) {
		add_editor_style( $stylesheet );
	}
}
add_action( 'after_setup_theme', 'cik_hybrid_setup' );

/**
 * Matches theme.json's contentSize (44rem) for embeds and legacy code that reads $content_width.
 */
function cik_hybrid_content_width(): void {
	$GLOBALS['content_width'] = apply_filters( 'cik_hybrid_content_width', 704 ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Core-defined global meant to be set by themes.
}
add_action( 'after_setup_theme', 'cik_hybrid_content_width', 0 );

/**
 * Adds a `js` class to <html> before first paint, so CSS can collapse
 * sub-menus only when the navigation script is able to open them again.
 */
function cik_hybrid_js_detection(): void {
	wp_print_inline_script_tag( "document.documentElement.classList.add('js');" );
}
add_action( 'wp_head', 'cik_hybrid_js_detection', 0 );

/**
 * Shorter, plain excerpt suffix.
 *
 * @return string
 */
function cik_hybrid_excerpt_more(): string {
	return '&hellip;';
}
add_filter( 'excerpt_more', 'cik_hybrid_excerpt_more' );
