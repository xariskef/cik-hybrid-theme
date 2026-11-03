<?php
/**
 * cik_hybrid_slides_from_posts(): the props the Slider island receives.
 *
 * @package Cik_Hybrid
 */

namespace CikHybrid\Tests\Unit;

use Brain\Monkey\Functions;
use CikHybrid\Tests\TestCase;

final class SlidesFromPostsTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();

		Functions\when( 'get_the_title' )->alias( static fn ( $post ) => $post->post_title );
		Functions\when( 'wp_strip_all_tags' )->alias( 'strip_tags' );
		Functions\when( 'get_the_excerpt' )->alias( static fn ( $post ) => $post->post_excerpt );
		Functions\when( 'get_permalink' )->alias( static fn ( $post ) => $post->permalink );
	}

	private function post( int $id, string $permalink ): object {
		return (object) array(
			'ID'           => $id,
			'post_title'   => 'Post <em>' . $id . '</em>',
			'post_excerpt' => 'Excerpt ' . $id,
			'permalink'    => $permalink,
		);
	}

	public function test_slide_urls_are_restricted_to_http_and_https(): void {
		Functions\when( 'wp_trim_words' )->returnArg();
		Functions\when( 'get_post_thumbnail_id' )->justReturn( 0 );
		// esc_url_raw() returns '' for any protocol outside the list; the island drops empty hrefs.
		Functions\expect( 'esc_url_raw' )
			->once()
			->with( 'https://example.test/hello/', array( 'http', 'https' ) )
			->andReturnFirstArg();
		Functions\expect( 'esc_url_raw' )
			->once()
			->with( 'javascript:alert(1)', array( 'http', 'https' ) )
			->andReturn( '' );

		$slides = cik_hybrid_slides_from_posts(
			array(
				$this->post( 1, 'https://example.test/hello/' ),
				$this->post( 2, 'javascript:alert(1)' ),
			)
		);

		$this->assertSame( 'https://example.test/hello/', $slides[0]['url'] );
		$this->assertSame( '', $slides[1]['url'] );
	}

	public function test_titles_are_plain_text_and_excerpts_trimmed(): void {
		Functions\expect( 'wp_trim_words' )->once()->with( 'Excerpt 1', 24 )->andReturn( 'Excerpt 1' );
		Functions\when( 'esc_url_raw' )->returnArg();
		Functions\when( 'get_post_thumbnail_id' )->justReturn( 0 );
		Functions\expect( 'wp_get_attachment_image_src' )->never();

		$slides = cik_hybrid_slides_from_posts( array( $this->post( 1, 'https://example.test/a/' ) ) );

		$this->assertSame(
			array(
				array(
					'title' => 'Post 1',
					'text'  => 'Excerpt 1',
					'url'   => 'https://example.test/a/',
				),
			),
			$slides
		);
	}

	public function test_featured_image_is_passed_with_dimensions_srcset_and_alt(): void {
		Functions\when( 'wp_trim_words' )->returnArg();
		Functions\when( 'get_post_thumbnail_id' )->justReturn( 42 );
		Functions\expect( 'wp_get_attachment_image_src' )
			->once()
			->with( 42, 'cik-hybrid-hero' )
			->andReturn( array( 'https://example.test/uploads/hero.jpg', '1600', '900', true ) );
		Functions\when( 'wp_get_attachment_image_srcset' )->justReturn( 'hero-800.jpg 800w, hero.jpg 1600w' );
		Functions\expect( 'get_post_meta' )->once()->with( 42, '_wp_attachment_image_alt', true )->andReturn( 'A hero' );
		Functions\expect( 'esc_url_raw' )->twice()->with( \Mockery::type( 'string' ), array( 'http', 'https' ) )->andReturnFirstArg();

		$slides = cik_hybrid_slides_from_posts( array( $this->post( 1, 'https://example.test/a/' ) ) );

		$this->assertSame(
			array(
				'src'    => 'https://example.test/uploads/hero.jpg',
				'srcset' => 'hero-800.jpg 800w, hero.jpg 1600w',
				'alt'    => 'A hero',
				'width'  => 1600,
				'height' => 900,
			),
			$slides[0]['image']
		);
	}
}
