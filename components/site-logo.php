<?php
/**
 * Component: site logo (custom logo, or the site name as text).
 *
 * @package Cik_Hybrid
 *
 * @var array $args {
 *     @type string $class     Extra CSS classes for the wrapper. Default ''.
 *     @type bool   $show_name Show the site name next to the custom logo. Default true.
 * }
 */

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args(
	$args,
	array(
		'class'     => '',
		'show_name' => true,
	)
);

$cik_hybrid_logo_id = (int) get_theme_mod( 'custom_logo' );
?>
<div class="flex items-center <?php echo esc_attr( $args['class'] ); ?>">
	<a class="flex items-center gap-3 text-xl font-bold tracking-tight text-ink no-underline hover:text-accent" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"<?php echo is_front_page() ? ' aria-current="page"' : ''; ?>>
		<?php
		if ( $cik_hybrid_logo_id ) {
			// One link for logo and name; the image is decorative when the name is printed next to it.
			echo wp_get_attachment_image(
				$cik_hybrid_logo_id,
				'medium',
				false,
				array(
					'class' => 'custom-logo h-10 w-auto',
					'alt'   => $args['show_name'] ? '' : get_bloginfo( 'name' ),
				)
			);
		}

		if ( ! $cik_hybrid_logo_id || $args['show_name'] ) {
			bloginfo( 'name' );
		}
		?>
	</a>
</div>
