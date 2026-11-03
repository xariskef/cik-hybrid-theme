<?php
/**
 * Component loader and two components rendered with real arguments.
 *
 * @package Cik_Hybrid
 */

namespace CikHybrid\Tests\Unit;

use Brain\Monkey\Functions;
use CikHybrid\Tests\TestCase;

final class ComponentsTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Functions\stubEscapeFunctions();
		Functions\when( 'wp_kses' )->returnArg();
		// Same as core for array input.
		Functions\when( 'wp_parse_args' )->alias( static fn ( array $args, array $defaults ) => array_merge( $defaults, $args ) );
	}

	public function test_component_delegates_to_get_template_part_so_child_themes_can_override(): void {
		$args = array( 'title' => 'Hello' );
		// get_template_part() returns void when it loaded a file and false when it found none.
		Functions\expect( 'get_template_part' )->once()->with( 'components/card', null, $args )->andReturnNull();
		Functions\expect( 'get_template_part' )->once()->with( 'components/missing', null, array() )->andReturn( false );

		$this->assertTrue( cik_hybrid_component( 'card', $args ) );
		$this->assertFalse( cik_hybrid_component( 'missing' ) );
	}

	/**
	 * @dataProvider heading_tags
	 */
	public function test_heading_tag_is_limited_to_known_tags( string $tag, string $expected ): void {
		$this->assertSame( $expected, cik_hybrid_heading_tag( $tag ) );
	}

	/**
	 * @return array<string, array{string, string}>
	 */
	public static function heading_tags(): array {
		return array(
			'h3'         => array( 'h3', 'h3' ),
			'paragraph'  => array( 'p', 'p' ),
			'script'     => array( 'script', 'h2' ),
			'upper case' => array( 'H1', 'h2' ),
			'with attrs' => array( 'h2 onclick=x', 'h2' ),
		);
	}

	public function test_heading_tag_uses_the_given_fallback(): void {
		$this->assertSame( 'h3', cik_hybrid_heading_tag( 'div', 'h3' ) );
	}

	public function test_section_heading_escapes_text_and_rejects_unknown_levels(): void {
		$html = $this->render_component(
			'section-heading',
			array(
				'title' => 'Tips & <tricks>',
				'level' => 'script',
				'id'    => 'a"b',
			)
		);

		$this->assertStringContainsString( '<h2 id="a&quot;b" class=', $html );
		$this->assertStringContainsString( 'Tips &amp; &lt;tricks&gt;', $html );
		$this->assertStringContainsString( '</h2>', $html );
		$this->assertStringNotContainsString( '<script', $html );
	}

	public function test_section_heading_without_title_renders_nothing(): void {
		$this->assertSame( '', $this->render_component( 'section-heading', array( 'eyebrow' => 'Blog' ) ) );
	}

	public function test_button_with_url_is_a_link(): void {
		$html = $this->render_component(
			'button',
			array(
				'label' => 'Read',
				'url'   => 'https://example.test/blog/',
			)
		);

		$this->assertStringContainsString( 'href="https://example.test/blog/"', $html );
		$this->assertStringContainsString( 'cik-btn--primary', $html );
		$this->assertStringNotContainsString( '<button', $html );
	}

	public function test_button_without_url_only_allows_known_types_and_variants(): void {
		$html = $this->render_component(
			'button',
			array(
				'label'   => 'Send',
				'type'    => 'image',
				'variant' => 'shiny',
			)
		);

		$this->assertStringContainsString( 'type="button"', $html );
		// Unknown variants get the primary styles.
		$this->assertStringContainsString( 'cik-btn--primary', $html );

		$submit = $this->render_component(
			'button',
			array(
				'label' => 'Send',
				'type'  => 'submit',
			)
		);
		$this->assertStringContainsString( 'type="submit"', $submit );
	}
}
