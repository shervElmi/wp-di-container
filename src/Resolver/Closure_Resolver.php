<?php
/**
 * Class Closure_Resolver.
 *
 * @package Sherv\Container
 * @since   X.X.X
 */

declare( strict_types=1 );

namespace Sherv\Container\Resolver;

use Closure;
use Sherv\Container\Exception\Failed_Resolution_Exception;
use Sherv\Container\Contracts\Container;
use Sherv\Container\Contracts\Resolver;

/**
 * Resolves an entry by invoking closures with given parameters.
 *
 * @since X.X.X
 */
final readonly class Closure_Resolver implements Resolver {

	/**
	 * Create a new closure resolver instance.
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
			throw Failed_Resolution_Exception::for_invalid_closure( $entry ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
		}

		return $entry( $this->container, $with );
	}

	/**
	 * {@inheritDoc}
	 */
	public function is_resolvable( mixed $entry ): bool {
		return $entry instanceof Closure;
	}
}
