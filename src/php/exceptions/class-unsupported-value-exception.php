<?php
/**
 * Contains the Unsupported_Value_Exception class.
 *
 * @package skaut-google-drive-gallery
 */

namespace Sgdg\Exceptions;

use Sgdg\Exceptions\Exception as Sgdg_Exception;
use Sgdg\Frontend\API_Fields;

/**
 * A value that is not supported was passed.
 */
final class Unsupported_Value_Exception extends Sgdg_Exception {

	/**
	 * Unsupported_Value_Exception class constructor
	 *
	 * @param string|array<string>|API_Fields $value The name of the value(s).
	 * @param string                          $fn_name The name of the function the value was passed to.
	 */
	public function __construct( $value, $fn_name ) {
		if ( is_array( $value ) ) {
			$value = implode( ', ', $value );
		}

		if ( $value instanceof API_Fields ) {
			$value = $value->format();
		}

		parent::__construct(
			sprintf(
				/* translators: 1: The name of the value that isn't supported 2: The name of the function the value was passed to */
				esc_html__(
					'The value "%1$s" has been passed to the function "%2$s" but is not supported by it.',
					'skaut-google-drive-gallery'
				),
				$value,
				$fn_name
			)
		);
	}
}
