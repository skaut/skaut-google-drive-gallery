<?php
/**
 * Contains the API_Exception class.
 *
 * @package skaut-google-drive-gallery
 */

namespace Sgdg\Exceptions;

/**
 * A wrapper for an exception with the API
 */
class API_Exception extends Exception {
	/**
	 * API_Exception class constructor
	 *
	 * @param \Sgdg\Vendor\Google_Service_Exception $api_exception The original API exception.
	 */
	public function __construct( $api_exception ) {
		parent::__construct( $api_exception->getMessage(), $api_exception->getCode(), $api_exception );
	}
}
