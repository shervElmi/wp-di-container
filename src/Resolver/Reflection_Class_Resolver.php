<?php
/**
 * Class Reflection_Class_Resolver.
 *
 * @package Sherv\Container
 * @since   X.X.X
 */

declare( strict_types=1 );

namespace Sherv\Container\Resolver;

use ReflectionClass;
use ReflectionNamedType;
use ReflectionParameter;
use Sherv\Container\Exception\Failed_Resolution_Exception;
use Sherv\Container\Contracts\Container;
use Sherv\Container\Contracts\Resolver;

/**
 * Resolves class dependencies using PHP's Reflection API.
 *
 * @since X.X.X
 */
final readonly class Reflection_Class_Resolver implements Resolver {

	/**
	 * Create a new reflection class resolver instance.
	 *
	 * @since X.X.X
	 *
	 * @param Container $container The container instance.
	 */
	public function __construct( private Container $container ) {
	}

	/**
	 * {@inheritDoc}
	 */
	public function resolve( mixed $entry, array $with = [] ): mixed {
		if ( ! $this->is_resolvable( $entry ) ) {
			throw Failed_Resolution_Exception::for_unreflectable_entry( $entry ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
		}

		$reflection = new ReflectionClass( $entry );

		if ( ! $reflection->isInstantiable() ) {
			throw Failed_Resolution_Exception::for_uninstantiable_entry( $reflection->getName() ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
		}

		$constructor = $reflection->getConstructor();

		if ( is_null( $constructor ) ) {
			return new $entry();
		}

		try {
			$dependencies = $this->resolve_dependencies( $constructor->getParameters(), $with );
		} catch ( Failed_Resolution_Exception $error ) {
			throw $error;
		}

		return $reflection->newInstanceArgs( $dependencies );
	}

	/**
	 * {@inheritDoc}
	 */
	public function is_resolvable( mixed $entry ): bool {
		return is_string( $entry ) && ( class_exists( $entry ) || interface_exists( $entry ) );
	}

	/**
	 * Resolve all dependencies from the given array of ReflectionParameters.
	 *
	 * @since X.X.X
	 *
	 * @param ReflectionParameter[] $dependencies The array of dependencies to resolve.
	 * @param array                 $with         Parameters to pass during the resolving of the entry.
	 * @return array An array of resolved dependencies.
	 */
	private function resolve_dependencies( array $dependencies, array $with ): array {
		// Use the parameter value from $with if it exists, otherwise resolve normally.
		return array_map(
			fn ( ReflectionParameter $dependency ): mixed => array_key_exists( $dependency->name, $with )
				? $with[ $dependency->name ]
				: $this->resolve_dependency( $dependency ),
			$dependencies
		);
	}

	/**
	 * Resolve a single dependency.
	 *
	 * @since X.X.X
	 *
	 * @param ReflectionParameter $dependency The dependency to resolve.
	 * @return mixed The resolved dependency.
	 */
	private function resolve_dependency( ReflectionParameter $dependency ): mixed {
		$class_name = $this->resolve_parameter_class_name( $dependency );

		// Determine if the dependency is a class or a primitive type and resolve accordingly.
		return $class_name
			? $this->resolve_class( $dependency, $class_name )
			: $this->resolve_primitive( $dependency );
	}

	/**
	 * Resolve a class-based dependency from the container.
	 *
	 * @since X.X.X
	 *
	 * @param ReflectionParameter $dependency The dependency to resolve.
	 * @param class-string        $class_name The name of the class to resolve.
	 * @return mixed The resolved dependency.
	 *
	 * @throws Failed_Resolution_Exception When the class cannot be resolved.
	 */
	private function resolve_class( ReflectionParameter $dependency, string $class_name ): mixed {
		try {
			return $this->container->make( $class_name );
		} catch ( Failed_Resolution_Exception $error ) {
			if ( $dependency->isDefaultValueAvailable() ) {
				return $dependency->getDefaultValue();
			}

			throw $error;
		}
	}

	/**
	 * Resolve a non-class (primitive) dependency.
	 *
	 * @since X.X.X
	 *
	 * @param ReflectionParameter $dependency The dependency to resolve.
	 * @return mixed The resolved dependency value.
	 *
	 * @throws Failed_Resolution_Exception When the primitive cannot be resolved.
	 */
	private function resolve_primitive( ReflectionParameter $dependency ): mixed {
		return $dependency->isDefaultValueAvailable()
			? $dependency->getDefaultValue()
			: throw Failed_Resolution_Exception::for_unresolvable_primitive( $dependency ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
	}

	/**
	 * Get the resolved class name from a reflected parameter's type.
	 *
	 * Maps `self` and `parent` keywords to their actual class names.
	 * Returns null for built-in types or untyped parameters.
	 *
	 * @since X.X.X
	 *
	 * @param ReflectionParameter $param The reflected parameter.
	 * @return string|null The resolved class name, or null for non-class types.
	 */
	private function resolve_parameter_class_name( ReflectionParameter $param ): ?string {
		$type = $param->getType();

		if ( ! $type instanceof ReflectionNamedType || $type->isBuiltin() ) {
			return null;
		}

		$name  = $type->getName();
		$class = $param->getDeclaringClass();

		return match ( $name ) {
			'self'   => $class?->getName(),
			'parent' => $class?->getParentClass()?->getName(),
			default  => $name,
		};
	}
}
