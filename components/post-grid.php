<?php
/**
 * Component: grid of post cards for the main query, with pagination.
 *
 * @package Cik_Hybrid
 *
 * @var array $args {
 *     @type string $empty_message Text shown when the query has no posts. Default 'Nothing found.'.
 *     @type bool   $search_form   Whether to show the search form when empty. Default true.
 * }
 */

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args(
	$args,
	array(
		'empty_message' => __( 'Nothing found.', 'cik-hybrid' ),
		'search_form'   => true,
	)
);
?>
<div class="cik-container py-16 sm:py-20">
	<?php if ( have_posts() ) : ?>
		<div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
			<?php
			while ( have_posts() ) :
				the_post();
				cik_hybrid_component( 'card', array( 'heading_level' => 'h2' ) );
			endwhile;
			?>
		</div>
		<?php cik_hybrid_component( 'pagination' ); ?>
	<?php else : ?>
		<div class="max-w-2xl rounded-card bg-surface p-8 sm:p-10">
			<p class="font-display text-2xl font-bold text-ink"><?php echo esc_html( $args['empty_message'] ); ?></p>
			<?php if ( $args['search_form'] ) : ?>
				<div class="mt-6"><?php get_search_form(); ?></div>
			<?php endif; ?>
		</div>
	<?php endif; ?>
</div>
