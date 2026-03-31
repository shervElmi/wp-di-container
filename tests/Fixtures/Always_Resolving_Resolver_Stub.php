<?php
/**
 * Test fixture: Always_Resolving_Resolver_Stub.
 *
 * @package Sherv\Container\Tests
 */

declare( strict_types=1 );

namespace Sherv\Container\Tests\Fixtures;

use Sherv\Container\Contracts\Resolver;

class Always_Resolving_Resolver_Stub implements Resolver {
	public function resolve( mixed $_entry, array $_with = [] ): mixed { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		return new \stdClass();
	}

	public function is_resolvable( mixed $_entry ): bool { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		return true;
	}
}
