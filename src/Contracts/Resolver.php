<?php
/**
 * Interface Resolver.
 *
 * @package Sherv\Container
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Sherv\Container\Contracts;

/**
 * Contract for dependency resolution.
 *
 * @since 1.0.0
 */
interface Resolver {

	/**
	 * Resolve the given entry from the container.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $entry The entry to be resolved.
	 * @param array $with  Parameters to pass during the resolving of the entry.
	 * @return mixed
	 *
	 * @throws \Sherv\Container\Exception\FailedResolutionException When the entry cannot be resolved.
	 */
	public function resolve( mixed $entry, array $with = [] ): mixed;

	/**
	 * Check if the entry can be resolved.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $entry The entry to check.
	 * @return bool
	 */
	public function is_resolvable( mixed $entry ): bool;
}
