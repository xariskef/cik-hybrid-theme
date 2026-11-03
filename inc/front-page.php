<?php
/**
 * Data for the front page sections. Everything comes from WordPress content,
 * so the template only decides the layout.
 *
 * @package Cik_Hybrid
 */

defined( 'ABSPATH' ) || exit;

/**
 * Feature cards: the published child pages of the static front page, in menu
 * order (title, excerpt, link). Filter `cik_hybrid_front_page_features` to use
 * anything else.
 *
 * @param int $front_page_id ID of the static front page, 0 when the front page lists posts.
 * @return array<int, array{title: string, text: string, url: string}>
 */
function cik_hybrid_front_page_features( int $front_page_id ): array {
	$features = array();

	if ( $front_page_id > 0 ) {
		$children = get_posts(
			array(
				'post_type'      => 'page',
				'post_parent'    => $front_page_id,
				'post_status'    => 'publish',
				'posts_per_page' => 4,
				'orderby'        => array(
					'menu_order' => 'ASC',
					'title'      => 'ASC',
				),
			)
		);

		foreach ( $children as $child ) {
			$features[] = array(
				'title' => wp_strip_all_tags( get_the_title( $child ) ),
				'text'  => wp_trim_words( get_the_excerpt( $child ), 26 ),
				'url'   => (string) get_permalink( $child ),
			);
		}
	}

	/**
	 * Filters the feature cards shown on the front page accent band.
	 *
	 * @param array<int, array{title: string, text: string, url: string}> $features      Cards, up to four look best.
	 * @param int                                                         $front_page_id Static front page ID or 0.
	 */
	$features = (array) apply_filters( 'cik_hybrid_front_page_features', $features, $front_page_id );

	// Keep only well-formed cards with a title.
	return array_values(
		array_filter(
			array_map(
				static function ( $feature ): array {
					$feature = is_array( $feature ) ? $feature : array();
					return array(
						'title' => isset( $feature['title'] ) ? (string) $feature['title'] : '',
						'text'  => isset( $feature['text'] ) ? (string) $feature['text'] : '',
						'url'   => isset( $feature['url'] ) ? (string) $feature['url'] : '',
					);
				},
				$features
			),
			static fn( array $feature ): bool => '' !== $feature['title']
		)
	);
}

/**
 * The closing call to action: links to the page with the slug "contact" when
 * there is one. Filter `cik_hybrid_front_page_cta` to change or remove it.
 *
 * @return array{title: string, text: string, button: array{label: string, url: string}}|array{}
 */
function cik_hybrid_front_page_cta(): array {
	$contact = get_page_by_path( 'contact' );
	$cta     = array();

	if ( $contact instanceof WP_Post && 'publish' === $contact->post_status ) {
		$cta = array(
			'title'  => __( 'Have a project in mind?', 'cik-hybrid' ),
			'text'   => has_excerpt( $contact ) ? get_the_excerpt( $contact ) : '',
			'button' => array(
				'label' => __( 'Get in touch', 'cik-hybrid' ),
				'url'   => (string) get_permalink( $contact ),
			),
		);
	}

	/**
	 * Filters the front page call to action. Return an empty array to hide it.
	 *
	 * @param array $cta [ 'title' => '', 'text' => '', 'button' => [ 'label' => '', 'url' => '' ] ].
	 */
	$cta = (array) apply_filters( 'cik_hybrid_front_page_cta', $cta );

	return ! empty( $cta['title'] ) ? $cta : array();
}
