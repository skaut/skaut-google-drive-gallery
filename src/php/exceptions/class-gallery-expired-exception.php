<?php
/**
 * Contains the Gallery_Expired_Exception class.
 *
 * @package skaut-google-drive-gallery
 */

namespace Sgdg\Exceptions;

use Sgdg\Exceptions\Exception as Sgdg_Exception;

/**
 * The requested path doesn't exist in this gallery.
 */
final class Gallery_Expired_Exception extends Sgdg_Exception {

	/**
	 * Gallery_Expired_Exception class constructor
	 */
	public function __construct() {
		parent::__construct( esc_html__( 'The gallery has expired.', 'skaut-google-drive-gallery' ) );
	}
}
