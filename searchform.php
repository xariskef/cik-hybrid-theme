<?php
/**
 * Search form.
 *
 * @package Cik_Hybrid
 */

defined( 'ABSPATH' ) || exit;

$cik_hybrid_field_id = wp_unique_id( 'cik-search-' );
?>
<form role="search" method="get" class="flex gap-2" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="sr-only" for="<?php echo esc_attr( $cik_hybrid_field_id ); ?>"><?php esc_html_e( 'Search for:', 'cik-hybrid' ); ?></label>
	<input id="<?php echo esc_attr( $cik_hybrid_field_id ); ?>" class="min-w-0 flex-1 rounded-md border border-line bg-canvas px-4 py-3" type="search" name="s" value="<?php echo esc_attr( get_search_query( false ) ); ?>" placeholder="<?php esc_attr_e( 'Search&hellip;', 'cik-hybrid' ); ?>">
	<button class="inline-flex items-center gap-2 rounded-md bg-accent px-5 py-3 font-semibold text-accent-ink hover:bg-accent-strong" type="submit">
		<?php cik_hybrid_component( 'icon', array( 'name' => 'search' ) ); ?>
		<span class="sr-only sm:not-sr-only"><?php esc_html_e( 'Search', 'cik-hybrid' ); ?></span>
	</button>
</form>
