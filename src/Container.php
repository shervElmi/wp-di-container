<?php
/**
 * Class Container.
 *
 * @package Sherv\Container
 * @since   X.X.X
 */

declare( strict_types=1 );

namespace Sherv\Container;

use Closure;
use Exception;
use Sherv\Container\Exception\Entry_Not_Found_Exception;
use Sherv\Container\Exception\Failed_Resolution_Exception;
use Sherv\Container\Resolver\Closure_Resolver;
use Sherv\Container\Resolver\Reflection_Class_Resolver;
use Sherv\Container\Resolver\Resolver_Chain;
use Sherv\Container\Contracts\Container as ContainerContract;
use Sherv\Container\Contracts\Resolver;

/**
 * A dependency injection container that manages and resolves dependencies automatically.
 *
 * @since X.X.X
 */
class Container implements ContainerContract {

	/**
	 * Bindings within the container.
	 *
	 * @since X.X.X
	 *
	 * @var array<string, array{entry: mixed, shared: bool}>
	 */
	protected array $bindings = [];

	/**
	 * Shared entries within the container.
	 *
	 * @since X.X.X
	 *
	 * @var array<string, mixed>
	 */
	protected array $shared_entries = [];

	/**
	 * Extension closures linked to container entries.
	 *
	 * @since X.X.X
	 *
	 * @var array<string, list<Closure>>
	 */
	protected array $extenders = [];

	/**
	 * Resolved entries.
	 *
	 * @since X.X.X
	 *
	 * @var array<string, bool>
	 */
	protected array $resolved = [];

	/**
	 * Entries currently being resolved.
	 *
	 * @since X.X.X
	 *
	 * @var string[]
	 */
	protected array $resolving_stack = [];

	/**
	 * Create a new container instance.
	 *
	 * @since X.X.X
	 *
	 * @param Resolver|null $resolver Optional custom resolver. Defaults to a Resolver_Chain
	 *                                 with Reflection_Class_Resolver and Closure_Resolver.
	 */
	public function __construct( protected ?Resolver $resolver = null ) {
		$this->resolver = $resolver ?? $this->create_default_resolver();
	}

	/**
	 * {@inheritDoc}
	 */
	public function bind( string $id, mixed $entry = null, bool $shared = false ): void {
		// Use the identifier as the default entry if no implementation is provided.
		// This avoids passing the same identifier and entry repeatedly.
		$entry ??= $id;

		// Save the entry as a shared entry if it doesn't need resolution.
		if ( ! $this->resolver->is_resolvable( $entry ) ) {
			$this->shared_entries[ $id ] = $entry;

			return;
		}

		$this->bindings[ $id ] = compact( 'entry', 'shared' );

		if ( $this->resolved( $id ) ) {
			$this->make( $id );
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function singleton( string $id, mixed $entry = null ): void {
		$this->bind( $id, $entry, true );
	}

	/**
	 * {@inheritDoc}
	 */
	public function extend( string $id, Closure $closure ): void {
		if ( isset( $this->shared_entries[ $id ] ) ) {
			$this->shared_entries[ $id ] = $closure( $this->shared_entries[ $id ], $this );
		} else {
			$this->extenders[ $id ][] = $closure;

			if ( $this->resolved( $id ) ) {
				$this->make( $id );
			}
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function make( string $id, array $with = [] ): mixed {
		// If the entry is shared, return the existing entry to ensure the same singleton object.
		// We resolve the shared entry again if new parameters are provided.
		if ( $this->is_shared_entry( $id ) && empty( $with ) ) {
			return $this->shared_entries[ $id ];
		}

		// Check for circular dependency by seeing if the entry is in the resolving stack.
		if ( isset( $this->resolving_stack[ $id ] ) ) {
			throw Failed_Resolution_Exception::for_circular_dependency( $id ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
		}
		$this->resolving_stack[ $id ] = true;

		$entry = $this->bindings[ $id ]['entry'] ?? $id;

		try {
			$object = $this->resolver->resolve( $entry, $with );
		} finally {
			unset( $this->resolving_stack[ $id ] );
		}

		foreach ( $this->get_extenders( $id ) as $extender ) {
			$object = $extender( $object, $this );
		}

		if ( ! empty( $this->bindings[ $id ]['shared'] ) ) {
			$this->shared_entries[ $id ] = $object;
		}

		$this->resolved[ $id ] = true;

		return $object;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws Entry_Not_Found_Exception When the entry is not found.
	 */
	public function get( string $id ): mixed {
		try {
			return $this->make( $id );
		} catch ( Exception $error ) {
			if ( $this->has( $id ) ) {
				throw $error;
			}

			throw Entry_Not_Found_Exception::for_entry_id( $id ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function has( string $id ): bool {
		return isset( $this->bindings[ $id ] ) || isset( $this->shared_entries[ $id ] );
	}

	/**
	 * Create the default resolver chain.
	 *
	 * @since X.X.X
	 *
	 * @return Resolver
	 */
	private function create_default_resolver(): Resolver {
		return new Resolver_Chain(
			[
				new Reflection_Class_Resolver( $this ),
				new Closure_Resolver( $this ),
			]
		);
	}

	/**
	 * Check if the given binding has been resolved.
	 *
	 * @since X.X.X
	 *
	 * @param string $id The binding identifier.
	 * @return bool
	 */
	protected function resolved( string $id ): bool {
		return isset( $this->resolved[ $id ] ) || isset( $this->shared_entries[ $id ] );
	}

	/**
	 * Check if an entry is shared.
	 *
	 * @since X.X.X
	 *
	 * @param string $id The binding identifier.
	 * @return bool
	 */
	protected function is_shared_entry( string $id ): bool {
		return isset( $this->shared_entries[ $id ] );
	}

	/**
	 * Get extender callbacks for a specific entry ID.
	 *
	 * @since X.X.X
	 *
	 * @param string $id The binding identifier.
	 * @return array The extender callbacks.
	 */
	protected function get_extenders( string $id ): array {
		return $this->extenders[ $id ] ?? [];
	}

	/**
	 * Get the container's bindings.
	 *
	 * @return array[]
	 */
	public function get_bindings(): array {
		return $this->bindings;
	}

	/**
	 * Remove all of the extender callbacks for a given entry ID.
	 *
	 * @since X.X.X
	 *
	 * @param string $id The binding identifier.
	 * @return void
	 */
	public function forget_extenders( string $id ): void {
		unset( $this->extenders[ $id ] );
	}

	/**
	 * {@inheritDoc}
	 */
	public function offsetExists( mixed $key ): bool {
		return $this->has( (string) $key );
	}

	/**
	 * {@inheritDoc}
	 */
	public function offsetGet( mixed $key ): mixed {
		return $this->make( (string) $key );
	}

	/**
	 * {@inheritDoc}
	 */
	public function offsetSet( mixed $key, mixed $value ): void {
		$this->bind( (string) $key, $value );
	}

	/**
	 * {@inheritDoc}
	 */
	public function offsetUnset( mixed $key ): void {
		unset( $this->bindings[ $key ], $this->shared_entries[ $key ], $this->resolved[ $key ] );
	}
}
