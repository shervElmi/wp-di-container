<?php
/**
 * Test fixture: Dummy_Circular_A.
 *
 * @package Sherv\Container\Tests
 */

declare( strict_types=1 );

namespace Sherv\Container\Tests\Fixtures;

class Dummy_Circular_A {
	public function __construct( public Dummy_Circular_B $dependency ) {
	}
}
