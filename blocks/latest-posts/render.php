<?php
/**
 * Server render for cik-hybrid/latest-posts.
 *
 * Attributes arrive from post content (or the REST preview), so they are
 * re-validated here even though block.json declares their types.
 *
 * @package Cik_Hybrid
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Inner content (unused, the block has none).
 * @var WP_Block $block      Block instance.
 */

defined( 'ABSPATH' ) || exit;

$count        = min( max( absint( $attributes['count'] ?? 3 ), 1 ), 12 );
$columns      = min( max( absint( $attributes['columns'] ?? 3 ), 1 ), 4 );
$category     = absint( $attributes['category'] ?? 0 );
$show_excerpt = ! empty( $attributes['showExcerpt'] );

$query_args = array(
	'post_type'           => 'post',
	'post_status'         => 'publish',
	'posts_per_page'      => $count,
	'ignore_sticky_posts' => true,
	'no_found_rows'       => true,
);
if ( $category ) {
	$query_args['cat'] = $category;
}

$latest = new WP_Query( $query_args );

if ( ! $latest->have_posts() ) {
	// Only the editor preview explains an empty result; visitors get nothing.
	if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
		printf( '<p %s>%s</p>', get_block_wrapper_attributes(), esc_html__( 'No posts found.', 'cik-hybrid' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_block_wrapper_attributes() escapes its output.
	}
	return;
}

// Full class names so Tailwind finds them when scanning this file.
$grid_classes = array(
	1 => 'grid-cols-1',
	2 => 'sm:grid-cols-2',
	3 => 'sm:grid-cols-2 lg:grid-cols-3',
	4 => 'sm:grid-cols-2 lg:grid-cols-4',
);
?>
<div <?php echo get_block_wrapper_attributes( array( 'class' => 'grid gap-8 ' . $grid_classes[ $columns ] ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by core. ?>>
	<?php
	foreach ( $latest->posts as $latest_post ) {
		cik_hybrid_component(
			'card',
			array(
				'post'          => $latest_post,
				'heading_level' => 'h3',
				'show_excerpt'  => $show_excerpt,
			)
		);
	}
	?>
</div>
