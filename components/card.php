<?php
/**
 * Component: post card.
 *
 * Used by archives, the front page and the latest-posts block. The first
 * category is shown as a badge over the image.
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
$category    = ! empty( $categories ) ? $categories[0]->name : '';
$with_image  = $args['show_image'] && has_post_thumbnail( $card_post );
?>
<article <?php post_class( 'cik-card group relative flex flex-col overflow-hidden rounded-card bg-canvas text-ink shadow-card ring-1 ring-line/70 transition duration-300 hover:-translate-y-1 hover:shadow-xl', $card_post ); ?>>
	<?php if ( $with_image ) : ?>
		<div class="relative aspect-[16/10] overflow-hidden bg-surface">
			<?php
			echo get_the_post_thumbnail(
				$card_post,
				'cik-hybrid-card',
				array(
					'class' => 'size-full object-cover transition-transform duration-500 group-hover:scale-105',
					'sizes' => '(min-width: 64rem) 24rem, (min-width: 40rem) 50vw, 100vw',
				)
			);
			?>
			<?php if ( '' !== $category ) : ?>
				<span class="cik-badge cik-badge--solid absolute top-4 left-4 shadow-sm"><?php echo esc_html( $category ); ?></span>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<div class="flex flex-1 flex-col gap-3 p-6 sm:p-7">
		<p class="flex flex-wrap items-center gap-x-3 gap-y-2 text-sm text-muted">
			<?php if ( ! $with_image && '' !== $category ) : ?>
				<span class="cik-badge"><?php echo esc_html( $category ); ?></span>
			<?php endif; ?>
			<time datetime="<?php echo esc_attr( get_the_date( 'c', $card_post ) ); ?>"><?php echo esc_html( get_the_date( '', $card_post ) ); ?></time>
		</p>

		<<?php echo esc_attr( $heading_tag ); ?> class="text-xl leading-tight sm:text-2xl">
			<?php // The stretched link makes the whole card clickable while keeping one focusable element. ?>
			<a class="text-ink no-underline after:absolute after:inset-0 hover:text-accent" href="<?php echo esc_url( $permalink ); ?>">
				<?php echo esc_html( get_the_title( $card_post ) ); ?>
			</a>
		</<?php echo esc_attr( $heading_tag ); ?>>

		<?php if ( $args['show_excerpt'] ) : ?>
			<p class="text-muted"><?php echo esc_html( wp_trim_words( get_the_excerpt( $card_post ), 22 ) ); ?></p>
		<?php endif; ?>

		<span class="mt-auto inline-flex items-center gap-2 pt-3 text-sm font-bold text-accent" aria-hidden="true">
			<?php esc_html_e( 'Read article', 'cik-hybrid' ); ?>
			<?php
			cik_hybrid_component(
				'icon',
				array(
					'name'  => 'arrow-right',
					'class' => 'size-4 transition-transform group-hover:translate-x-1',
				)
			);
			?>
		</span>
	</div>
</article>
