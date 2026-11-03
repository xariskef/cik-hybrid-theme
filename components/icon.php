<?php
/**
 * Component: icon.
 *
 * @package Cik_Hybrid
 *
 * @var array $args {
 *     @type string $name  Icon name (see cik_hybrid_icon_paths()). Required.
 *     @type string $class CSS classes. Default 'size-5'.
 *     @type string $label Accessible label; empty means decorative. Default ''.
 * }
 */

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args(
	$args,
	array(
		'name'  => '',
		'class' => 'size-5',
		'label' => '',
	)
);

echo wp_kses(
	cik_hybrid_get_icon(
		(string) $args['name'],
		array(
			'class' => (string) $args['class'],
			'label' => (string) $args['label'],
		)
	),
	cik_hybrid_kses_svg()
);
