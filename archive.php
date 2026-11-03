<?php
/**
 * Archives: categories, tags, custom taxonomies, authors, dates and post type archives.
 *
 * @package Cik_Hybrid
 */

defined( 'ABSPATH' ) || exit;

get_header();

cik_hybrid_component(
	'page-header',
	array(
		'title'       => wp_strip_all_tags( get_the_archive_title() ),
		'description' => get_the_archive_description(),
	)
);
cik_hybrid_component( 'post-grid' );

get_footer();
