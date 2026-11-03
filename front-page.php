<?php
/**
 * Front page. Works for both "Your latest posts" and a static front page.
 *
 * Sections, top to bottom: photo hero, page content, feature cards on an
 * accent band, latest articles, the Slider island on a dark photo band, topic
 * tabs, integrations (e.g. products) and a call to action. Every section is
 * built from WordPress content and is skipped when it has nothing to show.
 *
 * @package Cik_Hybrid
 */

defined( 'ABSPATH' ) || exit;

get_header();

$cik_hybrid_static    = 'page' === get_option( 'show_on_front' ) && have_posts();
$cik_hybrid_blog_page = (int) get_option( 'page_for_posts' );
$cik_hybrid_blog_url  = $cik_hybrid_blog_page ? get_permalink( $cik_hybrid_blog_page ) : '';
$cik_hybrid_front_id  = 0;
$cik_hybrid_has_intro = false;

if ( $cik_hybrid_static ) {
	the_post();
	$cik_hybrid_front_id  = get_the_ID();
	$cik_hybrid_has_intro = '' !== trim( get_the_content() );
}

$cik_hybrid_cta = cik_hybrid_front_page_cta();

cik_hybrid_component(
	'hero',
	array(
		'title'     => $cik_hybrid_static ? get_the_title() : get_bloginfo( 'name' ),
		'text'      => $cik_hybrid_static && has_excerpt() ? get_the_excerpt() : get_bloginfo( 'description' ),
		'image_id'  => $cik_hybrid_static ? get_post_thumbnail_id() : 0,
		'primary'   => $cik_hybrid_cta ? array(
			'label' => $cik_hybrid_cta['button']['label'] ?? '',
			'url'   => $cik_hybrid_cta['button']['url'] ?? '',
			'icon'  => 'arrow-right',
		) : array(),
		'secondary' => $cik_hybrid_blog_url ? array(
			'label' => __( 'Read the blog', 'cik-hybrid' ),
			'url'   => $cik_hybrid_blog_url,
		) : array(),
	)
);

if ( $cik_hybrid_has_intro ) :
	?>
	<section class="cik-prose cik-container py-20 sm:py-28" aria-label="<?php esc_attr_e( 'Introduction', 'cik-hybrid' ); ?>">
		<?php the_content(); ?>
	</section>
	<?php
endif;

$cik_hybrid_features = cik_hybrid_front_page_features( $cik_hybrid_front_id );

if ( $cik_hybrid_features ) :
	?>
	<section class="cik-band cik-band--accent" aria-labelledby="cik-features-title">
		<div class="absolute -top-40 -right-40 -z-10 size-[28rem] rounded-full border-[4rem] border-canvas/10" aria-hidden="true"></div>
		<div class="cik-container">
			<?php
			cik_hybrid_component(
				'section-heading',
				array(
					'title'   => __( 'What we do', 'cik-hybrid' ),
					'eyebrow' => __( 'Services', 'cik-hybrid' ),
					'id'      => 'cik-features-title',
					'tone'    => 'accent',
				)
			);
			?>
			<div class="grid gap-6 sm:grid-cols-2 <?php echo count( $cik_hybrid_features ) > 3 ? 'lg:grid-cols-4' : 'lg:grid-cols-3'; ?>">
				<?php
				foreach ( $cik_hybrid_features as $cik_hybrid_index => $cik_hybrid_feature ) {
					cik_hybrid_component( 'feature-card', array_merge( $cik_hybrid_feature, array( 'number' => $cik_hybrid_index + 1 ) ) );
				}
				?>
			</div>
		</div>
	</section>
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
	<section class="cik-container py-20 sm:py-28" aria-labelledby="cik-latest-title">
		<?php
		cik_hybrid_component(
			'section-heading',
			array(
				'title'   => __( 'Latest articles', 'cik-hybrid' ),
				'eyebrow' => __( 'Blog', 'cik-hybrid' ),
				'id'      => 'cik-latest-title',
				'link'    => $cik_hybrid_blog_url ? array(
					'label' => __( 'All articles', 'cik-hybrid' ),
					'url'   => $cik_hybrid_blog_url,
				) : array(),
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

$cik_hybrid_featured = get_posts(
	array(
		'posts_per_page'      => 4,
		'ignore_sticky_posts' => true,
		'meta_key'            => '_thumbnail_id', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Small, front-page-only query.
	)
);

if ( count( $cik_hybrid_featured ) > 1 ) :
	$cik_hybrid_slides = cik_hybrid_slides_from_posts( $cik_hybrid_featured );
	// The band's photo is the featured image of the posts page, when it has one.
	$cik_hybrid_band_image = $cik_hybrid_blog_page ? (int) get_post_thumbnail_id( $cik_hybrid_blog_page ) : 0;
	?>
	<section class="cik-band cik-band--dark mx-3 rounded-[2rem] sm:mx-4" aria-labelledby="cik-featured-title">
		<?php
		if ( $cik_hybrid_band_image ) {
			echo wp_get_attachment_image(
				$cik_hybrid_band_image,
				'cik-hybrid-hero',
				false,
				array(
					'class'   => 'absolute inset-0 -z-20 size-full object-cover opacity-50',
					'alt'     => '',
					'loading' => 'lazy',
					'sizes'   => '100vw',
				)
			);
		}
		?>
		<div class="absolute inset-0 -z-10 bg-gradient-to-b from-night/80 via-night/60 to-night/90" aria-hidden="true"></div>
		<div class="cik-container">
			<?php
			cik_hybrid_component(
				'section-heading',
				array(
					'title'   => __( 'Featured', 'cik-hybrid' ),
					'eyebrow' => __( 'Highlights', 'cik-hybrid' ),
					'id'      => 'cik-featured-title',
					'tone'    => 'dark',
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
			$cik_hybrid_fallback = '<div class="max-w-md">' . (string) ob_get_clean() . '</div>';

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
		</div>
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
	<section class="cik-container pt-20 sm:pt-28" aria-labelledby="cik-topics-title">
		<?php
		cik_hybrid_component(
			'section-heading',
			array(
				'title'   => __( 'Browse by topic', 'cik-hybrid' ),
				'eyebrow' => __( 'Topics', 'cik-hybrid' ),
				'id'      => 'cik-topics-title',
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
 * Fires after the topic tabs. Integrations add their own sections here (WooCommerce: products).
 */
do_action( 'cik_hybrid_front_page_sections' );

if ( $cik_hybrid_cta ) {
	cik_hybrid_component( 'cta-band', $cik_hybrid_cta );
}

get_footer();
