<?php
/**
 * Tests for the Container class.
 *
 * @package Sherv\Container\Tests
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Sherv\Container\Tests;

use Sherv\Container\Container;
use Sherv\Container\Exception\Entry_Not_Found_Exception;
use Sherv\Container\Exception\Failed_Resolution_Exception;
use Sherv\Container\Tests\Fixtures\Dummy_Circular_A;
use Sherv\Container\Tests\Fixtures\Dummy_Concrete;
use Sherv\Container\Tests\Fixtures\Dummy_Contract;
use Sherv\Container\Tests\Fixtures\Dummy_Dependent;
use Sherv\Container\Tests\Fixtures\Dummy_Implementation;

class Container_Test extends Test_Case {

	private Container $container;

	protected function setUp(): void {
		parent::setUp();

		$this->container = new Container();
	}

	public function test_bind_and_make_concrete_class(): void {
		$this->container->bind( Dummy_Concrete::class );

		$result = $this->container->make( Dummy_Concrete::class );

		$this->assertInstanceOf( Dummy_Concrete::class, $result );
	}

	public function test_bind_interface_to_implementation(): void {
		$this->container->bind( Dummy_Contract::class, Dummy_Implementation::class );

		$result = $this->container->make( Dummy_Contract::class );

		$this->assertInstanceOf( Dummy_Implementation::class, $result );
	}

	public function test_bind_closure(): void {
		$this->container->bind( 'foo', static fn() => 'bar' );

		$result = $this->container->make( 'foo' );

		$this->assertSame( 'bar', $result );
	}

	public function test_singleton_returns_same_instance(): void {
		$this->container->singleton( Dummy_Concrete::class );

		$first  = $this->container->make( Dummy_Concrete::class );
		$second = $this->container->make( Dummy_Concrete::class );

		$this->assertSame( $first, $second );
	}

	public function test_non_singleton_returns_different_instances(): void {
		$this->container->bind( Dummy_Concrete::class );

		$first  = $this->container->make( Dummy_Concrete::class );
		$second = $this->container->make( Dummy_Concrete::class );

		$this->assertNotSame( $first, $second );
	}

	public function test_extend_modifies_shared_entry(): void {
		$this->container->bind( 'foo', 'bar' );

		$this->container->extend( 'foo', static fn ( $value ) => $value . '_extended' );

		$this->assertSame( 'bar_extended', $this->container->make( 'foo' ) );
	}

	public function test_extend_modifies_resolved_entry(): void {
		$this->container->bind( Dummy_Concrete::class );
		$this->container->make( Dummy_Concrete::class );

		$this->container->extend(
			Dummy_Concrete::class,
			static function ( $instance, $_container ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable, Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
				$instance->extended = true;

				return $instance;
			}
		);

		$result = $this->container->make( Dummy_Concrete::class );

		$this->assertTrue( $result->extended );
	}

	public function test_has_returns_true_for_bound_entry(): void {
		$this->container->bind( Dummy_Concrete::class );

		$this->assertTrue( $this->container->has( Dummy_Concrete::class ) );
	}

	public function test_has_returns_false_for_unbound_entry(): void {
		$this->assertFalse( $this->container->has( 'nonexistent' ) );
	}

	public function test_get_throws_entry_not_found_for_unbound_entry(): void {
		$this->expectException( Entry_Not_Found_Exception::class );

		$this->container->get( 'nonexistent' );
	}

	public function test_get_rethrows_when_registered_entry_fails(): void {
		$this->container->bind( Dummy_Circular_A::class );

		$this->expectException( Failed_Resolution_Exception::class );

		$this->container->get( Dummy_Circular_A::class );
	}

	public function test_make_throws_failed_resolution_for_circular_dependency(): void {
		$this->expectException( Failed_Resolution_Exception::class );

		$this->container->make( Dummy_Circular_A::class );
	}

	public function test_make_resolves_auto_dependencies(): void {
		$this->container->bind( Dummy_Contract::class, Dummy_Implementation::class );

		$result = $this->container->make( Dummy_Dependent::class );

		$this->assertInstanceOf( Dummy_Dependent::class, $result );
		$this->assertInstanceOf( Dummy_Implementation::class, $result->dependency );
	}

	public function test_make_concrete_class_without_binding(): void {
		$result = $this->container->make( Dummy_Concrete::class );

		$this->assertInstanceOf( Dummy_Concrete::class, $result );
	}

	public function test_bind_scalar_value_as_shared_entry(): void {
		$this->container->bind( 'config.key', 'some_value' );

		$this->assertSame( 'some_value', $this->container->make( 'config.key' ) );
	}

	public function test_offset_exists_returns_true_for_bound_entry(): void {
		$this->container->bind( 'foo', 'bar' );

		$this->assertTrue( isset( $this->container['foo'] ) );
	}

	public function test_offset_get_returns_resolved_entry(): void {
		$this->container->bind( 'foo', 'bar' );

		$this->assertSame( 'bar', $this->container['foo'] );
	}

	public function test_offset_set_binds_entry(): void {
		$this->container['foo'] = 'bar';

		$this->assertSame( 'bar', $this->container->make( 'foo' ) );
	}

	public function test_offset_unset_removes_entry(): void {
		$this->container->bind( 'foo', 'bar' );

		unset( $this->container['foo'] );

		$this->assertFalse( $this->container->has( 'foo' ) );
	}

	public function test_re_binding_triggers_re_resolution(): void {
		$this->container->bind( 'foo', static fn() => 'first' );
		$this->container->make( 'foo' );

		$this->container->bind( 'foo', static fn() => 'second' );

		$this->assertSame( 'second', $this->container->make( 'foo' ) );
	}

	public function test_get_bindings_returns_registered_bindings(): void {
		$this->container->bind( Dummy_Concrete::class );
		$this->container->bind( Dummy_Implementation::class );

		$bindings = $this->container->get_bindings();

		$this->assertArrayHasKey( Dummy_Concrete::class, $bindings );
		$this->assertArrayHasKey( Dummy_Implementation::class, $bindings );
	}

	public function test_forget_extenders_removes_extenders(): void {
		$this->container->bind( Dummy_Concrete::class );

		$this->container->extend(
			Dummy_Concrete::class,
			static function ( $instance ) {
				$instance->extended = true;

				return $instance;
			}
		);

		$this->container->forget_extenders( Dummy_Concrete::class );

		$result = $this->container->make( Dummy_Concrete::class );

		$this->assertFalse( $result->extended );
	}
}
