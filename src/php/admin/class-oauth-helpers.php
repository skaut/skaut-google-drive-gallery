<?php
/**
 * Contains the OAuth_Helpers class.
 *
 * @package skaut-google-drive-gallery
 */

namespace Sgdg\Admin;

use Sgdg\API_Client;
use Sgdg\GET_Helpers;
use Sgdg\Vendor\Google\Service\Drive;
use Sgdg\Vendor\Google\Service\Exception as Google_Service_Exception;
use Sgdg\Vendor\GuzzleHttp\Exception\TransferException;

/**
 * Contains all the OAuth redirect handling functions, called by \Sgdg\Admin\AdminPages\action_handler()
 *
 * @see \Sgdg\Admin\AdminPages\action_handler()
 */
final class OAuth_Helpers {

	/**
	 * Redirects to the OAuth granting URL
	 *
	 * @return void
	 */
	public static function grant_redirect() {
		if ( ! is_admin() ) {
			return;
		}

		$client   = API_Client::get_unauthorized_raw_client();
		$auth_url = $client->createAuthUrl();
		header( 'Location: ' . esc_url_raw( $auth_url ) );
	}

	/**
	 * Handles the redirect back from Google app permission granting and redirects back to basic settings
	 *
	 * @return void
	 */
	public static function grant_return() {
		if ( ! is_admin() ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! isset( $_GET['code'] ) ) {
			add_settings_error(
				'general',
				'oauth_failed',
				esc_html__(
					'Google API hasn\'t returned an authentication code. Please try again.',
					'skaut-google-drive-gallery'
				),
				'error'
			);
		}

		if ( 0 === count( get_settings_errors() ) && false === get_option( 'sgdg_access_token', false ) ) {
			self::fetch_and_check_access_token();
		}

		if ( 0 === count( get_settings_errors() ) ) {
			add_settings_error(
				'general',
				'oauth_updated',
				esc_html__( 'Permission granted.', 'skaut-google-drive-gallery' ),
				'updated'
			);
		}

		set_transient( 'settings_errors', get_settings_errors(), 30 );
		header( 'Location: ' . esc_url_raw( admin_url( 'admin.php?page=sgdg_basic&settings-updated=true' ) ) );
	}

	/**
	 * Revokes and deletes the OAuth token and redirects back to basic settings
	 *
	 * @return void
	 */
	public static function revoke() {
		if ( ! is_admin() ) {
			return;
		}

		$client = API_Client::get_unauthorized_raw_client();

		try {
			$client->revokeToken();
			delete_option( 'sgdg_access_token' );
		} catch ( TransferException $e ) {
			add_settings_error(
				'general',
				'oauth_failed',
				esc_html__( 'An unknown error has been encountered:', 'skaut-google-drive-gallery' ) .
					' ' .
					$e->getMessage(),
				'error'
			);
		}

		if ( 0 === count( get_settings_errors() ) ) {
			add_settings_error(
				'general',
				'oauth_updated',
				__( 'Permission revoked.', 'skaut-google-drive-gallery' ),
				'updated'
			);
		}

		set_transient( 'settings_errors', get_settings_errors(), 30 );
		header( 'Location: ' . esc_url_raw( admin_url( 'admin.php?page=sgdg_basic&settings-updated=true' ) ) );
	}

	/**
	 * Handles the redirect back from Google app permission granting and redirects back to basic settings
	 *
	 * @return void
	 */
	private static function fetch_and_check_access_token() {
		$client = API_Client::get_unauthorized_raw_client();

		try {
			$client->fetchAccessTokenWithAuthCode( GET_Helpers::get_string_variable( 'code' ) );
			$access_token = $client->getAccessToken();

			if ( ! array_key_exists( 'refresh_token', $access_token ) ) {
				add_settings_error(
					'general',
					'oauth_failed',
					esc_html__(
						"The Google authorization API didn't provide a refresh token.",
						'skaut-google-drive-gallery'
					),
					'error'
				);

				return;
			}

			$drive_client = new Drive( $client );
			// phpcs:ignore SlevomatCodingStandard.Functions.RequireSingleLineCall.RequiredSingleLineCall
			$drive_client->drives->listDrives(
				array(
					'fields'   => 'drives(id)',
					'pageSize' => 1,
				)
			);
			update_option( 'sgdg_access_token', $access_token );
		} catch ( Google_Service_Exception $e ) {
			$errors = $e->getErrors();

			if ( null === $errors ) {
				add_settings_error(
					'general',
					'oauth_failed',
					esc_html__( 'An unknown error has been encountered.', 'skaut-google-drive-gallery' ),
					'error'
				);

				return;
			}

			if ( 'accessNotConfigured' === $errors[0]['reason'] ) {
				add_settings_error(
					'general',
					'oauth_failed',
					sprintf(
						/* translators: %s: Link to the Google developers console */
						esc_html__(
							'Google Drive API is not enabled. Please enable it at %s and try again after a while.',
							'skaut-google-drive-gallery'
						),
						'<a href="https://console.developers.google.com/apis/library/drive.googleapis.com" ' .
						'target="_blank">' .
						'https://console.developers.google.com/apis/library/drive.googleapis.com</a>'
					),
					'error'
				);
			} else {
				add_settings_error(
					'general',
					'oauth_failed',
					esc_html__( 'An unknown error has been encountered:', 'skaut-google-drive-gallery' ) .
						' ' .
						$errors[0]['message'],
					'error'
				);
			}
		} catch ( TransferException $e ) {
			add_settings_error(
				'general',
				'oauth_failed',
				esc_html__( 'An unknown error has been encountered:', 'skaut-google-drive-gallery' ) .
					' ' .
					$e->getMessage(),
				'error'
			);
		}
	}
}
