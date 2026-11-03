<?php
/**
 * Front page. Works for both "Your latest posts" and a static front page.
 *
 * The featured slider and the topic tabs are React islands (demo of opt-in
 * interactivity); the hero, page content and latest articles are plain PHP.
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

$cik_hybrid_featured = get_posts(
	array(
		'posts_per_page'      => 4,
		'ignore_sticky_posts' => true,
		'meta_key'            => '_thumbnail_id', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Small, front-page-only query.
	)
);

if ( count( $cik_hybrid_featured ) > 1 ) :
	$cik_hybrid_slides = cik_hybrid_slides_from_posts( $cik_hybrid_featured );
	?>
	<section class="cik-container pt-16" aria-labelledby="cik-featured-title">
		<?php
		cik_hybrid_component(
			'section-heading',
			array(
				'title'   => __( 'Featured', 'cik-hybrid' ),
				'eyebrow' => __( 'Highlights', 'cik-hybrid' ),
				'id'      => 'cik-featured-title',
			)
		);

		// Until the island mounts (or without JS), show the first slide as a plain card.
		ob_start();
		cik_hybrid_component(
			'card',
			array(
				'post'          => $cik_hybrid_featured[0],
				'heading_level' => 'h3',
			)
		);
		$cik_hybrid_fallback = (string) ob_get_clean();

		cik_hybrid_island(
			'Slider',
			array(
				'slides'   => $cik_hybrid_slides,
				'interval' => 6000,
				'labels'   => array(
					'region'   => __( 'Featured articles', 'cik-hybrid' ),
					'start'    => __( 'Start automatic slide show', 'cik-hybrid' ),
					'stop'     => __( 'Stop automatic slide show', 'cik-hybrid' ),
					'previous' => __( 'Previous slide', 'cik-hybrid' ),
					'next'     => __( 'Next slide', 'cik-hybrid' ),
					/* translators: 1: slide number, 2: total slides. */
					'slide'    => __( '%1$s of %2$s', 'cik-hybrid' ),
					/* translators: 1: slide number, 2: slide title. */
					'goTo'     => __( 'Show slide %1$s: %2$s', 'cik-hybrid' ),
					'readMore' => __( 'Read the article', 'cik-hybrid' ),
				),
			),
			$cik_hybrid_fallback
		);
		?>
	</section>
	<?php
endif;

$cik_hybrid_tabs = array();
foreach ( get_categories(
	array(
		'orderby'    => 'count',
		'order'      => 'DESC',
		'number'     => 3,
		'hide_empty' => true,
	)
) as $cik_hybrid_category ) {
	$cik_hybrid_items = array();
	foreach ( get_posts(
		array(
			'cat'            => $cik_hybrid_category->term_id,
			'posts_per_page' => 4,
		)
	) as $cik_hybrid_item ) {
		$cik_hybrid_items[] = array(
			'title' => wp_strip_all_tags( get_the_title( $cik_hybrid_item ) ),
			'url'   => esc_url_raw( get_permalink( $cik_hybrid_item ), array( 'http', 'https' ) ),
			'meta'  => get_the_date( '', $cik_hybrid_item ),
		);
	}

	$cik_hybrid_tabs[] = array(
		'label'     => $cik_hybrid_category->name,
		'items'     => $cik_hybrid_items,
		'moreUrl'   => esc_url_raw( get_category_link( $cik_hybrid_category ), array( 'http', 'https' ) ),
		/* translators: %s: category name. */
		'moreLabel' => sprintf( __( 'All posts in %s', 'cik-hybrid' ), $cik_hybrid_category->name ),
	);
}

if ( count( $cik_hybrid_tabs ) > 1 ) :
	?>
	<section class="cik-container pt-16" aria-labelledby="cik-topics-title">
		<?php
		cik_hybrid_component(
			'section-heading',
			array(
				'title' => __( 'Browse by topic', 'cik-hybrid' ),
				'id'    => 'cik-topics-title',
			)
		);

		cik_hybrid_island(
			'Tabs',
			array(
				'tabs'  => $cik_hybrid_tabs,
				'label' => __( 'Topics', 'cik-hybrid' ),
			)
		);
		?>
	</section>
	<?php
endif;

/**
 * Fires before the latest articles section. Integrations add their own sections here.
 */
do_action( 'cik_hybrid_front_page_sections' );

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
