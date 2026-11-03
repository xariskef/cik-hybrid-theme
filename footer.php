<?php
/**
 * Site footer: dark band with logo, tagline, footer menu, social links and footer text.
 *
 * @package Cik_Hybrid
 */

defined( 'ABSPATH' ) || exit;

$cik_hybrid_social = array(
	'facebook'  => array( cik_hybrid_setting( 'social_facebook' ), 'Facebook' ),
	'instagram' => array( cik_hybrid_setting( 'social_instagram' ), 'Instagram' ),
	'linkedin'  => array( cik_hybrid_setting( 'social_linkedin' ), 'LinkedIn' ),
);
$cik_hybrid_social = array_filter( $cik_hybrid_social, fn( $link ) => '' !== $link[0] );
?>
</main>

<footer class="mt-24 bg-night text-night-muted">
	<div class="cik-container grid gap-12 pt-16 pb-10 sm:pt-20 lg:grid-cols-[1.4fr_1fr]">
		<div class="max-w-md">
			<?php cik_hybrid_component( 'site-logo', array( 'show_name' => '1' === cik_hybrid_setting( 'show_site_name' ) ) ); ?>
			<?php if ( '' !== get_bloginfo( 'description' ) ) : ?>
				<p class="mt-5 text-lg"><?php bloginfo( 'description' ); ?></p>
			<?php endif; ?>

			<?php if ( $cik_hybrid_social ) : ?>
				<ul class="mt-8 flex gap-3" aria-label="<?php esc_attr_e( 'Social links', 'cik-hybrid' ); ?>">
					<?php foreach ( $cik_hybrid_social as $cik_hybrid_icon => $cik_hybrid_link ) : ?>
						<li>
							<a class="inline-flex size-11 items-center justify-center rounded-full border border-night-line text-night-ink no-underline transition-colors hover:border-brand hover:bg-brand hover:text-accent-ink" href="<?php echo esc_url( $cik_hybrid_link[0] ); ?>" rel="me noopener">
								<?php
								cik_hybrid_component(
									'icon',
									array(
										'name'  => $cik_hybrid_icon,
										'label' => $cik_hybrid_link[1],
									)
								);
								?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>

		<?php if ( has_nav_menu( 'footer' ) ) : ?>
			<nav aria-label="<?php esc_attr_e( 'Footer', 'cik-hybrid' ); ?>">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'footer',
						'container'      => false,
						'menu_class'     => 'grid grid-cols-2 gap-x-8 gap-y-3 font-medium [&_a]:text-night-ink [&_a]:no-underline [&_a:hover]:text-brand-bright',
						'depth'          => 1,
					)
				);
				?>
			</nav>
		<?php endif; ?>
	</div>

	<div class="cik-container">
		<div class="flex flex-col gap-2 border-t border-night-line py-8 text-sm sm:flex-row sm:items-center sm:justify-between [&_a]:text-night-ink [&_a:hover]:text-brand-bright">
			<p>
				<?php
				$cik_hybrid_footer_text = cik_hybrid_setting( 'footer_text' );
				if ( '' !== $cik_hybrid_footer_text ) {
					echo wp_kses_post( $cik_hybrid_footer_text );
				} else {
					echo esc_html( sprintf( '© %s %s', wp_date( 'Y' ), get_bloginfo( 'name' ) ) );
				}
				?>
			</p>
			<?php
			$cik_hybrid_credit = cik_hybrid_footer_credit();
			if ( '' !== $cik_hybrid_credit ) :
				?>
				<p class="cik-credit"><?php echo wp_kses_post( $cik_hybrid_credit ); ?></p>
			<?php endif; ?>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
