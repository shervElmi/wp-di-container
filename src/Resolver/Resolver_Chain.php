<?php
/**
 * Class Resolver_Chain.
 *
 * @package Sherv\Container
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Sherv\Container\Resolver;

use Sherv\Container\Exception\Failed_Resolution_Exception;
use Sherv\Container\Contracts\Resolver;

/**
 * Chains resolvers to handle dependency resolution in sequence.
 *
 * @since 1.0.0
 */
final readonly class Resolver_Chain implements Resolver {

	/**
	 * Create a new resolver chain instance.
	 *
	 * @since 1.0.0
	 *
	 * @param Resolver[] $resolvers List of resolvers to use in the chain.
	 */
	public function __construct( private array $resolvers = [] ) {
	}

	/**
	 * {@inheritDoc}
	 *
	 * Attempts to resolve a dependency using a series of resolvers.
	 */
	public function resolve( mixed $entry, array $with = [] ): mixed {
		foreach ( $this->resolvers as $resolver ) {
			if ( $resolver->is_resolvable( $entry ) ) {
				return $resolver->resolve( $entry, $with );
			}
		}

		throw Failed_Resolution_Exception::for_unresolvable_entry( $entry ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
	}

	/**
	 * {@inheritDoc}
	 *
	 * Checks if any resolver in the chain can resolve the entry.
	 */
	public function is_resolvable( mixed $entry ): bool {
		foreach ( $this->resolvers as $resolver ) {
			if ( $resolver->is_resolvable( $entry ) ) {
				return true;
			}
		}

		return false;
	}
}
