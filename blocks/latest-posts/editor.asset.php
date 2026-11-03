<?php
/**
 * Dependencies of editor.js. Written by hand because the editor script is not
 * built; the version follows the file's modification time.
 *
 * @package Cik_Hybrid
 */

return array(
	'dependencies' => array(
		'wp-block-editor',
		'wp-blocks',
		'wp-components',
		'wp-core-data',
		'wp-data',
		'wp-element',
		'wp-i18n',
		'wp-server-side-render',
	),
	'version'      => (string) filemtime( __DIR__ . '/editor.js' ),
);
