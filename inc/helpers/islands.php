<?php
/**
 * React islands: opt-in interactivity mounted into server-rendered pages.
 *
 * Islands are meant for widgets and landing-page extras where search engines
 * do not need the content. Anything that should be indexed is rendered by PHP.
 *
 * @package Cik_Hybrid
 */

defined( 'ABSPATH' ) || exit;

/**
 * Prints an island container and enqueues the islands loader.
 *
 * The loader (src/js/islands.ts) is enqueued on first use only, so pages
 * without islands never download React. Classic themes print script modules
 * in wp_footer, so enqueueing here, in the middle of the template, still works.
 *
 * Props are JSON-encoded into a data attribute: never put secrets in them, and
 * sanitise URLs before passing them (esc_url_raw() with http/https protocols).
 *
 * @param string               $component     Component file name in src/islands/, e.g. 'Slider'.
 * @param array<string, mixed> $props         Props passed to the component.
 * @param string               $fallback_html Optional markup shown until the island mounts (and without JS).
 *                                            Filtered with wp_kses_post(). Empty prints an accessible
 *                                            loading placeholder.
 */
function cik_hybrid_island( string $component, array $props = array(), string $fallback_html = '' ): void {
	if ( ! preg_match( '/^[A-Z][A-Za-z0-9]*$/', $component ) ) {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Island names must be PascalCase component file names.', 'cik-hybrid' ), '1.0.0' );
		return;
	}

	static $loader_enqueued = false;
	if ( ! $loader_enqueued ) {
		$loader_enqueued = cik_hybrid_enqueue_vite_entry( 'src/js/islands.ts' );
	}

	$has_fallback = '' !== trim( $fallback_html );

	printf(
		'<div class="cik-island" data-island="%1$s" data-props="%2$s"%3$s>',
		esc_attr( $component ),
		esc_attr( (string) wp_json_encode( $props ) ),
		$has_fallback ? '' : ' aria-busy="true"'
	);

	if ( $has_fallback ) {
		echo wp_kses_post( $fallback_html );
	} else {
		printf(
			'<p class="py-6 text-center text-muted" role="status">%s</p>',
			esc_html__( 'Loading&hellip;', 'cik-hybrid' )
		);
	}

	echo '</div>';
}

/**
 * Builds Slider props from posts. URLs are passed through esc_url_raw() with
 * http/https only, because the value ends up in an href inside React.
 *
 * @param WP_Post[] $posts Posts to show as slides.
 * @return array<int, array<string, mixed>>
 */
function cik_hybrid_slides_from_posts( array $posts ): array {
	$slides = array();

	foreach ( $posts as $slide_post ) {
		$slide = array(
			'title' => wp_strip_all_tags( get_the_title( $slide_post ) ),
			'text'  => wp_trim_words( get_the_excerpt( $slide_post ), 24 ),
			'url'   => esc_url_raw( get_permalink( $slide_post ), array( 'http', 'https' ) ),
		);

		$image_id = get_post_thumbnail_id( $slide_post );
		$image    = $image_id ? wp_get_attachment_image_src( $image_id, 'cik-hybrid-hero' ) : false;
		if ( $image ) {
			$slide['image'] = array(
				'src'    => esc_url_raw( $image[0], array( 'http', 'https' ) ),
				'srcset' => (string) wp_get_attachment_image_srcset( $image_id, 'cik-hybrid-hero' ),
				'alt'    => (string) get_post_meta( $image_id, '_wp_attachment_image_alt', true ),
				'width'  => (int) $image[1],
				'height' => (int) $image[2],
			);
		}

		$slides[] = $slide;
	}

	return $slides;
}
