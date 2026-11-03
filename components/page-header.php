<?php
/**
 * Component: page header (breadcrumbs, title, optional description).
 *
 * @package Cik_Hybrid
 *
 * @var array $args {
 *     @type string $title       Page title. Required.
 *     @type string $description HTML description, filtered with wp_kses_post(). Default ''.
 *     @type bool   $breadcrumbs Whether to print breadcrumbs. Default true.
 * }
 */

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args(
	$args,
	array(
		'title'       => '',
		'description' => '',
		'breadcrumbs' => true,
	)
);
?>
<header class="border-b border-line bg-surface">
	<div class="cik-container py-10 sm:py-14">
		<?php
		if ( $args['breadcrumbs'] ) {
			cik_hybrid_component( 'breadcrumbs', array( 'class' => 'mb-4' ) );
		}
		?>
		<h1 class="max-w-4xl text-4xl font-bold tracking-tight text-balance sm:text-5xl"><?php echo esc_html( $args['title'] ); ?></h1>
		<?php if ( '' !== trim( (string) $args['description'] ) ) : ?>
			<div class="mt-4 max-w-content text-lg text-muted"><?php echo wp_kses_post( $args['description'] ); ?></div>
		<?php endif; ?>
	</div>
</header>
