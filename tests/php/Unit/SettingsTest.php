<?php
/**
 * Theme settings: ACF options page or Customizer, sanitised on read.
 *
 * Whether ACF is "installed" is decided with function_exists(), and a function
 * defined by a stub cannot be undefined, so tests that "install" it run in
 * their own process.
 *
 * @package Cik_Hybrid
 */

namespace CikHybrid\Tests\Unit;

use Brain\Monkey\Functions;
use CikHybrid\Tests\TestCase;
use Mockery;

final class SettingsTest extends TestCase {

	private const KSES_TAGS = array(
		'a'      => array(
			'href' => true,
			'rel'  => true,
		),
		'br'     => array(),
		'em'     => array(),
		'strong' => array(),
	);

	protected function setUp(): void {
		parent::setUp();
		Functions\stubTranslationFunctions();
	}

	/**
	 * Defines the ACF PRO functions the theme checks for; get_field() is set up by each test.
	 */
	private function install_acf(): void {
		Functions\when( 'acf_add_options_page' )->justReturn( array() );
		Functions\when( 'acf_add_local_field_group' )->justReturn( true );
	}

	public function test_without_acf_values_come_from_the_customizer(): void {
		Functions\expect( 'get_theme_mod' )
			->once()
			->with( 'cik_hybrid_footer_text', '' )
			->andReturn( 'Made in <strong>Athens</strong><script>x</script>' );
		Functions\expect( 'wp_kses' )
			->once()
			->with( 'Made in <strong>Athens</strong><script>x</script>', self::KSES_TAGS )
			->andReturn( 'Made in <strong>Athens</strong>x' );

		$this->assertFalse( cik_hybrid_uses_acf() );
		$this->assertSame( 'Made in <strong>Athens</strong>x', cik_hybrid_setting( 'footer_text' ) );
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_with_acf_values_come_from_the_options_page(): void {
		$this->install_acf();
		Functions\expect( 'get_field' )
			->once()
			->with( 'cik_hybrid_social_facebook', 'option' )
			->andReturn( 'https://facebook.com/cikefallinos' );
		Functions\expect( 'get_theme_mod' )->never();
		Functions\expect( 'esc_url_raw' )
			->once()
			->with( 'https://facebook.com/cikefallinos', array( 'http', 'https' ) )
			->andReturnFirstArg();

		$this->assertTrue( cik_hybrid_uses_acf() );
		$this->assertSame( 'https://facebook.com/cikefallinos', cik_hybrid_setting( 'social_facebook' ) );
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_acf_without_options_pages_falls_back_to_the_customizer(): void {
		// Free ACF has get_field() but no options pages.
		Functions\when( 'get_field' )->justReturn( 'unused' );
		Functions\expect( 'get_theme_mod' )->once()->andReturn( '' );
		Functions\when( 'esc_url_raw' )->returnArg();

		$this->assertFalse( cik_hybrid_uses_acf() );
		$this->assertSame( '', cik_hybrid_setting( 'social_linkedin' ) );
	}

	public function test_a_value_rejected_by_sanitising_returns_the_default(): void {
		Functions\when( 'get_theme_mod' )->justReturn( 'javascript:alert(1)' );
		Functions\expect( 'esc_url_raw' )
			->once()
			->with( 'javascript:alert(1)', array( 'http', 'https' ) )
			->andReturn( '' );

		$this->assertSame( 'https://example.test/', cik_hybrid_setting( 'social_instagram', 'https://example.test/' ) );
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_non_scalar_values_are_treated_as_empty(): void {
		$this->install_acf();
		Functions\when( 'get_field' )->justReturn( array( 'url' => 'https://example.test/' ) );
		Functions\expect( 'esc_url_raw' )->once()->with( '', array( 'http', 'https' ) )->andReturn( '' );

		$this->assertSame( 'fallback', cik_hybrid_setting( 'social_facebook', 'fallback' ) );
	}

	/**
	 * @dataProvider checkbox_values
	 *
	 * @param mixed  $raw      Stored value.
	 * @param string $expected Sanitised value.
	 */
	public function test_checkbox_values_are_normalised( $raw, string $expected ): void {
		$this->assertSame( $expected, cik_hybrid_sanitize_setting( $raw, 'checkbox' ) );
	}

	public static function checkbox_values(): array {
		return array(
			'never saved'          => array( null, '' ),
			'empty theme mod'      => array( '', '' ),
			'ACF true'             => array( true, '1' ),
			'ACF false'            => array( false, '0' ),
			'customizer on'        => array( '1', '1' ),
			'customizer off'       => array( '0', '0' ),
			'unexpected string'    => array( '<b>yes</b>', '0' ),
			'non-scalar'           => array( array( 1 ), '0' ),
		);
	}

	public function test_customizer_checkbox_saves_unchecked_as_zero(): void {
		$this->assertSame( '0', cik_hybrid_sanitize_checkbox_setting( false ) );
		$this->assertSame( '0', cik_hybrid_sanitize_checkbox_setting( '' ) );
		$this->assertSame( '1', cik_hybrid_sanitize_checkbox_setting( true ) );
	}

	public function test_unsaved_checkbox_falls_back_to_the_field_default(): void {
		Functions\when( 'get_theme_mod' )->justReturn( '' );
		$this->assertSame( '1', cik_hybrid_setting( 'show_site_name' ) );
	}

	public function test_unchecked_checkbox_is_not_replaced_by_the_default(): void {
		Functions\when( 'get_theme_mod' )->justReturn( '0' );
		$this->assertSame( '0', cik_hybrid_setting( 'show_site_name' ) );
	}

	public function test_unknown_keys_return_the_default_without_reading_anything(): void {
		Functions\expect( 'get_theme_mod' )->never();

		$this->assertSame( 'default', cik_hybrid_setting( 'admin_email', 'default' ) );
	}

	public function test_customizer_registers_every_field_with_a_type_specific_sanitizer(): void {
		$manager = Mockery::mock( 'WP_Customize_Manager' );
		$manager->expects( 'add_section' )->once()->with( 'cik_hybrid_settings', Mockery::type( 'array' ) );
		$manager->expects( 'add_control' )->times( 6 );

		$callbacks = array();
		$manager->expects( 'add_setting' )->times( 6 )->andReturnUsing(
			static function ( string $id, array $args ) use ( &$callbacks ): void {
				$callbacks[ $id ] = $args['sanitize_callback'];
			}
		);

		cik_hybrid_customize_register( $manager );

		$this->assertSame(
			array(
				'cik_hybrid_show_site_name'   => 'cik_hybrid_sanitize_checkbox_setting',
				'cik_hybrid_disable_comments' => 'cik_hybrid_sanitize_checkbox_setting',
				'cik_hybrid_social_facebook'  => 'cik_hybrid_sanitize_url_setting',
				'cik_hybrid_social_instagram' => 'cik_hybrid_sanitize_url_setting',
				'cik_hybrid_social_linkedin'  => 'cik_hybrid_sanitize_url_setting',
				'cik_hybrid_footer_text'      => 'cik_hybrid_sanitize_text_setting',
			),
			$callbacks
		);
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_customizer_section_is_skipped_when_acf_owns_the_settings(): void {
		$this->install_acf();
		Functions\when( 'get_field' )->justReturn( null );
		$manager = Mockery::mock( 'WP_Customize_Manager' );
		$manager->expects( 'add_section' )->never();

		cik_hybrid_customize_register( $manager );
	}
}
