<?php
/**
 * Blog posts index (the "Posts page", or the front page when it shows latest posts
 * and front-page.php is not used).
 *
 * @package Cik_Hybrid
 */

defined( 'ABSPATH' ) || exit;

get_header();

$cik_hybrid_posts_page = (int) get_option( 'page_for_posts' );

cik_hybrid_component(
	'page-header',
	array(
		'title'       => $cik_hybrid_posts_page ? get_the_title( $cik_hybrid_posts_page ) : __( 'Blog', 'cik-hybrid' ),
		'description' => $cik_hybrid_posts_page ? get_the_excerpt( $cik_hybrid_posts_page ) : '',
	)
);
cik_hybrid_component( 'post-grid', array( 'empty_message' => __( 'No posts have been published yet.', 'cik-hybrid' ) ) );

get_footer();
