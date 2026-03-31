<?php
/**
 * Test fixture: Dummy_Dependent.
 *
 * @package Sherv\Container\Tests
 */

declare( strict_types=1 );

namespace Sherv\Container\Tests\Fixtures;

class Dummy_Dependent {
	public function __construct( public Dummy_Contract $dependency ) {
	}
}
