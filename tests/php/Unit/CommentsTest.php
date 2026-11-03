<?php
/**
 * Comments switch (inc/comments.php).
 *
 * @package Cik_Hybrid
 */

namespace CikHybrid\Tests\Unit;

use Brain\Monkey\Functions;
use CikHybrid\Tests\TestCase;

final class CommentsTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Functions\stubTranslationFunctions();
	}

	public function test_comments_are_disabled_when_nothing_is_saved(): void {
		Functions\when( 'get_theme_mod' )->justReturn( '' );

		$this->assertTrue( cik_hybrid_comments_disabled() );

		cik_hybrid_disable_comments_setup();

		$this->assertNotFalse( has_filter( 'comments_open', '__return_false' ) );
		$this->assertNotFalse( has_filter( 'pings_open', '__return_false' ) );
		$this->assertNotFalse( has_filter( 'comments_array', '__return_empty_array' ) );
		$this->assertNotFalse( has_filter( 'get_comments_number', '__return_zero' ) );
		$this->assertNotFalse( has_filter( 'feed_links_show_comments_feed', '__return_false' ) );
		$this->assertNotFalse( has_filter( 'rest_endpoints', 'cik_hybrid_remove_comment_endpoints' ) );
		$this->assertNotFalse( has_filter( 'xmlrpc_methods', 'cik_hybrid_remove_pingback_methods' ) );
		$this->assertNotFalse( has_filter( 'wp_headers', 'cik_hybrid_remove_pingback_header' ) );
		$this->assertNotFalse( has_action( 'init', 'cik_hybrid_remove_comment_support' ) );
		$this->assertNotFalse( has_action( 'admin_menu', 'cik_hybrid_remove_comments_admin_menu' ) );
	}

	public function test_turning_the_setting_off_leaves_wordpress_untouched(): void {
		Functions\when( 'get_theme_mod' )->justReturn( '0' );

		$this->assertFalse( cik_hybrid_comments_disabled() );

		cik_hybrid_disable_comments_setup();

		$this->assertFalse( has_filter( 'comments_open', '__return_false' ) );
		$this->assertFalse( has_filter( 'comments_array', '__return_empty_array' ) );
		$this->assertFalse( has_filter( 'rest_endpoints', 'cik_hybrid_remove_comment_endpoints' ) );
		$this->assertFalse( has_action( 'init', 'cik_hybrid_remove_comment_support' ) );
	}

	public function test_only_the_comment_routes_are_removed_from_the_rest_api(): void {
		$endpoints = array(
			'/wp/v2/posts'                    => array(),
			'/wp/v2/comments'                 => array(),
			'/wp/v2/comments/(?P<id>[\d]+)'   => array(),
			'/wp/v2/block-renderer/(?P<name>' => array(),
		);

		$this->assertSame(
			array( '/wp/v2/posts', '/wp/v2/block-renderer/(?P<name>' ),
			array_keys( cik_hybrid_remove_comment_endpoints( $endpoints ) )
		);
	}

	public function test_pingback_methods_and_header_are_removed(): void {
		$methods = array(
			'pingback.ping'           => 'this:pingback_ping',
			'pingback.extensionsList' => 'this:pingback_extensions_getPingbacks',
			'wp.getPosts'             => 'this:wp_getPosts',
		);
		$this->assertSame( array( 'wp.getPosts' ), array_keys( cik_hybrid_remove_pingback_methods( $methods ) ) );

		$headers = array(
			'X-Pingback'   => 'https://example.test/xmlrpc.php',
			'Content-Type' => 'text/html',
		);
		$this->assertSame( array( 'Content-Type' ), array_keys( cik_hybrid_remove_pingback_header( $headers ) ) );
	}

	public function test_comment_support_is_removed_from_every_post_type_that_has_it(): void {
		Functions\when( 'get_post_types' )->justReturn(
			array(
				'post' => 'post',
				'page' => 'page',
			)
		);
		Functions\when( 'post_type_supports' )->alias( static fn ( $type ) => 'post' === $type );

		$removed = array();
		Functions\when( 'remove_post_type_support' )->alias(
			static function ( $type, $feature ) use ( &$removed ): void {
				$removed[] = $type . ':' . $feature;
			}
		);

		cik_hybrid_remove_comment_support();

		$this->assertSame( array( 'post:comments', 'post:trackbacks' ), $removed );
	}
}
