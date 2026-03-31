<?php
/**
 * Tests for the Reflection_Class_Resolver class.
 *
 * @package Sherv\Container\Tests
 * @since   X.X.X
 */

declare( strict_types=1 );

namespace Sherv\Container\Tests\Resolver;

use Sherv\Container\Container;
use Sherv\Container\Exception\Failed_Resolution_Exception;
use Sherv\Container\Resolver\Reflection_Class_Resolver;
use Sherv\Container\Tests\Fixtures\Dummy_Concrete;
use Sherv\Container\Tests\Fixtures\Dummy_Contract;
use Sherv\Container\Tests\Fixtures\Dummy_Dependent;
use Sherv\Container\Tests\Fixtures\Dummy_Implementation;
use Sherv\Container\Tests\Fixtures\Dummy_Nested_Dependent;
use Sherv\Container\Tests\Fixtures\Dummy_Child_With_Parent_Ref;
use Sherv\Container\Tests\Fixtures\Dummy_Self_Referencing;
use Sherv\Container\Tests\Fixtures\Dummy_With_Default_Param_Value;
use Sherv\Container\Tests\Fixtures\Dummy_With_Mixed_Params;
use Sherv\Container\Tests\Test_Case;

class Reflection_Class_Resolver_Test extends Test_Case {

	private Container $container;
	private Reflection_Class_Resolver $resolver;

	protected function setUp(): void {
		parent::setUp();

		$this->container = new Container();
		$this->resolver  = new Reflection_Class_Resolver( $this->container );
	}

	public function test_resolve_concrete_class(): void {
		$result = $this->resolver->resolve( Dummy_Concrete::class );

		$this->assertInstanceOf( Dummy_Concrete::class, $result );
	}

	public function test_resolve_class_with_dependencies(): void {
		$this->container->bind( Dummy_Contract::class, Dummy_Implementation::class );

		$result = $this->resolver->resolve( Dummy_Dependent::class );

		$this->assertInstanceOf( Dummy_Dependent::class, $result );
		$this->assertInstanceOf( Dummy_Implementation::class, $result->dependency );
	}

	public function test_resolve_class_with_nested_dependencies(): void {
		$this->container->bind( Dummy_Contract::class, Dummy_Implementation::class );

		$result = $this->resolver->resolve( Dummy_Nested_Dependent::class );

		$this->assertInstanceOf( Dummy_Nested_Dependent::class, $result );
		$this->assertInstanceOf( Dummy_Dependent::class, $result->dependency );
		$this->assertInstanceOf( Dummy_Implementation::class, $result->dependency->dependency );
	}

	public function test_resolve_class_with_default_parameter_value(): void {
		$result = $this->resolver->resolve( Dummy_With_Default_Param_Value::class );

		$this->assertInstanceOf( Dummy_With_Default_Param_Value::class, $result );
		$this->assertSame( 'default', $result->name );
	}

	public function test_resolve_class_with_explicit_parameters(): void {
		$result = $this->resolver->resolve( Dummy_With_Mixed_Params::class, [ 'name' => 'custom' ] );

		$this->assertInstanceOf( Dummy_With_Mixed_Params::class, $result );
		$this->assertSame( 'custom', $result->name );
	}

	public function test_resolve_throws_for_unresolvable_entry(): void {
		$this->expectException( Failed_Resolution_Exception::class );

		$this->resolver->resolve( 'not_a_class' );
	}

	public function test_resolve_throws_for_interface(): void {
		$this->expectException( Failed_Resolution_Exception::class );

		$this->resolver->resolve( Dummy_Contract::class );
	}

	public function test_resolve_throws_for_unresolvable_primitive(): void {
		$this->expectException( Failed_Resolution_Exception::class );

		$this->resolver->resolve( Dummy_With_Mixed_Params::class );
	}

	public function test_is_resolvable_returns_true_for_class(): void {
		$this->assertTrue( $this->resolver->is_resolvable( Dummy_Concrete::class ) );
	}

	public function test_is_resolvable_returns_true_for_interface(): void {
		$this->assertTrue( $this->resolver->is_resolvable( Dummy_Contract::class ) );
	}

	public function test_is_resolvable_returns_false_for_closure(): void {
		$this->assertFalse( $this->resolver->is_resolvable( static fn() => null ) );
	}

	public function test_is_resolvable_returns_false_for_string(): void {
		$this->assertFalse( $this->resolver->is_resolvable( 'nonexistent_class' ) );
	}

	public function test_is_resolvable_returns_false_for_null(): void {
		$this->assertFalse( $this->resolver->is_resolvable( null ) );
	}

	public function test_resolve_class_with_self_type_hint(): void {
		$result = $this->resolver->resolve( Dummy_Self_Referencing::class );

		$this->assertInstanceOf( Dummy_Self_Referencing::class, $result );
		$this->assertInstanceOf( Dummy_Self_Referencing::class, $result->parent );
		$this->assertNull( $result->parent->parent );
	}

	public function test_resolve_class_with_parent_type_hint(): void {
		$result = $this->resolver->resolve( Dummy_Child_With_Parent_Ref::class );

		$this->assertInstanceOf( Dummy_Child_With_Parent_Ref::class, $result );
		$this->assertInstanceOf( Dummy_Concrete::class, $result->ref );
	}
}
