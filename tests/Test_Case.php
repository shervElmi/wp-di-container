<?php
/**
 * Base Test_Case.
 *
 * @package Sherv\Container\Tests
 * @since   X.X.X
 */

declare( strict_types=1 );

namespace Sherv\Container\Tests;

use Brain\Monkey;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

/**
 * Abstract base test case for the container test suite.
 *
 * @since X.X.X
 */
abstract class Test_Case extends TestCase {

	use MockeryPHPUnitIntegration;

	/**
	 * Set up Brain Monkey before each test.
	 *
	 * @since X.X.X
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
	 * @since X.X.X
	 *
	 * @return void
	 */
	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}
}
