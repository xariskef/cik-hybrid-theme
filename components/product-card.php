<?php
/**
 * Component: WooCommerce product card.
 *
 * The add-to-cart link uses WooCommerce's own classes and data attributes, so
 * its AJAX handler and the cart fragments work without extra code.
 *
 * @package Cik_Hybrid
 *
 * @var array $args {
 *     @type WC_Product|int $product       Product object or ID. Required.
 *     @type string         $heading_level Title tag. Default 'h3'.
 * }
 */

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args(
	$args,
	array(
		'product'       => null,
		'heading_level' => 'h3',
	)
);

$product = $args['product'] instanceof WC_Product ? $args['product'] : wc_get_product( $args['product'] );
if ( ! $product instanceof WC_Product || ! $product->is_visible() ) {
	return;
}

$heading_tag = cik_hybrid_heading_tag( (string) $args['heading_level'], 'h3' );
$ajax        = $product->supports( 'ajax_add_to_cart' ) && $product->is_purchasable() && $product->is_in_stock();
?>
<article class="group relative flex flex-col overflow-hidden rounded-card border border-line bg-canvas">
	<div class="aspect-square overflow-hidden bg-surface">
		<?php
		$image_attrs = array(
			'class' => 'size-full object-cover transition-transform duration-300 group-hover:scale-105',
			'sizes' => '(min-width: 64rem) 17rem, (min-width: 40rem) 50vw, 100vw',
		);
		if ( $product->get_image_id() ) {
			echo wp_get_attachment_image( (int) $product->get_image_id(), 'woocommerce_thumbnail', false, $image_attrs );
		} else {
			echo wp_kses_post( wc_placeholder_img( 'woocommerce_thumbnail', $image_attrs ) );
		}
		?>
	</div>
	<div class="flex flex-1 flex-col gap-2 p-5">
		<?php if ( $product->is_on_sale() ) : ?>
			<p class="text-xs font-semibold tracking-wide text-accent uppercase"><?php esc_html_e( 'On sale', 'cik-hybrid' ); ?></p>
		<?php endif; ?>
		<<?php echo esc_attr( $heading_tag ); ?> class="text-lg font-semibold">
			<a class="text-ink no-underline hover:text-accent" href="<?php echo esc_url( $product->get_permalink() ); ?>">
				<?php echo esc_html( $product->get_name() ); ?>
			</a>
		</<?php echo esc_attr( $heading_tag ); ?>>
		<p class="text-muted"><?php echo wp_kses_post( $product->get_price_html() ); ?></p>
		<p class="mt-auto pt-2">
			<a
				href="<?php echo esc_url( $product->add_to_cart_url() ); ?>"
				class="<?php echo esc_attr( 'button product_type_' . $product->get_type() . ( $ajax ? ' add_to_cart_button ajax_add_to_cart' : '' ) . ' inline-flex rounded-md bg-accent px-4 py-2 font-semibold text-accent-ink no-underline hover:bg-accent-strong' ); ?>"
				data-quantity="1"
				data-product_id="<?php echo esc_attr( (string) $product->get_id() ); ?>"
				data-product_sku="<?php echo esc_attr( $product->get_sku() ); ?>"
				aria-label="<?php echo esc_attr( wp_strip_all_tags( $product->add_to_cart_description() ) ); ?>"
				rel="nofollow"
			><?php echo esc_html( $product->add_to_cart_text() ); ?></a>
		</p>
	</div>
</article>
