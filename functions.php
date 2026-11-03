<?php
/**
 * Kefallinos Hybrid bootstrap.
 *
 * Only defines constants and loads the central loader. All behaviour lives in inc/.
 *
 * @package Cik_Hybrid
 */

defined( 'ABSPATH' ) || exit;

define( 'CIK_HYBRID_VERSION', '1.0.0' );
define( 'CIK_HYBRID_DIR', get_template_directory() );
define( 'CIK_HYBRID_URI', get_template_directory_uri() );
define( 'CIK_HYBRID_INC_DIR', CIK_HYBRID_DIR . '/inc' );

require_once CIK_HYBRID_INC_DIR . '/init.php';
