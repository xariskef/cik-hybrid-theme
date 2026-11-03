<?php
/**
 * Template Name: Landing page
 *
 * Hero built from the page title, excerpt and featured image, then the content
 * with no breadcrumbs or comments. A good place for React islands.
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
			'hero',
			array(
				'title'    => get_the_title(),
				'text'     => has_excerpt() ? get_the_excerpt() : '',
				'image_id' => get_post_thumbnail_id(),
			)
		);
		?>
		<div class="cik-prose cik-container pt-20 sm:pt-24">
			<?php the_content(); ?>
		</div>
	</article>
	<?php
endwhile;

get_footer();
