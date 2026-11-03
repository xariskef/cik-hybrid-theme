<?php
/**
 * Component: site logo (custom logo, or the site name as text).
 *
 * @package Cik_Hybrid
 *
 * @var array $args {
 *     @type string $class Extra CSS classes for the wrapper. Default ''.
 * }
 */

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args( $args, array( 'class' => '' ) );
?>
<div class="flex items-center <?php echo esc_attr( $args['class'] ); ?>">
	<?php if ( has_custom_logo() ) : ?>
		<?php the_custom_logo(); ?>
	<?php else : ?>
		<a class="text-xl font-bold tracking-tight text-ink no-underline hover:text-accent" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"<?php echo is_front_page() ? ' aria-current="page"' : ''; ?>>
			<?php bloginfo( 'name' ); ?>
		</a>
	<?php endif; ?>
</div>
