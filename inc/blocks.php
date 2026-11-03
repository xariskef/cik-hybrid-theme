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
