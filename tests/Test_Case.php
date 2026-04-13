<?php
/**
 * Base Test_Case.
 *
 * @package Sherv\Container\Tests
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Sherv\Container\Tests;

use Brain\Monkey;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

/**
 * Abstract base test case for the container test suite.
 *
 * @since 1.0.0
 */
abstract class Test_Case extends TestCase {

	use MockeryPHPUnitIntegration;

	/**
	 * Set up Brain Monkey before each test.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
		Monkey\Functions\stubEscapeFunctions();
	}

	/**
	 * Tear down Brain Monkey after each test.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}
}
