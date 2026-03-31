<?php
/**
 * Test fixture: Dummy_With_Default_Param_Value.
 *
 * @package Sherv\Container\Tests
 */

declare( strict_types=1 );

namespace Sherv\Container\Tests\Fixtures;

class Dummy_With_Default_Param_Value {
	public function __construct( public string $name = 'default' ) {
	}
}
