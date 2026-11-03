<?php
/**
 * Component: pagination for the main query.
 *
 * Wraps the_posts_pagination() so archives, search and the blog share one look.
 *
 * @package Cik_Hybrid
 *
 * @var array $args {
 *     @type string $label Accessible name of the navigation landmark. Default 'Posts'.
 * }
 */

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args(
	$args,
	array(
		'label' => __( 'Posts', 'cik-hybrid' ),
	)
);

$icon_args = array( 'class' => 'size-4' );

the_posts_pagination(
	array(
		'mid_size'           => 1,
		'aria_label'         => $args['label'],
		'class'              => 'cik-pagination mt-12',
		'prev_text'          => cik_hybrid_get_icon( 'chevron-left', $icon_args ) . '<span>' . esc_html__( 'Previous', 'cik-hybrid' ) . '</span>',
		'next_text'          => '<span>' . esc_html__( 'Next', 'cik-hybrid' ) . '</span>' . cik_hybrid_get_icon( 'chevron-right', $icon_args ),
		'before_page_number' => '<span class="sr-only">' . esc_html__( 'Page', 'cik-hybrid' ) . ' </span>',
	)
);
