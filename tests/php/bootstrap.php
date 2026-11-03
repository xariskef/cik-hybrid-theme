<?php
/**
 * PHPUnit bootstrap: no WordPress, no database. WordPress functions are
 * stubbed per test with Brain Monkey.
 *
 * @package Cik_Hybrid
 */

require_once dirname( __DIR__, 2 ) . '/vendor/autoload.php';

// Loads Patchwork and the hook API stubs before any theme file, so theme
// functions can be redefined in tests (Patchwork only patches code it loads).
Brain\Monkey\setUp();

define( 'ABSPATH', sys_get_temp_dir() . '/wordpress/' );
define( 'CIK_HYBRID_VERSION', '1.0.0' );
define( 'CIK_HYBRID_DIR', dirname( __DIR__, 2 ) );
define( 'CIK_HYBRID_URI', 'https://example.test/wp-content/themes/cik-hybrid-theme' );
define( 'CIK_HYBRID_INC_DIR', CIK_HYBRID_DIR . '/inc' );

require_once CIK_HYBRID_INC_DIR . '/setup/assets.php';
require_once CIK_HYBRID_INC_DIR . '/helpers/icons.php';
require_once CIK_HYBRID_INC_DIR . '/helpers/components.php';
require_once CIK_HYBRID_INC_DIR . '/helpers/islands.php';
require_once CIK_HYBRID_INC_DIR . '/blocks.php';
require_once CIK_HYBRID_INC_DIR . '/settings.php';

// Hooks added while loading the files above are not part of any test.
Brain\Monkey\tearDown();
