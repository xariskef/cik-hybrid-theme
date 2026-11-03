<?php
/**
 * Site header: document head, skip link, branding and primary navigation.
 *
 * @package Cik_Hybrid
 */

defined( 'ABSPATH' ) || exit;
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>

<body <?php body_class( 'flex min-h-screen flex-col' ); ?>>
<?php wp_body_open(); ?>
<a class="cik-skip-link" href="#main"><?php esc_html_e( 'Skip to content', 'cik-hybrid' ); ?></a>

<header class="relative z-30 border-b border-line bg-canvas">
	<div class="cik-container flex flex-wrap items-center justify-between gap-x-6 gap-y-3 py-4 lg:flex-nowrap">
		<?php cik_hybrid_component( 'site-logo' ); ?>

		<nav id="cik-primary-nav" class="cik-nav order-last w-full lg:order-none lg:ml-auto lg:w-auto" aria-label="<?php esc_attr_e( 'Primary', 'cik-hybrid' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'cik-menu',
					'depth'          => 3,
					'walker'         => new Cik_Hybrid_Walker_Nav(),
					'fallback_cb'    => array( Cik_Hybrid_Walker_Nav::class, 'fallback' ),
				)
			);
			?>
		</nav>

		<div class="flex items-center gap-2">
			<?php
			/**
			 * Fires inside the header, next to the menu toggle. Used for the WooCommerce cart link.
			 */
			do_action( 'cik_hybrid_header_actions' );
			?>
			<button id="cik-menu-toggle" type="button" class="cik-nav-toggle inline-flex size-10 items-center justify-center rounded-md border border-line text-ink lg:hidden" aria-expanded="false" aria-controls="cik-primary-nav">
				<?php cik_hybrid_component( 'icon', array( 'name' => 'menu' ) ); ?>
				<span class="sr-only"><?php esc_html_e( 'Menu', 'cik-hybrid' ); ?></span>
			</button>
		</div>
	</div>
</header>

<main id="main" class="flex-1" tabindex="-1">
