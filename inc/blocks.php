<?php
/**
 * Block registration.
 *
 * @package Cik_Hybrid
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers the theme's dynamic blocks from their block.json files.
 */
function cik_hybrid_register_blocks(): void {
	register_block_type( CIK_HYBRID_DIR . '/blocks/latest-posts' );
}
add_action( 'init', 'cik_hybrid_register_blocks' );

/**
 * Normalises latest-posts attributes. They come from post content or the REST
 * preview, so block.json types are not a guarantee.
 *
 * @param array<string, mixed> $attributes Raw block attributes.
 * @return array{count: int, columns: int, category: int, show_excerpt: bool}
 */
function cik_hybrid_latest_posts_settings( array $attributes ): array {
	return array(
		'count'        => min( max( absint( $attributes['count'] ?? 3 ), 1 ), 12 ),
		'columns'      => min( max( absint( $attributes['columns'] ?? 3 ), 1 ), 4 ),
		'category'     => absint( $attributes['category'] ?? 0 ),
		'show_excerpt' => ! empty( $attributes['showExcerpt'] ),
	);
}
