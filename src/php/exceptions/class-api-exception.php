<?php
/**
 * Contains the API_Exception class.
 *
 * @package skaut-google-drive-gallery
 */

namespace Sgdg\Exceptions;

use Sgdg\Exceptions\Exception as Sgdg_Exception;
use Sgdg\Vendor\Google\Service\Exception as Google_Service_Exception;

/**
 * A wrapper for an exception with the API
 */
final class API_Exception extends Sgdg_Exception {

	/**
	 * API_Exception class constructor
	 *
	 * @param Google_Service_Exception $api_exception The original API exception.
	 */
	public function __construct( $api_exception ) {
		$errors = array_column( $api_exception->getErrors(), 'message' );

		parent::__construct(
			esc_html(
				_n(
					'The Google Drive API returned the following error: ',
					'The Google Drive API returned the following errors: ',
					count( $errors ),
					'skaut-google-drive-gallery'
				)
			) . implode( "\n", $errors ),
			$api_exception->getCode(),
			$api_exception
		);
	}

}
