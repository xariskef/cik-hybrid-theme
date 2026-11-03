<?php
/**
 * Archives: categories, tags, custom taxonomies, authors, dates and post type archives.
 *
 * The archive type ("Category", "Tag"...) is shown as the eyebrow, so the title is just the name.
 *
 * @package Cik_Hybrid
 */

defined( 'ABSPATH' ) || exit;

get_header();

$cik_hybrid_eyebrow = '';
$cik_hybrid_title   = get_the_archive_title();
if ( is_category() || is_tag() ) {
	$cik_hybrid_eyebrow = is_category() ? __( 'Category', 'cik-hybrid' ) : __( 'Tag', 'cik-hybrid' );
	$cik_hybrid_title   = single_term_title( '', false );
} elseif ( is_author() ) {
	$cik_hybrid_eyebrow = __( 'Author', 'cik-hybrid' );
	$cik_hybrid_title   = get_the_author_meta( 'display_name', get_queried_object_id() );
}

cik_hybrid_component(
	'page-header',
	array(
		'eyebrow'     => $cik_hybrid_eyebrow,
		'title'       => wp_strip_all_tags( (string) $cik_hybrid_title ),
		'description' => get_the_archive_description(),
	)
);
cik_hybrid_component( 'post-grid' );

get_footer();
