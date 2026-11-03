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
	?>
	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		<header class="cik-container max-w-content pt-10 sm:pt-14">
			<?php cik_hybrid_component( 'breadcrumbs', array( 'class' => 'mb-6' ) ); ?>
			<?php the_title( '<h1 class="text-4xl font-bold tracking-tight text-balance sm:text-5xl">', '</h1>' ); ?>
			<p class="mt-4 flex flex-wrap items-center gap-x-3 text-muted">
				<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
				<span aria-hidden="true">&middot;</span>
				<span>
					<?php
					/* translators: %s: author name. */
					printf( esc_html__( 'By %s', 'cik-hybrid' ), esc_html( get_the_author() ) );
					?>
				</span>
			</p>
		</header>

		<?php if ( has_post_thumbnail() ) : ?>
			<figure class="cik-container mt-10 max-w-wide">
				<?php
				the_post_thumbnail(
					'cik-hybrid-hero',
					array(
						'class'         => 'w-full rounded-card',
						'loading'       => 'eager',
						'fetchpriority' => 'high',
					)
				);
				?>
			</figure>
		<?php endif; ?>

		<div class="cik-prose cik-container mt-10">
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

		<footer class="cik-container mt-10 max-w-content">
			<?php the_tags( '<p class="flex flex-wrap gap-2 text-sm"><span class="sr-only">' . esc_html__( 'Tags:', 'cik-hybrid' ) . '</span>', ' ', '</p>' ); ?>
		</footer>
	</article>

	<div class="cik-container mt-12 max-w-content">
		<?php
		the_post_navigation(
			array(
				'prev_text' => '<span class="block text-sm text-muted">' . esc_html__( 'Previous post', 'cik-hybrid' ) . '</span> %title',
				'next_text' => '<span class="block text-sm text-muted">' . esc_html__( 'Next post', 'cik-hybrid' ) . '</span> %title',
				'class'     => 'border-t border-line pt-8 [&_.nav-links]:grid [&_.nav-links]:gap-6 sm:[&_.nav-links]:grid-cols-2 [&_.nav-next]:sm:text-right',
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
