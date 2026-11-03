<?php
/**
 * Component: section heading with an optional pill eyebrow and a link on the side.
 *
 * @package Cik_Hybrid
 *
 * @var array $args {
 *     @type string $title   Heading text. Required.
 *     @type string $eyebrow Small pill label above the title. Default ''.
 *     @type string $intro   Paragraph below the title. Default ''.
 *     @type string $level   Heading tag, h1-h6. Default 'h2'.
 *     @type string $id      Heading id, e.g. for aria-labelledby. Default ''.
 *     @type string $align   'left' or 'center'. Default 'left'.
 *     @type string $tone    Background it sits on: 'light', 'dark' or 'accent'. Default 'light'.
 *     @type array  $link    Optional [ 'label' => '', 'url' => '' ] shown beside the heading. Default empty.
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
		'tone'    => 'light',
		'link'    => array(),
	)
);

if ( '' === $args['title'] ) {
	return;
}

$cik_hybrid_tag    = cik_hybrid_heading_tag( (string) $args['level'] );
$cik_hybrid_tones  = array(
	'light'  => array( 'cik-badge', 'text-muted', 'secondary' ),
	'dark'   => array( 'cik-badge cik-badge--dark', 'text-night-muted', 'outline-light' ),
	'accent' => array( 'cik-badge cik-badge--light', 'text-accent-ink/90', 'light' ),
);
$cik_hybrid_tone   = $cik_hybrid_tones[ $args['tone'] ] ?? $cik_hybrid_tones['light'];
$cik_hybrid_center = 'center' === $args['align'];
$cik_hybrid_link   = ! empty( $args['link']['url'] ) && ! empty( $args['link']['label'] ) && ! $cik_hybrid_center;
?>
<div class="mb-12 flex flex-wrap items-end justify-between gap-6 <?php echo $cik_hybrid_center ? 'justify-center text-center' : ''; ?>">
	<div class="max-w-3xl <?php echo $cik_hybrid_center ? 'mx-auto' : ''; ?>">
		<?php if ( '' !== $args['eyebrow'] ) : ?>
			<p class="<?php echo esc_attr( $cik_hybrid_tone[0] ); ?> mb-4"><?php echo esc_html( $args['eyebrow'] ); ?></p>
		<?php endif; ?>
		<<?php echo esc_attr( $cik_hybrid_tag ); ?><?php echo '' !== $args['id'] ? ' id="' . esc_attr( $args['id'] ) . '"' : ''; ?> class="text-4xl text-balance sm:text-5xl lg:text-6xl">
			<?php echo esc_html( $args['title'] ); ?>
		</<?php echo esc_attr( $cik_hybrid_tag ); ?>>
		<?php if ( '' !== $args['intro'] ) : ?>
			<p class="mt-5 text-lg <?php echo esc_attr( $cik_hybrid_tone[1] ); ?>"><?php echo esc_html( $args['intro'] ); ?></p>
		<?php endif; ?>
	</div>
	<?php
	if ( $cik_hybrid_link ) {
		cik_hybrid_component(
			'button',
			array(
				'label'   => (string) $args['link']['label'],
				'url'     => (string) $args['link']['url'],
				'variant' => $cik_hybrid_tone[2],
				'icon'    => 'arrow-right',
			)
		);
	}
	?>
</div>
