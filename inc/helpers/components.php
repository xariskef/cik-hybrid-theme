<?php
/**
 * Component loader.
 *
 * @package Cik_Hybrid
 */

defined( 'ABSPATH' ) || exit;

/**
 * Renders a component from components/{$name}.php.
 *
 * Thin wrapper around get_template_part(), so a child theme overrides a
 * component by shipping its own components/{$name}.php. Each component
 * documents its arguments, applies defaults with wp_parse_args() and escapes
 * its own output.
 *
 * @param string               $name Component name, e.g. 'card' or 'section-heading'.
 * @param array<string, mixed> $args Arguments passed to the component as $args.
 * @return bool False when the component file does not exist.
 */
function cik_hybrid_component( string $name, array $args = array() ): bool {
	return false !== get_template_part( 'components/' . $name, null, $args );
}

/**
 * Restricts a heading tag to a known list, so callers cannot inject arbitrary tags.
 *
 * @param string $tag      Requested tag.
 * @param string $fallback Tag used when the request is not allowed.
 * @return string
 */
function cik_hybrid_heading_tag( string $tag, string $fallback = 'h2' ): string {
	return in_array( $tag, array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p' ), true ) ? $tag : $fallback;
}
