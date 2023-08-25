<?php
/**
 * Contains the Cant_Manage_Exception class.
 *
 * @package skaut-google-drive-gallery
 */

namespace Sgdg\Exceptions;

use Sgdg\Exceptions\Exception as Sgdg_Exception;

/**
 * Can't edit posts and pages
 */
final class Cant_Manage_Exception extends Sgdg_Exception {

	/**
	 * Cant_Manage_Exception class constructor
	 */
	public function __construct() {
		parent::__construct(
			esc_html__(
				'Insufficient role for this action - you have to be able to manage page options.',
				'skaut-google-drive-gallery'
			)
		);
	}
}
