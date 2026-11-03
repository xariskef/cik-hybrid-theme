<?php
/**
 * Not found.
 *
 * @package Cik_Hybrid
 */

defined( 'ABSPATH' ) || exit;

get_header();

cik_hybrid_component(
	'page-header',
	array(
		'eyebrow'     => __( 'Error 404', 'cik-hybrid' ),
		'title'       => __( 'Page not found', 'cik-hybrid' ),
		'description' => __( 'The page you are looking for does not exist or has moved.', 'cik-hybrid' ),
	)
);
?>
<div class="cik-container pt-16">
	<div class="max-w-2xl space-y-8">
		<?php get_search_form(); ?>
		<?php
		cik_hybrid_component(
			'button',
			array(
				'label' => __( 'Back to the homepage', 'cik-hybrid' ),
				'url'   => home_url( '/' ),
				'icon'  => 'arrow-right',
			)
		);
		?>
	</div>
</div>
<?php
get_footer();
