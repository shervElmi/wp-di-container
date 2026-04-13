<?php
/**
 * Class Container_Factory.
 *
 * @package Sherv\Container
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Sherv\Container;

/**
 * Static factory for a shared Container singleton.
 *
 * @since 1.0.0
 */
final class Container_Factory {

	/**
	 * The shared Container instance.
	 *
	 * @since 1.0.0
	 *
	 * @var Container|null
	 */
	private static ?Container $container = null;

	/**
	 * Get the shared Container instance, creating it if necessary.
	 *
	 * @since 1.0.0
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
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function reset(): void {
		self::$container = null;
	}
}
