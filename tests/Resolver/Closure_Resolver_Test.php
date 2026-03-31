<?php
/**
 * Tests for the Closure_Resolver class.
 *
 * @package Sherv\Container\Tests
 * @since   X.X.X
 */

declare( strict_types=1 );

namespace Sherv\Container\Tests\Resolver;

use Sherv\Container\Container;
use Sherv\Container\Exception\Failed_Resolution_Exception;
use Sherv\Container\Resolver\Closure_Resolver;
use Sherv\Container\Tests\Test_Case;

class Closure_Resolver_Test extends Test_Case {

	private Closure_Resolver $resolver;

	protected function setUp(): void {
		parent::setUp();

		$this->resolver = new Closure_Resolver( new Container() );
	}

	public function test_resolve_closure(): void {
		$closure = static fn() => 'resolved';

		$result = $this->resolver->resolve( $closure );

		$this->assertSame( 'resolved', $result );
	}

	public function test_resolve_closure_with_container_access(): void {
		$container = new Container();
		$container->bind( 'foo', 'bar' );

		$resolver = new Closure_Resolver( $container );

		$closure = static fn ( $container ) => $container->make( 'foo' );

		$result = $resolver->resolve( $closure );

		$this->assertSame( 'bar', $result );
	}

	public function test_resolve_closure_receives_with_parameters(): void {
		$closure = static fn ( $container, $with ) => $with;

		$result = $this->resolver->resolve( $closure, [ 'key' => 'value' ] );

		$this->assertSame( [ 'key' => 'value' ], $result );
	}

	public function test_resolve_throws_for_non_closure(): void {
		$this->expectException( Failed_Resolution_Exception::class );

		$this->resolver->resolve( 'not_a_closure' );
	}

	public function test_is_resolvable_returns_true_for_closure(): void {
		$this->assertTrue( $this->resolver->is_resolvable( static fn() => null ) );
	}

	public function test_is_resolvable_returns_false_for_string(): void {
		$this->assertFalse( $this->resolver->is_resolvable( 'string' ) );
	}

	public function test_is_resolvable_returns_false_for_object(): void {
		$this->assertFalse( $this->resolver->is_resolvable( new \stdClass() ) );
	}

	public function test_is_resolvable_returns_false_for_null(): void {
		$this->assertFalse( $this->resolver->is_resolvable( null ) );
	}

	public function test_is_resolvable_returns_false_for_array(): void {
		$this->assertFalse( $this->resolver->is_resolvable( [] ) );
	}
}
