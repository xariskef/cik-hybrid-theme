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

		<p class="text-sm text-muted">
			&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>
		</p>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
