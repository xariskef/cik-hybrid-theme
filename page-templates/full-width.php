<?php
/**
 * Template Name: Full width
 * Template Post Type: page, post
 *
 * Content spans the wide container; blocks set to "full width" reach the viewport edges.
 *
 * @package Cik_Hybrid
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		<?php cik_hybrid_component( 'page-header', array( 'title' => get_the_title() ) ); ?>
		<div class="cik-prose mt-10 px-4 sm:px-6 [&>*]:max-w-wide [&>.alignfull]:max-w-none">
			<?php the_content(); ?>
		</div>
	</article>
	<?php
endwhile;

get_footer();
