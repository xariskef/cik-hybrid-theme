<?php
/**
 * Component: full-bleed hero with a photo, dark overlay and a large headline.
 *
 * The header island floats over it, so the content starts below the island.
 *
 * @package Cik_Hybrid
 *
 * @var array $args {
 *     @type string $title     Main heading. Required.
 *     @type string $eyebrow   Small pill label above the title. Default ''.
 *     @type string $text      Lead paragraph. Default ''.
 *     @type int    $image_id  Attachment ID for the background photo. Default 0.
 *     @type array  $primary   Primary call to action: [ 'label' => '', 'url' => '', 'icon' => '' ]. Default empty.
 *     @type array  $secondary Secondary call to action, same shape. Default empty.
 *     @type string $level     Heading tag. Default 'h1'.
 * }
 */

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args(
	$args,
	array(
		'title'     => '',
		'eyebrow'   => '',
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
<section class="cik-hero relative isolate flex min-h-[36rem] items-end overflow-hidden bg-night text-night-ink sm:min-h-[44rem] lg:min-h-[52rem]">
	<?php if ( $args['image_id'] ) : ?>
		<?php
		echo wp_get_attachment_image(
			absint( $args['image_id'] ),
			'cik-hybrid-hero',
			false,
			array(
				'class'         => 'absolute inset-0 -z-20 size-full object-cover',
				'loading'       => 'eager',
				'fetchpriority' => 'high',
				'sizes'         => '100vw',
			)
		);
		?>
		<div class="absolute inset-0 -z-10 bg-night/65 lg:bg-transparent lg:bg-gradient-to-r lg:from-night/95 lg:via-night/70 lg:to-night/25" aria-hidden="true"></div>
		<div class="absolute inset-x-0 bottom-0 -z-10 h-2/3 bg-gradient-to-t from-night/90 to-transparent" aria-hidden="true"></div>
	<?php else : ?>
		<div class="absolute inset-0 -z-10 bg-[radial-gradient(60rem_40rem_at_85%_10%,rgb(0_136_204/0.35),transparent_70%)]" aria-hidden="true"></div>
	<?php endif; ?>

	<div class="cik-container pt-40 pb-16 sm:pb-24 lg:pb-32">
		<?php if ( '' !== $args['eyebrow'] ) : ?>
			<p class="cik-badge cik-badge--dark mb-6"><?php echo esc_html( $args['eyebrow'] ); ?></p>
		<?php endif; ?>
		<<?php echo esc_attr( $cik_hybrid_tag ); ?> class="max-w-5xl text-5xl leading-[0.98] font-bold text-balance sm:text-7xl lg:text-[6.25rem]">
			<?php echo esc_html( $args['title'] ); ?>
		</<?php echo esc_attr( $cik_hybrid_tag ); ?>>
		<?php if ( '' !== $args['text'] ) : ?>
			<p class="mt-6 max-w-2xl text-lg text-night-ink/85 sm:mt-8 sm:text-xl"><?php echo esc_html( $args['text'] ); ?></p>
		<?php endif; ?>
		<?php if ( ! empty( $args['primary']['url'] ) || ! empty( $args['secondary']['url'] ) ) : ?>
			<div class="mt-10 flex flex-wrap gap-3">
				<?php
				if ( ! empty( $args['primary']['url'] ) ) {
					cik_hybrid_component( 'button', array_merge( (array) $args['primary'], array( 'class' => 'px-8 py-4 text-lg' ) ) );
				}
				if ( ! empty( $args['secondary']['url'] ) ) {
					cik_hybrid_component( 'button', array_merge( (array) $args['secondary'], array( 'variant' => 'outline-light' ) ) );
				}
				?>
			</div>
		<?php endif; ?>
	</div>
</section>
