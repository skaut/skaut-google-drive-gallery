<?php
/**
 * Contains the Internal_Exception class.
 *
 * @package skaut-google-drive-gallery
 */

namespace Sgdg\Exceptions;

/**
 * An internal exception
 */
class Internal_Exception extends Exception {

	/**
	 * Internal_Exception class constructor
	 */
	public function __construct() {
		parent::__construct( esc_html__( 'An internal error happened in the gallery.', 'skaut-google-drive-gallery' ) );
	}

}
