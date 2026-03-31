<?php
/**
 * Tests for the Resolver_Chain class.
 *
 * @package Sherv\Container\Tests
 * @since   X.X.X
 */

declare( strict_types=1 );

namespace Sherv\Container\Tests\Resolver;

use Sherv\Container\Exception\Failed_Resolution_Exception;
use Sherv\Container\Resolver\Resolver_Chain;
use Sherv\Container\Tests\Fixtures\Always_Resolving_Resolver_Stub;
use Sherv\Container\Tests\Fixtures\Never_Resolving_Resolver_Stub;
use Sherv\Container\Tests\Test_Case;

class Resolver_Chain_Test extends Test_Case {

	public function test_resolve_uses_first_capable_resolver(): void {
		$chain = new Resolver_Chain(
			[
				new Never_Resolving_Resolver_Stub(),
				new Always_Resolving_Resolver_Stub(),
			]
		);

		$result = $chain->resolve( 'anything' );

		$this->assertInstanceOf( \stdClass::class, $result );
	}

	public function test_resolve_throws_when_no_resolver_can_handle(): void {
		$chain = new Resolver_Chain(
			[
				new Never_Resolving_Resolver_Stub(),
			]
		);

		$this->expectException( Failed_Resolution_Exception::class );

		$chain->resolve( 'anything' );
	}

	public function test_is_resolvable_returns_true_when_any_resolver_can_handle(): void {
		$chain = new Resolver_Chain(
			[
				new Never_Resolving_Resolver_Stub(),
				new Always_Resolving_Resolver_Stub(),
			]
		);

		$this->assertTrue( $chain->is_resolvable( 'anything' ) );
	}

	public function test_is_resolvable_returns_false_when_no_resolver_can_handle(): void {
		$chain = new Resolver_Chain(
			[
				new Never_Resolving_Resolver_Stub(),
			]
		);

		$this->assertFalse( $chain->is_resolvable( 'anything' ) );
	}

	public function test_resolve_with_empty_chain_throws(): void {
		$chain = new Resolver_Chain( [] );

		$this->expectException( Failed_Resolution_Exception::class );

		$chain->resolve( 'anything' );
	}
}
