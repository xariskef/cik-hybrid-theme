<?php
/**
 * Inline SVG icons.
 *
 * Icons are inlined (no sprite request, styleable with currentColor). The path
 * data is part of the theme, never user input, so it is safe to print as-is.
 *
 * @package Cik_Hybrid
 */

defined( 'ABSPATH' ) || exit;

/**
 * SVG path data per icon name, 24x24 viewBox, stroke based.
 *
 * @return array<string, string>
 */
function cik_hybrid_icon_paths(): array {
	$paths = array(
		'arrow-right'  => 'M5 12h14M13 6l6 6-6 6',
		'cart'         => 'M3 4h2l2.4 10.2a1 1 0 0 0 1 .8h8.7a1 1 0 0 0 1-.8L20 8H6.2M9 20h.01M17 20h.01',
		'chevron-down' => 'M6 9l6 6 6-6',
		'chevron-left' => 'M15 6l-6 6 6 6',
		'chevron-right' => 'M9 6l6 6-6 6',
		'close'        => 'M6 6l12 12M18 6L6 18',
		'facebook'     => 'M14 8h3V4h-3a4 4 0 0 0-4 4v2H7v4h3v6h4v-6h3l1-4h-4V8z',
		'instagram'    => 'M7 3h10a4 4 0 0 1 4 4v10a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4V7a4 4 0 0 1 4-4zM16 11.4A4 4 0 1 1 12.6 8 4 4 0 0 1 16 11.4zM17.5 6.5h.01',
		'linkedin'     => 'M6 9v12M6 4.5v.01M10 21v-7a3 3 0 0 1 6 0v7M10 12V9',
		'menu'         => 'M4 7h16M4 12h16M4 17h16',
		'pause'        => 'M8 5v14M16 5v14',
		'play'         => 'M7 5l12 7-12 7V5z',
		'search'       => 'M11 18a7 7 0 1 1 0-14 7 7 0 0 1 0 14zM20 20l-4-4',
	);

	/**
	 * Filters the available icons so child themes can add or replace path data.
	 *
	 * @param array<string, string> $paths Icon name => SVG path "d" attribute.
	 */
	return (array) apply_filters( 'cik_hybrid_icon_paths', $paths );
}

/**
 * Returns an icon as inline SVG markup.
 *
 * Icons are decorative (aria-hidden) by default; pass a label for standalone icons.
 *
 * @param string $name  Icon name, see cik_hybrid_icon_paths().
 * @param array  $args  {
 *     Optional.
 *
 *     @type string $class CSS classes. Default 'size-5'.
 *     @type string $label Accessible label. Default '' (decorative).
 * }
 * @return string SVG markup, or '' for an unknown icon.
 */
function cik_hybrid_get_icon( string $name, array $args = array() ): string {
	$paths = cik_hybrid_icon_paths();
	if ( empty( $paths[ $name ] ) ) {
		return '';
	}

	$args = wp_parse_args(
		$args,
		array(
			'class' => 'size-5',
			'label' => '',
		)
	);

	$a11y = '' === $args['label']
		? 'aria-hidden="true" focusable="false"'
		: sprintf( 'role="img" aria-label="%s"', esc_attr( $args['label'] ) );

	return sprintf(
		'<svg class="%1$s" %2$s xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="%3$s"/></svg>',
		esc_attr( $args['class'] ),
		$a11y,
		esc_attr( $paths[ $name ] )
	);
}

/**
 * Allowed tags for echoing icon markup through wp_kses().
 *
 * @return array<string, array<string, bool>>
 */
function cik_hybrid_kses_svg(): array {
	return array(
		'svg'  => array(
			'class'           => true,
			'aria-hidden'     => true,
			'aria-label'      => true,
			'role'            => true,
			'focusable'       => true,
			'xmlns'           => true,
			'viewbox'         => true,
			'fill'            => true,
			'stroke'          => true,
			'stroke-width'    => true,
			'stroke-linecap'  => true,
			'stroke-linejoin' => true,
		),
		'path' => array( 'd' => true ),
	);
}
