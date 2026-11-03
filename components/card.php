<?php
/**
 * Component: post card.
 *
 * Used by archives, the front page and the latest-posts block.
 *
 * @package Cik_Hybrid
 *
 * @var array $args {
 *     @type WP_Post|int|null $post          Post object or ID. Default the current post.
 *     @type string           $heading_level Title tag. Default 'h2'.
 *     @type bool             $show_excerpt  Whether to print the excerpt. Default true.
 *     @type bool             $show_image    Whether to print the featured image. Default true.
 * }
 */

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args(
	$args,
	array(
		'post'          => null,
		'heading_level' => 'h2',
		'show_excerpt'  => true,
		'show_image'    => true,
	)
);

$card_post = get_post( $args['post'] );
if ( ! $card_post instanceof WP_Post ) {
	return;
}

$heading_tag = cik_hybrid_heading_tag( (string) $args['heading_level'] );
$permalink   = get_permalink( $card_post );
$categories  = 'post' === $card_post->post_type ? get_the_category( $card_post->ID ) : array();
?>
<article <?php post_class( 'group relative flex flex-col overflow-hidden rounded-card border border-line bg-canvas transition-shadow hover:shadow-lg', $card_post ); ?>>
	<?php if ( $args['show_image'] && has_post_thumbnail( $card_post ) ) : ?>
		<div class="aspect-[16/10] overflow-hidden bg-surface">
			<?php
			echo get_the_post_thumbnail(
				$card_post,
				'cik-hybrid-card',
				array(
					'class' => 'size-full object-cover transition-transform duration-300 group-hover:scale-105',
					'sizes' => '(min-width: 64rem) 22rem, (min-width: 40rem) 50vw, 100vw',
				)
			);
			?>
		</div>
	<?php endif; ?>

	<div class="flex flex-1 flex-col gap-3 p-6">
		<p class="flex flex-wrap items-center gap-x-3 text-sm text-muted">
			<time datetime="<?php echo esc_attr( get_the_date( 'c', $card_post ) ); ?>"><?php echo esc_html( get_the_date( '', $card_post ) ); ?></time>
			<?php if ( ! empty( $categories ) ) : ?>
				<span aria-hidden="true">&middot;</span>
				<span><?php echo esc_html( $categories[0]->name ); ?></span>
			<?php endif; ?>
		</p>

		<<?php echo esc_attr( $heading_tag ); ?> class="text-xl font-semibold leading-snug">
			<?php // The stretched link makes the whole card clickable while keeping one focusable element. ?>
			<a class="text-ink no-underline after:absolute after:inset-0 hover:text-accent" href="<?php echo esc_url( $permalink ); ?>">
				<?php echo esc_html( get_the_title( $card_post ) ); ?>
			</a>
		</<?php echo esc_attr( $heading_tag ); ?>>

		<?php if ( $args['show_excerpt'] ) : ?>
			<p class="text-muted"><?php echo esc_html( wp_trim_words( get_the_excerpt( $card_post ), 24 ) ); ?></p>
		<?php endif; ?>
	</div>
</article>
