<?php
/**
 * Component: feature card (white card for the accent band on the front page).
 *
 * @package Cik_Hybrid
 *
 * @var array $args {
 *     @type string $title         Card title. Required.
 *     @type string $text          Short description. Default ''.
 *     @type string $url           Optional link; the whole card becomes clickable. Default ''.
 *     @type int    $number        Position shown as "01", "02"... 0 hides it. Default 0.
 *     @type string $heading_level Title tag. Default 'h3'.
 * }
 */

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args(
	$args,
	array(
		'title'         => '',
		'text'          => '',
		'url'           => '',
		'number'        => 0,
		'heading_level' => 'h3',
	)
);

if ( '' === $args['title'] ) {
	return;
}

$cik_hybrid_tag = cik_hybrid_heading_tag( (string) $args['heading_level'], 'h3' );
?>
<article class="cik-card group relative flex flex-col gap-4 rounded-card bg-canvas p-7 text-ink shadow-card transition duration-300 hover:-translate-y-1 hover:shadow-2xl sm:p-8">
	<?php if ( absint( $args['number'] ) > 0 ) : ?>
		<span class="inline-flex size-12 items-center justify-center rounded-full bg-brand-tint font-display text-lg font-bold text-accent-strong" aria-hidden="true">
			<?php echo esc_html( sprintf( '%02d', absint( $args['number'] ) ) ); ?>
		</span>
	<?php endif; ?>
	<<?php echo esc_attr( $cik_hybrid_tag ); ?> class="text-2xl leading-tight">
		<?php if ( '' !== $args['url'] ) : ?>
			<a class="text-ink no-underline after:absolute after:inset-0 hover:text-accent" href="<?php echo esc_url( $args['url'] ); ?>"><?php echo esc_html( $args['title'] ); ?></a>
		<?php else : ?>
			<?php echo esc_html( $args['title'] ); ?>
		<?php endif; ?>
	</<?php echo esc_attr( $cik_hybrid_tag ); ?>>
	<?php if ( '' !== $args['text'] ) : ?>
		<p class="text-muted"><?php echo esc_html( $args['text'] ); ?></p>
	<?php endif; ?>
</article>
