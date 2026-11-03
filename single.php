<?php
/**
 * Single post (and any post type without its own template).
 *
 * @package Cik_Hybrid
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$cik_hybrid_categories = get_the_category();
	$cik_hybrid_author     = '';
	if ( get_the_author() ) {
		/* translators: %s: author name. */
		$cik_hybrid_author = '<span aria-hidden="true">&middot;</span><span>' . esc_html( sprintf( __( 'By %s', 'cik-hybrid' ), get_the_author() ) ) . '</span>';
	}
	$cik_hybrid_meta = sprintf(
		'<p class="flex flex-wrap items-center gap-x-3 gap-y-1 text-base"><time datetime="%1$s">%2$s</time>%3$s</p>',
		esc_attr( get_the_date( 'c' ) ),
		esc_html( get_the_date() ),
		$cik_hybrid_author
	);
	?>
	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		<?php
		cik_hybrid_component(
			'page-header',
			array(
				'title'       => get_the_title(),
				'eyebrow'     => ! empty( $cik_hybrid_categories ) ? $cik_hybrid_categories[0]->name : '',
				'description' => $cik_hybrid_meta,
				'overlap'     => has_post_thumbnail(),
			)
		);
		?>

		<?php if ( has_post_thumbnail() ) : ?>
			<div class="cik-container relative -mt-20 max-w-wide sm:-mt-32">
				<?php
				the_post_thumbnail(
					'cik-hybrid-hero',
					array(
						'class'         => 'aspect-[16/9] w-full rounded-card object-cover shadow-card',
						'loading'       => 'eager',
						'fetchpriority' => 'high',
						'sizes'         => '(min-width: 76rem) 72rem, 100vw',
					)
				);
				?>
			</div>
		<?php endif; ?>

		<div class="cik-prose cik-container mt-14 sm:mt-16">
			<?php
			the_content();
			wp_link_pages(
				array(
					'before' => '<nav class="mt-8 font-semibold" aria-label="' . esc_attr__( 'Post pages', 'cik-hybrid' ) . '">' . esc_html__( 'Pages:', 'cik-hybrid' ),
					'after'  => '</nav>',
				)
			);
			?>
		</div>

		<?php if ( has_tag() ) : ?>
			<footer class="cik-container mt-12 max-w-content">
				<?php the_tags( '<p class="flex flex-wrap gap-2 text-sm [&_a]:rounded-full [&_a]:bg-surface [&_a]:px-4 [&_a]:py-1.5 [&_a]:font-semibold [&_a]:text-ink [&_a]:no-underline [&_a:hover]:bg-brand-tint [&_a:hover]:text-accent-strong"><span class="sr-only">' . esc_html__( 'Tags:', 'cik-hybrid' ) . '</span>', ' ', '</p>' ); ?>
			</footer>
		<?php endif; ?>
	</article>

	<div class="cik-container mt-16 max-w-content">
		<?php
		the_post_navigation(
			array(
				'prev_text' => '<span class="mb-1 block font-sans text-sm font-medium text-muted">' . esc_html__( 'Previous post', 'cik-hybrid' ) . '</span> %title',
				'next_text' => '<span class="mb-1 block font-sans text-sm font-medium text-muted">' . esc_html__( 'Next post', 'cik-hybrid' ) . '</span> %title',
				'class'     => 'cik-post-navigation', // Core runs this through sanitize_html_class(): one class only.
			)
		);

		if ( comments_open() || get_comments_number() ) {
			comments_template();
		}
		?>
	</div>
	<?php
endwhile;

get_footer();
