<?php
/**
 * Default page template.
 *
 * @package Cik_Hybrid
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		<?php
		cik_hybrid_component(
			'page-header',
			array(
				'title'       => get_the_title(),
				'description' => has_excerpt() ? esc_html( get_the_excerpt() ) : '',
				'overlap'     => has_post_thumbnail(),
			)
		);
		?>

		<?php if ( has_post_thumbnail() ) : ?>
			<div class="cik-container relative -mt-20 max-w-wide sm:-mt-32">
				<?php the_post_thumbnail( 'cik-hybrid-hero', array( 'class' => 'aspect-[16/9] w-full rounded-card object-cover shadow-card' ) ); ?>
			</div>
		<?php endif; ?>

		<div class="cik-prose cik-container mt-14 sm:mt-16">
			<?php
			the_content();
			wp_link_pages();
			?>
		</div>
	</article>

	<?php if ( comments_open() || get_comments_number() ) : ?>
		<div class="cik-container mt-16 max-w-content">
			<?php comments_template(); ?>
		</div>
	<?php endif; ?>
	<?php
endwhile;

get_footer();
