<?php
/**
 * Contains the Plugin_Authorization_Expired_Exception class.
 *
 * @package skaut-google-drive-gallery
 */

namespace Sgdg\Exceptions;

use Sgdg\Exceptions\Exception as Sgdg_Exception;

/**
 * The permissions granted to the plugin are no longer valid.
 *
 * This happens e.g. when the permissions expire (which is the case after 7 days for Google apps in testing mode), when they are revoked, or when the Google app (OAuth client) of the plugin is deleted.
 */
final class Plugin_Authorization_Expired_Exception extends Sgdg_Exception {

	/**
	 * Plugin_Authorization_Expired_Exception class constructor
	 *
	 * @param string $error The OAuth error returned by Google, e.g. `invalid_grant` or `invalid_client`.
	 */
	public function __construct( $error ) {
		parent::__construct(
			'invalid_client' === $error
				? sprintf(
					/* translators: 1: Start of link to the settings 2: End of link to the settings */
					esc_html__(
						// phpcs:ignore SlevomatCodingStandard.Files.LineLength.LineTooLong
						'The Google app used by Google Drive gallery can no longer be used - it may have been deleted or its credentials changed. If you are the website administrator, check the client ID and secret and grant the permissions again in the %1$splugin settings%2$s.',
						'skaut-google-drive-gallery'
					),
					'<a href="' . esc_url( admin_url( 'admin.php?page=sgdg_basic' ) ) . '">',
					'</a>'
				)
				: sprintf(
					/* translators: 1: Start of link to the settings 2: End of link to the settings */
					esc_html__(
						// phpcs:ignore SlevomatCodingStandard.Files.LineLength.LineTooLong
						'The permissions granted to Google Drive gallery have expired or were revoked. If you are the website administrator, grant them again in the %1$splugin settings%2$s.',
						'skaut-google-drive-gallery'
					),
					'<a href="' . esc_url( admin_url( 'admin.php?page=sgdg_basic' ) ) . '">',
					'</a>'
				)
		);
	}
}
