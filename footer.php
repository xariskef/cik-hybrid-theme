<?php
/**
 * Site footer.
 *
 * @package Cik_Hybrid
 */

defined( 'ABSPATH' ) || exit;
?>
</main>

<footer class="mt-16 border-t border-line bg-surface">
	<div class="cik-container flex flex-col gap-6 py-10 md:flex-row md:items-center md:justify-between">
		<?php if ( has_nav_menu( 'footer' ) ) : ?>
			<nav aria-label="<?php esc_attr_e( 'Footer', 'cik-hybrid' ); ?>">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'footer',
						'container'      => false,
						'menu_class'     => 'flex flex-wrap gap-x-6 gap-y-2 text-sm [&_a]:text-muted [&_a]:no-underline [&_a:hover]:text-accent',
						'depth'          => 1,
					)
				);
				?>
			</nav>
		<?php endif; ?>

		<?php
		$cik_hybrid_social = array(
			'facebook'  => array( cik_hybrid_setting( 'social_facebook' ), 'Facebook' ),
			'instagram' => array( cik_hybrid_setting( 'social_instagram' ), 'Instagram' ),
			'linkedin'  => array( cik_hybrid_setting( 'social_linkedin' ), 'LinkedIn' ),
		);
		$cik_hybrid_social = array_filter( $cik_hybrid_social, fn( $link ) => '' !== $link[0] );
		?>
		<?php if ( $cik_hybrid_social ) : ?>
			<ul class="flex gap-2" aria-label="<?php esc_attr_e( 'Social links', 'cik-hybrid' ); ?>">
				<?php foreach ( $cik_hybrid_social as $cik_hybrid_icon => $cik_hybrid_link ) : ?>
					<li>
						<a class="inline-flex size-10 items-center justify-center rounded-md text-muted hover:bg-canvas hover:text-accent" href="<?php echo esc_url( $cik_hybrid_link[0] ); ?>" rel="me noopener">
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

		<p class="text-sm text-muted">
			<?php
			$cik_hybrid_footer_text = cik_hybrid_setting( 'footer_text' );
			if ( '' !== $cik_hybrid_footer_text ) {
				echo wp_kses_post( $cik_hybrid_footer_text );
			} else {
				echo esc_html( sprintf( '© %s %s', wp_date( 'Y' ), get_bloginfo( 'name' ) ) );
			}
			?>
		</p>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
