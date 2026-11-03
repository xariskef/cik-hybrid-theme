<?php
/**
 * Fallback template for any request without a more specific template.
 *
 * @package Cik_Hybrid
 */

defined( 'ABSPATH' ) || exit;

get_header();

cik_hybrid_component(
	'page-header',
	array(
		'title' => is_archive() ? wp_strip_all_tags( get_the_archive_title() ) : get_bloginfo( 'name' ),
	)
);
cik_hybrid_component( 'post-grid' );

get_footer();
