<?php
/**
 * Front page data: feature cards and the call to action.
 *
 * @package Cik_Hybrid
 */

namespace CikHybrid\Tests\Unit;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use CikHybrid\Tests\TestCase;

final class FrontPageTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Functions\stubTranslationFunctions();
		Functions\when( 'get_the_title' )->alias( static fn ( $post ) => $post->post_title );
		Functions\when( 'wp_strip_all_tags' )->alias( 'strip_tags' );
		Functions\when( 'get_the_excerpt' )->alias( static fn ( $post ) => $post->post_excerpt );
		Functions\when( 'wp_trim_words' )->returnArg();
		Functions\when( 'get_permalink' )->alias( static fn ( $post ) => 'https://example.test/' . $post->ID . '/' );
	}

	public function test_features_are_the_child_pages_of_the_front_page(): void {
		Functions\expect( 'get_posts' )
			->once()
			->with( \Mockery::on( static fn ( $args ) => 20 === $args['post_parent'] && 'page' === $args['post_type'] && 'publish' === $args['post_status'] ) )
			->andReturn(
				array(
					(object) array(
						'ID'           => 31,
						'post_title'   => 'Themes <em>custom</em>',
						'post_excerpt' => 'Built from your design.',
					),
				)
			);

		$this->assertSame(
			array(
				array(
					'title' => 'Themes custom',
					'text'  => 'Built from your design.',
					'url'   => 'https://example.test/31/',
				),
			),
			cik_hybrid_front_page_features( 20 )
		);
	}

	public function test_without_a_static_front_page_there_are_no_queries_and_the_filter_decides(): void {
		Functions\expect( 'get_posts' )->never();
		Filters\expectApplied( 'cik_hybrid_front_page_features' )
			->once()
			->andReturn(
				array(
					array( 'title' => 'Kept' ),
					array( 'text' => 'No title, dropped' ),
					'not an array',
				)
			);

		$this->assertSame(
			array(
				array(
					'title' => 'Kept',
					'text'  => '',
					'url'   => '',
				),
			),
			cik_hybrid_front_page_features( 0 )
		);
	}

	public function test_no_contact_page_means_no_call_to_action(): void {
		Functions\when( 'get_page_by_path' )->justReturn( null );

		$this->assertSame( array(), cik_hybrid_front_page_cta() );
	}
}
