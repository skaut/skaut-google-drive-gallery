<?php
/**
 * Contains the Root_Not_Found_Exception class.
 *
 * @package skaut-google-drive-gallery
 */

namespace Sgdg\Exceptions;

use Sgdg\Exceptions\Exception as Sgdg_Exception;

/**
 * The root directory of the gallery doesn't exist.
 */
final class Root_Not_Found_Exception extends Sgdg_Exception {

	/**
	 * Root_Not_Found_Exception class constructor
	 */
	public function __construct() {
		parent::__construct(
			esc_html__(
				"The root directory of the gallery couldn't be found - it may have been deleted or renamed.",
				'skaut-google-drive-gallery'
			)
		);
	}

}
