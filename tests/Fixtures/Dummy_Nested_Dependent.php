<?php
/**
 * Test fixture: Dummy_Nested_Dependent.
 *
 * @package Sherv\Container\Tests
 */

declare( strict_types=1 );

namespace Sherv\Container\Tests\Fixtures;

class Dummy_Nested_Dependent {
	public function __construct( public Dummy_Dependent $dependency ) {
	}
}
