<?php
/**
 * WooCommerce integration. Loaded only when WooCommerce is active.
 *
 * The shop keeps WooCommerce's own templates and hooks, so extensions that
 * rely on them keep working; the theme only supplies the page wrapper, a
 * header cart link and a product card for theme-controlled sections.
 *
 * @package Cik_Hybrid
 */

defined( 'ABSPATH' ) || exit;

/**
 * Declares WooCommerce support and gallery features.
 */
function cik_hybrid_woocommerce_setup(): void {
	add_theme_support(
		'woocommerce',
		array(
			'thumbnail_image_width' => 600,
			'single_image_width'    => 900,
			'product_grid'          => array(
				'default_columns' => 3,
				'min_columns'     => 1,
				'max_columns'     => 4,
			),
		)
	);
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );
}
add_action( 'after_setup_theme', 'cik_hybrid_woocommerce_setup' );

remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );

/**
 * Opens the theme's content wrapper around WooCommerce pages.
 */
function cik_hybrid_woocommerce_wrapper_start(): void {
	echo '<div class="cik-container cik-woocommerce py-12">';
}
add_action( 'woocommerce_before_main_content', 'cik_hybrid_woocommerce_wrapper_start', 10 );

/**
 * Closes the content wrapper.
 */
function cik_hybrid_woocommerce_wrapper_end(): void {
	echo '</div>';
}
add_action( 'woocommerce_after_main_content', 'cik_hybrid_woocommerce_wrapper_end', 10 );

/**
 * Gives WooCommerce breadcrumbs the same landmark and look as the theme's.
 *
 * @param array<string, string> $defaults Breadcrumb arguments.
 * @return array<string, string>
 */
function cik_hybrid_woocommerce_breadcrumb_defaults( array $defaults ): array {
	$defaults['wrap_before'] = '<nav class="woocommerce-breadcrumb mb-6 text-sm text-muted" aria-label="' . esc_attr__( 'Breadcrumb', 'cik-hybrid' ) . '">';
	$defaults['wrap_after']  = '</nav>';
	$defaults['delimiter']   = '<span class="mx-2" aria-hidden="true">/</span>';

	return $defaults;
}
add_filter( 'woocommerce_breadcrumb_defaults', 'cik_hybrid_woocommerce_breadcrumb_defaults' );

/**
 * Header cart link markup. Also used as the AJAX fragment, so it must be
 * self-contained and keep the `cik-cart-link` class that the fragment targets.
 *
 * @return string
 */
function cik_hybrid_cart_link(): string {
	$count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;

	return sprintf(
		'<a class="cik-cart-link relative inline-flex size-10 items-center justify-center rounded-md border border-line text-ink hover:border-accent hover:text-accent" href="%1$s">%2$s<span class="sr-only">%3$s</span><span class="absolute -top-1.5 -right-1.5 min-w-5 rounded-full bg-accent px-1 text-center text-xs font-semibold text-accent-ink%4$s" aria-hidden="true">%5$s</span></a>',
		esc_url( wc_get_cart_url() ),
		cik_hybrid_get_icon( 'cart' ),
		/* translators: %d: number of items in the cart. */
		esc_html( sprintf( _n( 'Cart, %d item', 'Cart, %d items', $count, 'cik-hybrid' ), $count ) ),
		$count ? '' : ' hidden',
		esc_html( number_format_i18n( $count ) )
	);
}

/**
 * Prints the cart link in the header.
 */
function cik_hybrid_header_cart(): void {
	echo wp_kses( cik_hybrid_cart_link(), cik_hybrid_cart_link_kses() );
}
add_action( 'cik_hybrid_header_actions', 'cik_hybrid_header_cart' );

/**
 * Allowed markup for the cart link.
 *
 * @return array<string, array<string, bool>>
 */
function cik_hybrid_cart_link_kses(): array {
	return array_merge(
		cik_hybrid_kses_svg(),
		array(
			'a'    => array(
				'class' => true,
				'href'  => true,
			),
			'span' => array(
				'class'       => true,
				'aria-hidden' => true,
			),
		)
	);
}

/**
 * Refreshes the header cart link after AJAX add-to-cart.
 *
 * @param array<string, string> $fragments Selector => replacement HTML.
 * @return array<string, string>
 */
function cik_hybrid_cart_fragment( array $fragments ): array {
	$fragments['a.cik-cart-link'] = cik_hybrid_cart_link();
	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'cik_hybrid_cart_fragment' );

/**
 * WooCommerce no longer loads the cart fragments script everywhere; the
 * header count needs it to stay correct on cached pages and after AJAX adds.
 */
function cik_hybrid_woocommerce_scripts(): void {
	wp_enqueue_script( 'wc-cart-fragments' );
}
add_action( 'wp_enqueue_scripts', 'cik_hybrid_woocommerce_scripts' );

/**
 * Adds a "From the shop" section to the front page.
 */
function cik_hybrid_front_page_products(): void {
	$products = wc_get_products(
		array(
			'status'     => 'publish',
			'limit'      => 4,
			'visibility' => 'catalog',
			'orderby'    => 'date',
			'order'      => 'DESC',
		)
	);

	if ( empty( $products ) ) {
		return;
	}
	?>
	<section class="cik-container pt-16" aria-labelledby="cik-products-title">
		<?php
		cik_hybrid_component(
			'section-heading',
			array(
				'title'   => __( 'From the shop', 'cik-hybrid' ),
				'eyebrow' => __( 'Shop', 'cik-hybrid' ),
				'id'      => 'cik-products-title',
			)
		);
		?>
		<div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
			<?php
			foreach ( $products as $product ) {
				cik_hybrid_component( 'product-card', array( 'product' => $product ) );
			}
			?>
		</div>
	</section>
	<?php
}
add_action( 'cik_hybrid_front_page_sections', 'cik_hybrid_front_page_products' );
