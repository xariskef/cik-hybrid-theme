<?php
/**
 * cik_hybrid_island(): container markup and lazy loader enqueueing.
 *
 * @package Cik_Hybrid
 */

namespace CikHybrid\Tests\Unit;

use Brain\Monkey\Functions;
use CikHybrid\Tests\TestCase;

final class IslandTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Functions\stubEscapeFunctions();
		Functions\stubTranslationFunctions();
	}

	private function loader_available(): void {
		Functions\when( 'cik_hybrid_enqueue_vite_entry' )->justReturn( true );
	}

	public function test_props_are_json_encoded_and_attribute_escaped(): void {
		$this->loader_available();
		$props = array(
			'title' => 'Tom & "Jerry" <b>',
			'url'   => 'https://example.test/?a=1&b=2',
			'count' => 3,
		);

		$html = $this->capture( static fn () => cik_hybrid_island( 'Slider', $props ) );

		$this->assertMatchesRegularExpression( '/^<div class="cik-island" data-island="Slider" data-props="[^"<>]*" aria-busy="true">/', $html );
		$this->assertStringNotContainsString( '<b>', $html );

		preg_match( '/data-props="([^"]*)"/', $html, $match );
		$this->assertSame( $props, json_decode( html_entity_decode( $match[1], ENT_QUOTES ), true ) );
	}

	public function test_without_fallback_an_accessible_placeholder_is_printed(): void {
		$this->loader_available();

		$html = $this->capture( static fn () => cik_hybrid_island( 'Tabs' ) );

		$this->assertStringContainsString( 'data-props="[]"', $html );
		$this->assertStringContainsString( 'aria-busy="true"', $html );
		$this->assertStringContainsString( 'role="status"', $html );
		$this->assertStringEndsWith( '</div>', $html );
	}

	public function test_fallback_markup_is_filtered_and_replaces_the_placeholder(): void {
		$this->loader_available();
		Functions\expect( 'wp_kses_post' )
			->once()
			->with( '<article>First slide</article><script>alert(1)</script>' )
			->andReturn( '<article>First slide</article>' );

		$html = $this->capture(
			static fn () => cik_hybrid_island( 'Slider', array(), '<article>First slide</article><script>alert(1)</script>' )
		);

		$this->assertStringContainsString( '<article>First slide</article></div>', $html );
		$this->assertStringNotContainsString( 'aria-busy', $html );
		$this->assertStringNotContainsString( 'role="status"', $html );
	}

	/**
	 * The "already enqueued" flag is a function-level static, so this needs a fresh process.
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_loader_is_enqueued_once_for_several_islands(): void {
		Functions\expect( 'cik_hybrid_enqueue_vite_entry' )->once()->with( 'src/js/islands.ts' )->andReturn( true );

		$html = $this->capture(
			static function (): void {
				cik_hybrid_island( 'Slider' );
				cik_hybrid_island( 'Tabs' );
			}
		);

		$this->assertSame( 2, substr_count( $html, 'data-island=' ) );
	}

	/**
	 * @dataProvider invalid_names
	 */
	public function test_invalid_component_names_print_nothing( string $name ): void {
		Functions\expect( 'cik_hybrid_enqueue_vite_entry' )->never();
		Functions\expect( '_doing_it_wrong' )->once();

		$this->assertSame( '', $this->capture( static fn () => cik_hybrid_island( $name ) ) );
	}

	/**
	 * @return array<string, array{string}>
	 */
	public static function invalid_names(): array {
		return array(
			'lower case'     => array( 'slider' ),
			'path traversal' => array( '../Slider' ),
			'markup'         => array( 'Slider"><script>' ),
			'empty'          => array( '' ),
		);
	}
}
