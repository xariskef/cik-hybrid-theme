<?php
/**
 * Small template helpers.
 *
 * @package Cik_Hybrid
 */

defined( 'ABSPATH' ) || exit;

/**
 * Theme credit for the footer, e.g. "Theme by Cikefallinos" with a link.
 * Filter `cik_hybrid_footer_credit` and return '' to remove it.
 *
 * @return string HTML, already escaped.
 */
function cik_hybrid_footer_credit(): string {
	$credit = sprintf(
		/* translators: %s: link to the theme author's website. */
		esc_html__( 'Theme by %s', 'cik-hybrid' ),
		'<a href="' . esc_url( 'https://cikefallinos.com' ) . '">Cikefallinos</a>'
	);

	/**
	 * Filters the footer credit HTML. Return an empty string to hide it.
	 *
	 * @param string $credit Credit HTML.
	 */
	return (string) apply_filters( 'cik_hybrid_footer_credit', $credit );
}
