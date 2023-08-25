<?php
/**
 * Contains the Not_Found_Exception class.
 *
 * @package skaut-google-drive-gallery
 */

namespace Sgdg\Exceptions;

use Sgdg\Exceptions\Exception as Sgdg_Exception;

/**
 * The requested resource wasn't found.
 */
final class Not_Found_Exception extends Sgdg_Exception {

	/**
	 * Not_Found_Exception class constructor
	 */
	public function __construct() {
		parent::__construct( esc_html__( "The requested resource couldn't be found.", 'skaut-google-drive-gallery' ) );
	}
}
