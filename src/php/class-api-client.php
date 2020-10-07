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

	/**
	 * Checks the API response and throws an exception if there was a problem.
	 *
	 * @param \ArrayAccess|\Countable|\Iterator|\Sgdg\Vendor\Google_Collection|\Sgdg\Vendor\Google_Model|\Sgdg\Vendor\Google_Service_Drive_FileList|\Traversable|iterable $response The API response.
	 *
	 * @throws \Sgdg\Exceptions\API_Exception|\Sgdg\Exceptions\API_Rate_Limit_Exception A wrapped API Exception.
	 */
	private static function check_response( $response ) {
		if ( ! ( $response instanceof \Sgdg\Vendor\Google_Service_Exception ) ) {
			return;
		}
		throw self::wrap_exception( $response );
	}

	/**
	 * Checks the API response and throws an exception if there was a problem.
	 *
	 * @param \Sgdg\Vendor\Google_Service_Exception $api_exception The API exception.
	 *
	 * @return \Sgdg\Exceptions\API_Rate_Limit_Exception|\Sgdg\Exceptions\API_Exception The wrapped API exception.
	 */
	private static function wrap_exception( $api_exception ) {
		if ( in_array( 'userRateLimitExceeded', array_column( $api_exception->getErrors(), 'reason' ), true ) ) {
			return new \Sgdg\Exceptions\API_Rate_Limit_Exception( $api_exception );
		}
		return new \Sgdg\Exceptions\API_Exception( $api_exception );
	}

	/**
	 * Searches for a directory ID by its parent and its name
	 *
	 * @param string $parent_id The ID of the directory to search in.
	 * @param string $name The name of the directory.
	 *
	 * @throws \Sgdg\Exceptions\API_Exception|\Sgdg\Exceptions\API_Rate_Limit_Exception A problem with the API.
	 * @throws \Sgdg\Exceptions\Directory_Not_Found_Exception The directory wasn't found.
	 *
	 * @return string The ID of the directory.
	 */
	public static function get_directory_id( $parent_id, $name ) {
		$params = array(
			'q'                         => '"' . $parent_id . '" in parents and name = "' . str_replace( '"', '\\"', $name ) . '" and (mimeType = "application/vnd.google-apps.folder" or (mimeType = "application/vnd.google-apps.shortcut" and shortcutDetails.targetMimeType = "application/vnd.google-apps.folder")) and trashed = false',
			'supportsAllDrives'         => true,
			'includeItemsFromAllDrives' => true,
			'fields'                    => 'files(id, name, mimeType, shortcutDetails(targetId))',
		);
		try {
			$response = self::get_drive_client()->files->listFiles( $params );
		} catch ( \Sgdg\Vendor\Google_Service_Exception $e ) {
			throw self::wrap_exception( $e );
		}
		self::check_response( $response );
		if ( 1 !== count( $response->getFiles() ) ) {
			throw new \Sgdg\Exceptions\Directory_Not_Found_Exception( $name );
		}
		$file = $response->getFiles()[0];
		return $file->getMimeType() === 'application/vnd.google-apps.shortcut' ? $file->getShortcutDetails()->getTargetId() : $file->getId();
	}

	/**
	 * Lists all directories inside a given directory.
	 *
	 * @param string $parent_id The ID of the directory to list directories in.
	 *
	 * @throws \Sgdg\Exceptions\API_Exception|\Sgdg\Exceptions\API_Rate_Limit_Exception A problem with the API.
	 *
	 * @return array A list of directories in the format `[ 'name' => '' ]`
	 */
	public static function list_directories( $parent_id ) {
		$ret        = array();
		$page_token = null;
		do {
			$params = array(
				'q'                         => '"' . $parent_id . '" in parents and (mimeType = "application/vnd.google-apps.folder" or (mimeType = "application/vnd.google-apps.shortcut" and shortcutDetails.targetMimeType = "application/vnd.google-apps.folder")) and trashed = false',
				'supportsAllDrives'         => true,
				'includeItemsFromAllDrives' => true,
				'pageToken'                 => $page_token,
				'pageSize'                  => 1000,
				'fields'                    => 'nextPageToken, files(name)',
			);
			try {
				$response = self::get_drive_client()->files->listFiles( $params );
			} catch ( \Sgdg\Vendor\Google_Service_Exception $e ) {
				throw self::wrap_exception( $e );
			}
			self::check_response( $response );
			foreach ( $response->getFiles() as $file ) {
				$ret[] = array( 'name' => $file->getName() );
			}
			$page_token = $response->getNextPageToken();
		} while ( null !== $page_token );
		return $ret;
	}
}
