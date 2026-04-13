<?php
/**
 * Class Entry_Not_Found_Exception.
 *
 * @package Sherv\Container
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Sherv\Container\Exception;

use Psr\Container\NotFoundExceptionInterface;
use InvalidArgumentException;

/**
 * Exception thrown when an entry is not found in the container.
 *
 * @since 1.0.0
 */
final class Entry_Not_Found_Exception extends InvalidArgumentException implements NotFoundExceptionInterface {

	/**
	 * Create a new exception for an unrecognized service identifier.
	 *
	 * @since 1.0.0
	 *
	 * @param string $id The identifier of the entry that was not found.
	 * @return self
	 */
	public static function for_entry_id( string $id ): self {
		$message = sprintf(
			'No entry found for identifier "%s".',
			esc_html( $id )
		);

		return new self( $message );
	}
}
