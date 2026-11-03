<?php
/**
 * Base test case: Brain Monkey lifecycle and a few helpers.
 *
 * @package Cik_Hybrid
 */

namespace CikHybrid\Tests;

use Brain\Monkey;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

abstract class TestCase extends PHPUnitTestCase {

	use MockeryPHPUnitIntegration;

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	/**
	 * Runs a callback and returns what it printed.
	 */
	protected function capture( callable $callback ): string {
		ob_start();
		try {
			$callback();
		} finally {
			$output = (string) ob_get_clean();
		}

		return $output;
	}

	/**
	 * Renders components/{$name}.php with $args in scope, like load_template() does.
	 *
	 * @param array<string, mixed> $args Component arguments.
	 */
	protected function render_component( string $name, array $args ): string {
		return $this->capture(
			static function () use ( $name, $args ): void {
				include CIK_HYBRID_DIR . '/components/' . $name . '.php';
			}
		);
	}
}
