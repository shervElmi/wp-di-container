<?php
/**
 * Class Failed_Resolution_Exception.
 *
 * @package Sherv\Container
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Sherv\Container\Exception;

use Psr\Container\ContainerExceptionInterface;
use ReflectionParameter;
use RuntimeException;

/**
 * Exception thrown when a resolution process fails in the container.
 *
 * @since 1.0.0
 */
final class Failed_Resolution_Exception extends RuntimeException implements ContainerExceptionInterface {

	/**
	 * Create a new exception for a detected circular dependency.
	 *
	 * @since 1.0.0
	 *
	 * @param string $id The identifier of the entry causing the circular dependency.
	 * @return self
	 */
	public static function for_circular_dependency( string $id ): self {
		$message = sprintf(
			'Circular dependency detected for "%s".',
			esc_html( $id )
		);

		return new self( $message );
	}

	/**
	 * Create a new exception for an entry that cannot be resolved.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $entry The entry that could not be resolved.
	 * @return self
	 */
	public static function for_unresolvable_entry( mixed $entry ): self {
		$message = sprintf(
			'Cannot resolve entry "%s".',
			esc_html( (string) $entry )
		);

		return new self( $message );
	}

	/**
	 * Create a new exception for an entry that is not a valid closure.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $entry The entry to be resolved.
	 * @return self
	 */
	public static function for_invalid_closure( mixed $entry ): self {
		$message = sprintf(
			'The provided entry "%s" is not a valid closure.',
			esc_html( (string) $entry )
		);

		return new self( $message );
	}

	/**
	 * Create a new exception for an entry that cannot be reflected.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $entry The entry that cannot be reflected.
	 * @return self
	 */
	public static function for_unreflectable_entry( mixed $entry ): self {
		$message = sprintf(
			'Cannot reflect on the class or interface "%s". It may not be valid or does not exist.',
			esc_html( (string) $entry )
		);

		return new self( $message );
	}

	/**
	 * Create a new exception for an entry that cannot be instantiated.
	 *
	 * @since 1.0.0
	 *
	 * @param string $reflection_name The name of the entry that cannot be instantiated.
	 * @return self
	 */
	public static function for_uninstantiable_entry( string $reflection_name ): self {
		$message = sprintf(
			'Cannot instantiate entry "%s". It may be an interface or an abstract class, probably forgot to bind an implementation.',
			esc_html( $reflection_name )
		);

		return new self( $message );
	}

	/**
	 * Create a new exception for an unresolvable primitive.
	 *
	 * @since 1.0.0
	 *
	 * @param ReflectionParameter $dependency The dependency that could not be resolved.
	 * @return self
	 */
	public static function for_unresolvable_primitive( ReflectionParameter $dependency ): self {
		$message = sprintf(
			'Unresolvable dependency "%s" in class "%s".',
			esc_html( '$' . $dependency->getName() ),
			esc_html( $dependency->getDeclaringClass()->getName() )
		);

		return new self( $message );
	}
}
