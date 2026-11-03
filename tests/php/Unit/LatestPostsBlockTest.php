<?php
/**
 * cik-hybrid/latest-posts: attributes are re-validated on the server.
 *
 * @package Cik_Hybrid
 */

namespace CikHybrid\Tests\Unit;

use CikHybrid\Tests\TestCase;

final class LatestPostsBlockTest extends TestCase {

	public function test_missing_attributes_fall_back_to_block_defaults(): void {
		$this->assertSame(
			array(
				'count'        => 3,
				'columns'      => 3,
				'category'     => 0,
				'show_excerpt' => false,
			),
			cik_hybrid_latest_posts_settings( array() )
		);
	}

	/**
	 * @dataProvider counts
	 *
	 * @param mixed $raw Attribute as stored in post content.
	 */
	public function test_post_count_is_clamped_between_1_and_12( $raw, int $expected ): void {
		$this->assertSame( $expected, cik_hybrid_latest_posts_settings( array( 'count' => $raw ) )['count'] );
	}

	/**
	 * @return array<string, array{mixed, int}>
	 */
	public static function counts(): array {
		return array(
			'in range'      => array( 6, 6 ),
			'zero'          => array( 0, 1 ),
			'too many'      => array( 500, 12 ),
			'numeric text'  => array( '7', 7 ),
			'text'          => array( 'all', 1 ),
			// absint() takes the absolute value; still within bounds afterwards.
			'negative'      => array( -40, 12 ),
		);
	}

	/**
	 * @dataProvider columns
	 *
	 * @param mixed $raw Attribute as stored in post content.
	 */
	public function test_columns_are_clamped_between_1_and_4( $raw, int $expected ): void {
		$this->assertSame( $expected, cik_hybrid_latest_posts_settings( array( 'columns' => $raw ) )['columns'] );
	}

	/**
	 * @return array<string, array{mixed, int}>
	 */
	public static function columns(): array {
		return array(
			'in range' => array( 2, 2 ),
			'zero'     => array( 0, 1 ),
			'too many' => array( 9, 4 ),
			'float'    => array( 3.9, 3 ),
		);
	}

	public function test_category_and_excerpt_flag_are_cast(): void {
		$settings = cik_hybrid_latest_posts_settings(
			array(
				'category'    => '12abc',
				'showExcerpt' => 'yes',
			)
		);

		$this->assertSame( 12, $settings['category'] );
		$this->assertTrue( $settings['show_excerpt'] );
		$this->assertFalse( cik_hybrid_latest_posts_settings( array( 'showExcerpt' => 0 ) )['show_excerpt'] );
	}
}
