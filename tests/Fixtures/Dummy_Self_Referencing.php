<?php
/**
 * Test fixture: Dummy_Self_Referencing.
 *
 * @package Sherv\Container\Tests
 */

declare( strict_types=1 );

namespace Sherv\Container\Tests\Fixtures;

class Dummy_Self_Referencing {
	public function __construct( public ?self $parent = null ) { // phpcs:ignore Universal.NamingConventions.NoReservedKeywordParameterNames.parentFound, VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
	}
}
