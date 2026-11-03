<?php
/**
 * Component: call-to-action panel (rounded accent block inside the container).
 *
 * @package Cik_Hybrid
 *
 * @var array $args {
 *     @type string $title  Heading. Required.
 *     @type string $text   Paragraph under the heading. Default ''.
 *     @type array  $button [ 'label' => '', 'url' => '' ]. Default empty.
 *     @type string $id     Heading id, for aria-labelledby. Default 'cik-cta-title'.
 * }
 */

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args(
	$args,
	array(
		'title'  => '',
		'text'   => '',
		'button' => array(),
		'id'     => 'cik-cta-title',
	)
);

if ( '' === $args['title'] ) {
	return;
}
?>
<section class="cik-container pt-20 sm:pt-28" aria-labelledby="<?php echo esc_attr( $args['id'] ); ?>">
	<div class="relative isolate overflow-hidden rounded-[2rem] bg-accent px-6 py-14 text-accent-ink sm:px-14 sm:py-20">
		<div class="absolute -top-24 -right-24 -z-10 size-80 rounded-full border-[3rem] border-canvas/10" aria-hidden="true"></div>
		<div class="absolute -bottom-32 left-1/3 -z-10 size-72 rounded-full bg-accent-strong/60 blur-3xl" aria-hidden="true"></div>
		<div class="flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
			<div class="max-w-2xl">
				<h2 id="<?php echo esc_attr( $args['id'] ); ?>" class="text-4xl text-balance sm:text-5xl lg:text-6xl"><?php echo esc_html( $args['title'] ); ?></h2>
				<?php if ( '' !== $args['text'] ) : ?>
					<p class="mt-5 text-lg text-accent-ink/90"><?php echo esc_html( $args['text'] ); ?></p>
				<?php endif; ?>
			</div>
			<?php
			if ( ! empty( $args['button']['url'] ) && ! empty( $args['button']['label'] ) ) {
				cik_hybrid_component(
					'button',
					array(
						'label'   => (string) $args['button']['label'],
						'url'     => (string) $args['button']['url'],
						'variant' => 'light',
						'icon'    => 'arrow-right',
						'class'   => 'shrink-0 px-8 py-4 text-lg',
					)
				);
			}
			?>
		</div>
	</div>
</section>
