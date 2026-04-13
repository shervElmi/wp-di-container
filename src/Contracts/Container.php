<?php
/**
 * Interface Container.
 *
 * @package Sherv\Container
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Sherv\Container\Contracts;

use ArrayAccess;
use Closure;
use Psr\Container\ContainerInterface;

/**
 * Contract for a dependency injection container.
 *
 * @since 1.0.0
 */
interface Container extends ContainerInterface, ArrayAccess {

	/**
	 * Add a binding to the container.
	 *
	 * @since 1.0.0
	 *
	 * @param string $id     The binding identifier.
	 * @param mixed  $entry  The entry to be bound.
	 * @param bool   $shared Whether the binding is shared.
	 * @return void
	 */
	public function bind( string $id, mixed $entry = null, bool $shared = false ): void;

	/**
	 * Add a shared binding to the container.
	 *
	 * @since 1.0.0
	 *
	 * @param string $id    The binding identifier.
	 * @param mixed  $entry The entry to be bound.
	 * @return void
	 */
	public function singleton( string $id, mixed $entry = null ): void;

	/**
	 * Extend an existing binding in the container.
	 *
	 * @since 1.0.0
	 *
	 * @param string   $id      The binding identifier.
	 * @param \Closure $closure The closure to extend the binding.
	 * @return void
	 */
	public function extend( string $id, Closure $closure ): void;

	/**
	 * Resolve an entry from the container.
	 *
	 * @since 1.0.0
	 *
	 * @param string $id   The identifier, either an entry ID or a class name.
	 * @param array  $with Parameters to pass during the resolving of the entry.
	 * @return mixed
	 *
	 * @throws \Sherv\Container\Exception\FailedResolutionException When the entry cannot be resolved.
	 */
	public function make( string $id, array $with = [] ): mixed;
}
