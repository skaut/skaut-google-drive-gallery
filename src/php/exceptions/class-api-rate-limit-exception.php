<?php
/**
 * Contains the API_Rate_Limit_Exception class.
 *
 * @package skaut-google-drive-gallery
 */

namespace Sgdg\Exceptions;

use Sgdg\Exceptions\Exception as Sgdg_Exception;

/**
 * A wrapper for a rate limit exception with the API
 */
final class API_Rate_Limit_Exception extends Sgdg_Exception {

	/**
	 * API_Rate_Limit_Exception class constructor
	 *
	 * @param \Sgdg\Vendor\Google\Service\Exception $api_exception The original API exception.
	 */
	public function __construct( $api_exception ) {
		parent::__construct(
			esc_html__(
				'The maximum number of requests has been exceeded. Please try again in a minute.',
				'skaut-google-drive-gallery'
			),
			$api_exception->getCode(),
			$api_exception
		);
	}

}
