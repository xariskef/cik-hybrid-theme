<?php
/**
 * Site header: document head, skip link, branding and primary navigation.
 *
 * The header is a dark "island" that floats over the top of the page, so every
 * template starts with enough top padding (dark page header, hero, or pt-36).
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

<header class="cik-site-header absolute inset-x-0 top-0 z-30 px-3 pt-3 sm:px-4 sm:pt-4">
	<div class="mx-auto flex max-w-[80rem] flex-wrap items-center justify-between gap-x-6 gap-y-3 rounded-2xl bg-night/95 px-4 py-3 text-night-ink shadow-2xl ring-1 ring-night-ink/10 backdrop-blur sm:px-6 lg:flex-nowrap">
		<?php cik_hybrid_component( 'site-logo', array( 'show_name' => '1' === cik_hybrid_setting( 'show_site_name' ) ) ); ?>

		<nav id="cik-primary-nav" class="cik-nav order-last w-full pb-2 lg:order-none lg:ml-auto lg:w-auto lg:pb-0" aria-label="<?php esc_attr_e( 'Primary', 'cik-hybrid' ); ?>">
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
			<button id="cik-menu-toggle" type="button" class="cik-nav-toggle inline-flex size-11 items-center justify-center rounded-full border border-night-line text-night-ink hover:border-night-ink lg:hidden" aria-expanded="false" aria-controls="cik-primary-nav">
				<?php cik_hybrid_component( 'icon', array( 'name' => 'menu' ) ); ?>
				<span class="sr-only"><?php esc_html_e( 'Menu', 'cik-hybrid' ); ?></span>
			</button>
		</div>
	</div>
</header>

<main id="main" class="flex-1" tabindex="-1">
