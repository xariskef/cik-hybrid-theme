<?php
/**
 * Vite integration: hot file, manifest resolution and the missing-build notice.
 *
 * @package Cik_Hybrid
 */

namespace CikHybrid\Tests\Unit;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use CikHybrid\Tests\TestCase;

final class AssetsTest extends TestCase {

	private const DEV = 'http://localhost:5173';
	private const DIST = CIK_HYBRID_URI . '/dist/';

	private string $tmp;

	protected function setUp(): void {
		parent::setUp();

		$this->tmp = sys_get_temp_dir() . '/cik-hybrid-' . bin2hex( random_bytes( 4 ) );
		mkdir( $this->tmp );

		Functions\when( 'wp_parse_url' )->alias( 'parse_url' );
		Functions\when( 'esc_url_raw' )->returnArg();
		Functions\when( 'sanitize_key' )->alias(
			static fn ( $key ) => preg_replace( '/[^a-z0-9_\-]/', '', strtolower( (string) $key ) )
		);
		Functions\when( 'wp_json_file_decode' )->alias(
			static fn ( string $file, array $options ) => json_decode( (string) file_get_contents( $file ), ! empty( $options['associative'] ) )
		);
	}

	protected function tearDown(): void {
		array_map( 'unlink', glob( $this->tmp . '/*' ) ?: array() );
		rmdir( $this->tmp );
		parent::tearDown();
	}

	private function hot_file( string $contents ): string {
		$path = $this->tmp . '/hot';
		file_put_contents( $path, $contents );
		return $path;
	}

	/**
	 * @return array<string, mixed>
	 */
	private function fixture_manifest(): array {
		return cik_hybrid_read_vite_manifest( dirname( __DIR__ ) . '/fixtures/manifest.json' );
	}

	private function use_dev_server( ?string $url ): void {
		Functions\when( 'cik_hybrid_vite_dev_url' )->justReturn( $url );
	}

	/**
	 * @param array<string, mixed> $manifest
	 */
	private function use_manifest( array $manifest ): void {
		Functions\when( 'cik_hybrid_vite_manifest' )->justReturn( $manifest );
	}

	public function test_hot_file_yields_the_dev_server_origin_without_trailing_slash(): void {
		$this->assertSame( self::DEV, cik_hybrid_read_hot_file( $this->hot_file( self::DEV . "/\n" ) ) );
		$this->assertSame( 'https://theme.test:5173', cik_hybrid_read_hot_file( $this->hot_file( 'https://theme.test:5173' ) ) );
	}

	/**
	 * @dataProvider unsafe_hot_file_contents
	 */
	public function test_hot_file_with_anything_but_an_http_url_is_ignored( string $contents ): void {
		$this->assertNull( cik_hybrid_read_hot_file( $this->hot_file( $contents ) ) );
	}

	/**
	 * @return array<string, array{string}>
	 */
	public static function unsafe_hot_file_contents(): array {
		return array(
			'javascript' => array( 'javascript:alert(1)' ),
			'data'       => array( 'data:text/html;base64,PHNjcmlwdD4=' ),
			'ftp'        => array( 'ftp://localhost:5173' ),
			'not a url'  => array( 'localhost:5173' ),
			'empty'      => array( '' ),
		);
	}

	public function test_missing_hot_file_means_production(): void {
		$this->assertNull( cik_hybrid_read_hot_file( $this->tmp . '/hot' ) );
	}

	public function test_dev_server_can_be_disabled_with_a_filter(): void {
		Filters\expectApplied( 'cik_hybrid_vite_dev_enabled' )->once()->with( true )->andReturn( false );

		$this->assertNull( cik_hybrid_read_hot_file( $this->hot_file( self::DEV ) ) );
	}

	public function test_manifest_reader_tolerates_missing_and_invalid_files(): void {
		$this->assertSame( array(), cik_hybrid_read_vite_manifest( $this->tmp . '/missing.json' ) );

		file_put_contents( $this->tmp . '/manifest.json', '{ not json' );
		$this->assertSame( array(), cik_hybrid_read_vite_manifest( $this->tmp . '/manifest.json' ) );

		$this->assertArrayHasKey( 'src/js/islands.ts', $this->fixture_manifest() );
	}

	public function test_dev_mode_enqueues_the_vite_client_and_the_entry_from_the_dev_server(): void {
		$this->use_dev_server( self::DEV );

		Functions\expect( 'wp_enqueue_script_module' )
			->twice()
			->with( 'cik-hybrid/vite-client', self::DEV . '/@vite/client', array(), null )
			->andReturnNull();
		Functions\expect( 'wp_enqueue_script_module' )
			->once()
			->with( 'cik-hybrid/src/js/app.ts', self::DEV . '/src/js/app.ts', array(), null );
		// CSS entries are JS modules in dev (Vite injects the styles with HMR).
		Functions\expect( 'wp_enqueue_script_module' )
			->once()
			->with( 'cik-hybrid/src/css/app.css', self::DEV . '/src/css/app.css', array(), null );
		Functions\expect( 'wp_enqueue_style' )->never();

		$this->assertTrue( cik_hybrid_enqueue_vite_entry( 'src/js/app.ts' ) );
		$this->assertTrue( cik_hybrid_enqueue_vite_entry( 'src/css/app.css' ) );
	}

	public function test_css_entry_resolves_to_the_hashed_stylesheet(): void {
		$this->use_dev_server( null );
		$this->use_manifest( $this->fixture_manifest() );

		Functions\expect( 'wp_enqueue_style' )
			->once()
			->with( 'cik-hybrid-app', self::DIST . 'assets/app-D1x2.css', array(), null );
		Functions\expect( 'wp_enqueue_script_module' )->never();

		$this->assertTrue( cik_hybrid_enqueue_vite_entry( 'src/css/app.css' ) );
	}

	public function test_script_entry_registers_static_imports_for_modulepreload_and_collects_their_css(): void {
		$this->use_dev_server( null );
		$this->use_manifest( $this->fixture_manifest() );

		// Imports are registered depth first, each with its own imports as dependencies.
		Functions\expect( 'wp_register_script_module' )
			->once()
			->with( 'cik-hybrid/_react-Rr34.js', self::DIST . 'assets/react-Rr34.js', array(), null );
		Functions\expect( 'wp_register_script_module' )
			->once()
			->with( 'cik-hybrid/_client-Xy12.js', self::DIST . 'assets/client-Xy12.js', array( 'cik-hybrid/_react-Rr34.js' ), null );
		// Dynamic imports (the island chunks) are not preloaded: no expectation for Slider.
		Functions\expect( 'wp_enqueue_script_module' )
			->once()
			->with( 'cik-hybrid/src/js/islands.ts', self::DIST . 'assets/islands-Ab90.js', array( 'cik-hybrid/_client-Xy12.js' ), null );
		Functions\expect( 'wp_enqueue_style' )
			->once()
			->with( 'cik-hybrid-assetsislands-cc11css', self::DIST . 'assets/islands-Cc11.css', array(), null );
		Functions\expect( 'wp_enqueue_style' )
			->once()
			->with( 'cik-hybrid-assetsshared-sh56css', self::DIST . 'assets/shared-Sh56.css', array(), null );

		$this->assertTrue( cik_hybrid_enqueue_vite_entry( 'src/js/islands.ts' ) );
	}

	public function test_css_collection_survives_import_cycles(): void {
		$manifest = array(
			'a.js' => array(
				'file'    => 'a.js',
				'imports' => array( 'b.js' ),
				'css'     => array( 'a.css' ),
			),
			'b.js' => array(
				'file'    => 'b.js',
				'imports' => array( 'a.js' ),
				'css'     => array( 'b.css', 'a.css' ),
			),
		);

		$this->assertSame( array( 'a.css', 'b.css' ), cik_hybrid_collect_vite_css( 'a.js', $manifest ) );
	}

	public function test_unknown_entry_is_not_enqueued(): void {
		$this->use_dev_server( null );
		$this->use_manifest( $this->fixture_manifest() );

		Functions\expect( 'wp_enqueue_script_module' )->never();
		Functions\expect( 'wp_enqueue_style' )->never();

		$this->assertFalse( cik_hybrid_enqueue_vite_entry( 'src/js/missing.ts' ) );
	}

	public function test_editor_stylesheet_comes_from_the_manifest(): void {
		$this->use_manifest( $this->fixture_manifest() );
		$this->assertSame( 'dist/assets/app-D1x2.css', cik_hybrid_built_stylesheet() );

		$this->use_manifest( array() );
		$this->assertNull( cik_hybrid_built_stylesheet() );
	}

	public function test_global_assets_do_not_include_the_islands_loader(): void {
		Functions\expect( 'cik_hybrid_enqueue_vite_entry' )->once()->with( 'src/css/app.css' )->andReturn( true );
		Functions\expect( 'cik_hybrid_enqueue_vite_entry' )->once()->with( 'src/js/app.ts' )->andReturn( true );
		Functions\when( 'is_singular' )->justReturn( false );

		cik_hybrid_enqueue_assets();
	}

	public function test_admins_are_told_when_there_is_no_build_and_no_dev_server(): void {
		$this->use_dev_server( null );
		$this->use_manifest( array() );
		Functions\expect( 'current_user_can' )->once()->with( 'edit_theme_options' )->andReturn( true );
		Functions\when( '__' )->returnArg();
		Functions\when( 'wp_kses' )->returnArg();

		$this->assertFalse( cik_hybrid_enqueue_vite_entry( 'src/js/app.ts' ) );

		$output = $this->capture( 'cik_hybrid_missing_assets_notice' );
		$this->assertStringContainsString( 'class="notice notice-warning"', $output );
		$this->assertStringContainsString( 'pnpm build', $output );
	}

	public function test_no_notice_for_users_who_cannot_fix_it(): void {
		$this->use_dev_server( null );
		$this->use_manifest( array() );
		Functions\expect( 'current_user_can' )->once()->andReturn( false );

		$this->assertSame( '', $this->capture( 'cik_hybrid_missing_assets_notice' ) );
	}

	public function test_no_notice_when_a_build_exists(): void {
		$this->use_dev_server( null );
		$this->use_manifest( $this->fixture_manifest() );
		Functions\expect( 'current_user_can' )->never();

		$this->assertSame( '', $this->capture( 'cik_hybrid_missing_assets_notice' ) );
	}
}
