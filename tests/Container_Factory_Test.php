<?php
/**
 * Tests for the Container_Factory class.
 *
 * @package Sherv\Container\Tests
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Sherv\Container\Tests;

use Sherv\Container\Container;
use Sherv\Container\Container_Factory;

class Container_Factory_Test extends Test_Case {

	protected function setUp(): void {
		parent::setUp();

		Container_Factory::reset();
	}

	protected function tearDown(): void {
		Container_Factory::reset();

		parent::tearDown();
	}

	public function test_create_returns_container_instance(): void {
		$container = Container_Factory::create();

		$this->assertInstanceOf( Container::class, $container );
	}

	public function test_create_returns_same_instance(): void {
		$first  = Container_Factory::create();
		$second = Container_Factory::create();

		$this->assertSame( $first, $second );
	}

	public function test_reset_clears_singleton_state(): void {
		$first = Container_Factory::create();
		Container_Factory::reset();
		$second = Container_Factory::create();

		$this->assertNotSame( $first, $second );
	}
}
