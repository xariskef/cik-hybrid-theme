<?php
/**
 * Component: page header, a dark band under the header island with
 * breadcrumbs, title and an optional description.
 *
 * @package Cik_Hybrid
 *
 * @var array $args {
 *     @type string $title       Page title. Required.
 *     @type string $eyebrow     Small pill label above the title. Default ''.
 *     @type string $description HTML description, filtered with wp_kses_post(). Default ''.
 *     @type bool   $breadcrumbs Whether to print breadcrumbs. Default true.
 *     @type bool   $overlap     Extra bottom space so the next element (e.g. an image) can overlap the band. Default false.
 * }
 */

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args(
	$args,
	array(
		'title'       => '',
		'eyebrow'     => '',
		'description' => '',
		'breadcrumbs' => true,
		'overlap'     => false,
	)
);
?>
<header class="cik-page-header relative isolate overflow-hidden bg-night text-night-ink">
	<div class="absolute inset-0 -z-10 bg-[radial-gradient(48rem_28rem_at_90%_0%,rgb(0_136_204/0.32),transparent_70%)]" aria-hidden="true"></div>
	<div class="cik-container pt-36 sm:pt-44 <?php echo $args['overlap'] ? 'pb-32 sm:pb-44' : 'pb-16 sm:pb-20'; ?>">
		<?php
		if ( $args['breadcrumbs'] ) {
			cik_hybrid_component( 'breadcrumbs', array( 'class' => 'mb-8' ) );
		}
		?>
		<?php if ( '' !== $args['eyebrow'] ) : ?>
			<p class="cik-badge cik-badge--dark mb-5"><?php echo esc_html( $args['eyebrow'] ); ?></p>
		<?php endif; ?>
		<h1 class="max-w-5xl text-4xl leading-[1.02] text-balance sm:text-6xl lg:text-7xl"><?php echo esc_html( $args['title'] ); ?></h1>
		<?php if ( '' !== trim( (string) $args['description'] ) ) : ?>
			<div class="mt-6 max-w-2xl text-lg text-night-muted sm:text-xl"><?php echo wp_kses_post( $args['description'] ); ?></div>
		<?php endif; ?>
	</div>
</header>
