<?php
/**
 * Central loader: every theme include is required from here, in dependency order.
 *
 * @package Cik_Hybrid
 */

defined( 'ABSPATH' ) || exit;

require_once CIK_HYBRID_INC_DIR . '/setup/assets.php';
require_once CIK_HYBRID_INC_DIR . '/setup/theme-setup.php';
require_once CIK_HYBRID_INC_DIR . '/helpers/icons.php';
require_once CIK_HYBRID_INC_DIR . '/helpers/components.php';
require_once CIK_HYBRID_INC_DIR . '/navigation/class-cik-hybrid-walker-nav.php';
