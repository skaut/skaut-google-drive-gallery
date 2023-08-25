<?php
/**
 * Contains the Plugin_Not_Authorized_Exception class.
 *
 * @package skaut-google-drive-gallery
 */

namespace Sgdg\Exceptions;

use Sgdg\Exceptions\Exception as Sgdg_Exception;

/**
 * The requested path doesn't exist in this gallery.
 */
final class Plugin_Not_Authorized_Exception extends Sgdg_Exception {

	/**
	 * Plugin_Not_Authorized_Exception class constructor
	 */
	public function __construct() {
		parent::__construct(
			sprintf(
				/* translators: 1: Start of link to the settings 2: End of link to the settings */
				esc_html__(
					// phpcs:ignore SlevomatCodingStandard.Files.LineLength.LineTooLong
					'Google Drive gallery hasn\'t been granted permissions yet. If you are the website administrator, you can %1$sconfigure%2$s it in the plugin settings.',
					'skaut-google-drive-gallery'
				),
				'<a href="' . esc_url( admin_url( 'admin.php?page=sgdg_basic' ) ) . '">',
				'</a>'
			)
		);
	}
}
