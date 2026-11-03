<?php
/**
 * Front page. Works for both "Your latest posts" and a static front page.
 *
 * @package Cik_Hybrid
 */

defined( 'ABSPATH' ) || exit;

get_header();

$cik_hybrid_static   = 'page' === get_option( 'show_on_front' ) && have_posts();
$cik_hybrid_blog_url = get_option( 'page_for_posts' ) ? get_permalink( (int) get_option( 'page_for_posts' ) ) : '';

if ( $cik_hybrid_static ) {
	the_post();
}

cik_hybrid_component(
	'hero',
	array(
		'title'    => $cik_hybrid_static ? get_the_title() : get_bloginfo( 'name' ),
		'text'     => $cik_hybrid_static && has_excerpt() ? get_the_excerpt() : get_bloginfo( 'description' ),
		'image_id' => $cik_hybrid_static ? get_post_thumbnail_id() : 0,
		'primary'  => $cik_hybrid_blog_url ? array(
			'label' => __( 'Read the blog', 'cik-hybrid' ),
			'url'   => $cik_hybrid_blog_url,
			'icon'  => 'arrow-right',
		) : array(),
	)
);

if ( $cik_hybrid_static && '' !== trim( get_the_content() ) ) :
	?>
	<div class="cik-prose cik-container py-12">
		<?php the_content(); ?>
	</div>
	<?php
endif;

$cik_hybrid_latest = new WP_Query(
	array(
		'posts_per_page'      => 3,
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	)
);

if ( $cik_hybrid_latest->have_posts() ) :
	?>
	<section class="cik-container py-16" aria-labelledby="cik-latest-title">
		<?php
		cik_hybrid_component(
			'section-heading',
			array(
				'title'   => __( 'Latest articles', 'cik-hybrid' ),
				'eyebrow' => __( 'Blog', 'cik-hybrid' ),
				'id'      => 'cik-latest-title',
			)
		);
		?>
		<div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
			<?php
			while ( $cik_hybrid_latest->have_posts() ) :
				$cik_hybrid_latest->the_post();
				cik_hybrid_component( 'card', array( 'heading_level' => 'h3' ) );
			endwhile;
			wp_reset_postdata();
			?>
		</div>
	</section>
	<?php
endif;

get_footer();
