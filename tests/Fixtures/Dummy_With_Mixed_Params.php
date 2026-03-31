<?php
/**
 * Test fixture: Dummy_With_Mixed_Params.
 *
 * @package Sherv\Container\Tests
 */

declare( strict_types=1 );

namespace Sherv\Container\Tests\Fixtures;

class Dummy_With_Mixed_Params {
	public function __construct( public Dummy_Concrete $concrete, public string $name ) {
	}
}
