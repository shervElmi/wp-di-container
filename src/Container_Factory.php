<?php
/**
 * Class Container_Factory.
 *
 * @package Sherv\Container
 * @since   X.X.X
 */

declare( strict_types=1 );

namespace Sherv\Container;

/**
 * Static factory for a shared Container singleton.
 *
 * @since X.X.X
 */
final class Container_Factory {

	/**
	 * The shared Container instance.
	 *
	 * @since X.X.X
	 *
	 * @var Container|null
	 */
	private static ?Container $container = null;

	/**
	 * Get the shared Container instance, creating it if necessary.
	 *
	 * @since X.X.X
	 *
	 * @return Container
	 */
	public static function create(): Container {
		self::$container ??= new Container();

		return self::$container;
	}

	/**
	 * Reset the factory state. Primarily useful for testing.
	 *
	 * @since X.X.X
	 *
	 * @return void
	 */
	public static function reset(): void {
		self::$container = null;
	}
}
