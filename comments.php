<?php
/**
 * Comments list and form.
 *
 * @package Cik_Hybrid
 */

defined( 'ABSPATH' ) || exit;

if ( post_password_required() ) {
	return;
}
?>
<section id="comments" class="cik-comments mt-16 border-t border-line pt-12" aria-labelledby="comments-title">
	<?php if ( have_comments() ) : ?>
		<h2 id="comments-title" class="text-3xl">
			<?php
			$cik_hybrid_count = get_comments_number();
			printf(
				/* translators: %s: number of comments. */
				esc_html( _n( '%s comment', '%s comments', $cik_hybrid_count, 'cik-hybrid' ) ),
				esc_html( number_format_i18n( $cik_hybrid_count ) )
			);
			?>
		</h2>

		<ol class="mt-8 space-y-6 [&_.children]:mt-6 [&_.children]:ml-6 [&_.children]:space-y-6 [&_.comment-content]:mt-3 [&_.comment-meta]:text-sm [&_.comment-meta]:text-muted [&_.avatar]:mr-3 [&_.avatar]:rounded-full">
			<?php
			wp_list_comments(
				array(
					'style'       => 'ol',
					'short_ping'  => true,
					'avatar_size' => 40,
				)
			);
			?>
		</ol>

		<?php the_comments_navigation(); ?>
	<?php else : ?>
		<h2 id="comments-title" class="sr-only"><?php esc_html_e( 'Comments', 'cik-hybrid' ); ?></h2>
	<?php endif; ?>

	<?php if ( ! comments_open() && get_comments_number() ) : ?>
		<p class="mt-6 text-muted"><?php esc_html_e( 'Comments are closed.', 'cik-hybrid' ); ?></p>
	<?php endif; ?>

	<?php
	comment_form(
		array(
			'class_container'    => 'mt-10',
			'title_reply_before' => '<h2 id="reply-title" class="text-2xl">',
			'title_reply_after'  => '</h2>',
			'class_submit'       => 'cik-btn cik-btn--primary mt-2 cursor-pointer',
		)
	);
	?>
</section>
