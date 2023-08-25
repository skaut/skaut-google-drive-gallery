<?php
/**
 * Contains the Drive_Not_Found_Exception class.
 *
 * @package skaut-google-drive-gallery
 */

namespace Sgdg\Exceptions;

use Sgdg\Exceptions\Exception as Sgdg_Exception;

/**
 * The requested drive wasn't found.
 */
final class Drive_Not_Found_Exception extends Sgdg_Exception {

	/**
	 * Drive_Not_Found_Exception class constructor
	 */
	public function __construct() {
		parent::__construct(
			esc_html__( "The requested shared drive couldn't be found.", 'skaut-google-drive-gallery' )
		);
	}
}
