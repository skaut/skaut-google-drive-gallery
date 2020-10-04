<?php
/**
 * Contains the API_Client class.
 *
 * @package skaut-google-drive-gallery
 */

namespace Sgdg;

/**
 * API client
 */
class API_Client {
	/**
	 * Google API client
	 *
	 * @var \Sgdg\Vendor\Google_Client $raw_client
	 */
	private static $raw_client;

	/**
	 * Google Drive API client
	 *
	 * @var \Sgdg\Vendor\Google_Service_Drive $raw_client
	 */
	private static $drive_client;

	/**
	 * Returns a fully set-up Google client.
	 *
	 * @return \Sgdg\Vendor\Google_Client
	 */
	public static function get_raw_client() {
		if ( ! isset( self::$raw_client ) ) {
			self::$raw_client = new \Sgdg\Vendor\Google_Client();
			self::$raw_client->setAuthConfig(
				array(
					'client_id'     => \Sgdg\Options::$client_id->get(),
					'client_secret' => \Sgdg\Options::$client_secret->get(),
					'redirect_uris' => array( esc_url_raw( admin_url( 'admin.php?page=sgdg_basic&action=oauth_redirect' ) ) ),
				)
			);
			self::$raw_client->setAccessType( 'offline' );
			self::$raw_client->setApprovalPrompt( 'force' );
			self::$raw_client->addScope( \Sgdg\Vendor\Google_Service_Drive::DRIVE_READONLY );
		}
		return self::$raw_client;
	}

	/**
	 * Returns a fully set-up Google Drive API client.
	 *
	 * @throws \Exception Not authorized.
	 *
	 * @return \Sgdg\Vendor\Google_Service_Drive
	 */
	public static function get_drive_client() {
		if ( ! isset( self::$drive_client ) ) {
			$raw_client   = self::get_raw_client();
			$access_token = get_option( 'sgdg_access_token', false );
			if ( false === $access_token ) {
				throw new \Exception( esc_html__( 'Not authorized.', 'skaut-google-drive-gallery' ) );
			}
			$raw_client->setAccessToken( $access_token );

			if ( $raw_client->isAccessTokenExpired() ) {
				$raw_client->fetchAccessTokenWithRefreshToken( $raw_client->getRefreshToken() );
				$new_access_token    = $raw_client->getAccessToken();
				$merged_access_token = array_merge( $access_token, $new_access_token );
				update_option( 'sgdg_access_token', $merged_access_token );
			}
			self::$drive_client = new \Sgdg\Vendor\Google_Service_Drive( $raw_client );
		}
		return self::$drive_client;
	}
}
