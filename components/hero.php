<?php
/**
 * Component: hero.
 *
 * @package Cik_Hybrid
 *
 * @var array $args {
 *     @type string $title     Main heading. Required.
 *     @type string $text      Lead paragraph. Default ''.
 *     @type int    $image_id  Attachment ID for the side image. Default 0.
 *     @type array  $primary   Primary call to action: [ 'label' => '', 'url' => '' ]. Default empty.
 *     @type array  $secondary Secondary call to action, same shape. Default empty.
 *     @type string $level     Heading tag. Default 'h1'.
 * }
 */

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args(
	$args,
	array(
		'title'     => '',
		'text'      => '',
		'image_id'  => 0,
		'primary'   => array(),
		'secondary' => array(),
		'level'     => 'h1',
	)
);

if ( '' === $args['title'] ) {
	return;
}

$cik_hybrid_tag = cik_hybrid_heading_tag( (string) $args['level'], 'h1' );
?>
<section class="border-b border-line bg-surface">
	<div class="cik-container grid items-center gap-10 py-16 lg:grid-cols-2 lg:py-24">
		<div>
			<<?php echo esc_attr( $cik_hybrid_tag ); ?> class="text-4xl font-bold tracking-tight text-balance sm:text-5xl">
				<?php echo esc_html( $args['title'] ); ?>
			</<?php echo esc_attr( $cik_hybrid_tag ); ?>>
			<?php if ( '' !== $args['text'] ) : ?>
				<p class="mt-6 max-w-xl text-lg text-muted"><?php echo esc_html( $args['text'] ); ?></p>
			<?php endif; ?>
			<?php if ( ! empty( $args['primary']['url'] ) || ! empty( $args['secondary']['url'] ) ) : ?>
				<div class="mt-8 flex flex-wrap gap-3">
					<?php
					if ( ! empty( $args['primary']['url'] ) ) {
						cik_hybrid_component( 'button', (array) $args['primary'] );
					}
					if ( ! empty( $args['secondary']['url'] ) ) {
						cik_hybrid_component( 'button', array_merge( (array) $args['secondary'], array( 'variant' => 'secondary' ) ) );
					}
					?>
				</div>
			<?php endif; ?>
		</div>
		<?php if ( $args['image_id'] ) : ?>
			<div>
				<?php
				echo wp_get_attachment_image(
					absint( $args['image_id'] ),
					'cik-hybrid-hero',
					false,
					array(
						'class'         => 'aspect-video w-full rounded-card object-cover',
						'loading'       => 'eager',
						'fetchpriority' => 'high',
						'sizes'         => '(min-width: 64rem) 36rem, 100vw',
					)
				);
				?>
			</div>
		<?php endif; ?>
	</div>
</section>
