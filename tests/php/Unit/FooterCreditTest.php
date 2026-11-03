<?php
/**
 * cik_hybrid_footer_credit(): default link and the filter that removes it.
 *
 * @package Cik_Hybrid
 */

namespace CikHybrid\Tests\Unit;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use CikHybrid\Tests\TestCase;

final class FooterCreditTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Functions\stubEscapeFunctions();
		Functions\stubTranslationFunctions();
	}

	public function test_default_credit_links_to_the_author(): void {
		$credit = cik_hybrid_footer_credit();

		$this->assertStringStartsWith( 'Theme by ', $credit );
		$this->assertStringContainsString( '<a href="https://cikefallinos.com">Cikefallinos</a>', $credit );
		$this->assertStringNotContainsString( 'target=', $credit );
	}

	public function test_filter_can_remove_the_credit(): void {
		Filters\expectApplied( 'cik_hybrid_footer_credit' )->once()->andReturn( '' );

		$this->assertSame( '', cik_hybrid_footer_credit() );
	}
}
