<?php
/**
 * Component: section heading.
 *
 * @package Cik_Hybrid
 *
 * @var array $args {
 *     @type string $title   Heading text. Required.
 *     @type string $eyebrow Small label above the title. Default ''.
 *     @type string $intro   Paragraph below the title. Default ''.
 *     @type string $level   Heading tag, h1-h6. Default 'h2'.
 *     @type string $id      Heading id, e.g. for aria-labelledby. Default ''.
 *     @type string $align   'left' or 'center'. Default 'left'.
 * }
 */

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args(
	$args,
	array(
		'title'   => '',
		'eyebrow' => '',
		'intro'   => '',
		'level'   => 'h2',
		'id'      => '',
		'align'   => 'left',
	)
);

if ( '' === $args['title'] ) {
	return;
}

$cik_hybrid_tag = cik_hybrid_heading_tag( (string) $args['level'] );
?>
<div class="mb-10 max-w-content <?php echo 'center' === $args['align'] ? 'mx-auto text-center' : ''; ?>">
	<?php if ( '' !== $args['eyebrow'] ) : ?>
		<p class="mb-2 text-sm font-semibold tracking-wide text-accent uppercase"><?php echo esc_html( $args['eyebrow'] ); ?></p>
	<?php endif; ?>
	<<?php echo esc_attr( $cik_hybrid_tag ); ?><?php echo '' !== $args['id'] ? ' id="' . esc_attr( $args['id'] ) . '"' : ''; ?> class="text-3xl font-bold tracking-tight sm:text-4xl">
		<?php echo esc_html( $args['title'] ); ?>
	</<?php echo esc_attr( $cik_hybrid_tag ); ?>>
	<?php if ( '' !== $args['intro'] ) : ?>
		<p class="mt-4 text-lg text-muted"><?php echo esc_html( $args['intro'] ); ?></p>
	<?php endif; ?>
</div>
