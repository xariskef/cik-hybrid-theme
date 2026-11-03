<?php
/**
 * Component: pill button (a link styled as a button, or a real <button>).
 *
 * @package Cik_Hybrid
 *
 * @var array $args {
 *     @type string $label   Visible text. Required.
 *     @type string $url     Link target. Empty renders a <button>. Default ''.
 *     @type string $variant 'primary', 'secondary', 'light', 'outline-light' (on dark bands) or 'ghost'. Default 'primary'.
 *     @type string $icon    Optional trailing icon name. Default ''.
 *     @type string $type    Button type when no URL is given. Default 'button'.
 *     @type string $class   Extra CSS classes. Default ''.
 * }
 */

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args(
	$args,
	array(
		'label'   => '',
		'url'     => '',
		'variant' => 'primary',
		'icon'    => '',
		'type'    => 'button',
		'class'   => '',
	)
);

if ( '' === $args['label'] ) {
	return;
}

// Full class names so Tailwind's scanner sees them; styles live in src/css/app.css.
$cik_hybrid_variants = array(
	'primary'       => 'cik-btn--primary',
	'secondary'     => 'cik-btn--secondary',
	'light'         => 'cik-btn--light',
	'outline-light' => 'cik-btn--outline-light',
	'ghost'         => 'cik-btn--ghost',
);
$cik_hybrid_classes  = 'cik-btn ' . ( $cik_hybrid_variants[ $args['variant'] ] ?? $cik_hybrid_variants['primary'] ) . ' ' . $args['class'];
$cik_hybrid_icon     = '' !== $args['icon'] ? cik_hybrid_get_icon( $args['icon'], array( 'class' => 'size-4' ) ) : '';

if ( '' !== $args['url'] ) :
	?>
	<a class="<?php echo esc_attr( trim( $cik_hybrid_classes ) ); ?>" href="<?php echo esc_url( $args['url'] ); ?>">
		<?php echo esc_html( $args['label'] ); ?>
		<?php echo wp_kses( $cik_hybrid_icon, cik_hybrid_kses_svg() ); ?>
	</a>
<?php else : ?>
	<button class="<?php echo esc_attr( trim( $cik_hybrid_classes ) ); ?>" type="<?php echo esc_attr( in_array( $args['type'], array( 'submit', 'reset' ), true ) ? $args['type'] : 'button' ); ?>">
		<?php echo esc_html( $args['label'] ); ?>
		<?php echo wp_kses( $cik_hybrid_icon, cik_hybrid_kses_svg() ); ?>
	</button>
	<?php
endif;
