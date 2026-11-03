<?php
/**
 * Search results.
 *
 * @package Cik_Hybrid
 */

defined( 'ABSPATH' ) || exit;

get_header();

cik_hybrid_component(
	'page-header',
	array(
		/* translators: %s: search query. */
		'title' => sprintf( __( 'Search results for "%s"', 'cik-hybrid' ), get_search_query( false ) ),
	)
);
?>
<div class="cik-container pt-10">
	<div class="max-w-content"><?php get_search_form(); ?></div>
</div>
<?php
cik_hybrid_component(
	'post-grid',
	array(
		'empty_message' => __( 'No results matched your search. Try different keywords.', 'cik-hybrid' ),
		'search_form'   => false,
	)
);

get_footer();
