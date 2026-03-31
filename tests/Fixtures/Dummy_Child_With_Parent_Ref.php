<?php
/**
 * Test fixture: Dummy_Child_With_Parent_Ref.
 *
 * @package Sherv\Container\Tests
 */

declare( strict_types=1 );

namespace Sherv\Container\Tests\Fixtures;

class Dummy_Child_With_Parent_Ref extends Dummy_Concrete {
	public function __construct( public ?parent $ref = null ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
	}
}
