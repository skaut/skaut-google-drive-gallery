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
	 * Searches for a drive name by its ID
	 *
	 * @param string $id The of the drive.
	 *
	 * @throws \Sgdg\Exceptions\API_Exception|\Sgdg\Exceptions\API_Rate_Limit_Exception A problem with the API.
	 *
	 * @return string The name of the drive.
	 *
	 * @SuppressWarnings(PHPMD.ShortVariable)
	 */
	public static function get_drive_name( $id ) {
		try {
			$response = self::get_drive_client()->drives->get(
				$id,
				array(
					'fields' => 'name',
				)
			);
		} catch ( \Sgdg\Vendor\Google_Service_Exception $e ) {
			throw self::wrap_exception( $e );
		}
		self::check_response( $response );
		return $response->getName();
	}

	/**
	 * Searches for a file/directory name by its ID
	 *
	 * @param string $id The of the file/directory.
	 *
	 * @throws \Sgdg\Exceptions\File_Not_Found_Exception The file/directory wasn't found.
	 * @throws \Sgdg\Exceptions\API_Exception|\Sgdg\Exceptions\API_Rate_Limit_Exception A problem with the API.
	 *
	 * @return string The name of the directory.
	 *
	 * @SuppressWarnings(PHPMD.ShortVariable)
	 */
	public static function get_file_name( $id ) {
		try {
			$response = self::get_drive_client()->files->get(
				$id,
				array(
					'supportsAllDrives' => true,
					'fields'            => 'name, trashed',
				)
			);
		} catch ( \Sgdg\Vendor\Google_Service_Exception $e ) {
			if ( in_array( 'notFound', array_column( $e->getErrors(), 'reason' ), true ) ) {
				throw new \Sgdg\Exceptions\File_Not_Found_Exception();
			}
			throw self::wrap_exception( $e );
		}
		self::check_response( $response );
		if ( $response->getTrashed() ) {
			throw new \Sgdg\Exceptions\File_Not_Found_Exception();
		}
		return $response->getName();
	}

	/**
	 * Lists all drives.
	 *
	 * @throws \Sgdg\Exceptions\API_Exception|\Sgdg\Exceptions\API_Rate_Limit_Exception A problem with the API.
	 *
	 * @return array A list of drives in the format `[ 'id' => '', 'name' => '' ]`.
	 */
	public static function list_drives() {
		$ret        = array();
		$page_token = null;
		do {
			$params = array(
				'pageToken' => $page_token,
				'pageSize'  => 100,
				'fields'    => 'nextPageToken, drives(id, name)',
			);
			try {
				$response = self::get_drive_client()->drives->listDrives( $params );
			} catch ( \Sgdg\Vendor\Google_Service_Exception $e ) {
				throw self::wrap_exception( $e );
			}
			self::check_response( $response );
			// @phan-suppress-next-line PhanTypeSuspiciousNonTraversableForeach
			foreach ( $response->getDrives() as $drive ) {
				$ret[] = array(
					'name' => $drive->getName(),
					'id'   => $drive->getId(),
				);
			}
			$page_token = $response->getNextPageToken();
		} while ( null !== $page_token );
		return $ret;
	}

	/**
	 * Lists all files of a given type inside a given directory.
	 *
	 * @param string $parent_id The ID of the directory to list the files in.
	 * @param array  $fields The fields to list.
	 * @param string $mime_type_prefix The mimeType prefix to filter the files for.
	 *
	 * @throws \Sgdg\Exceptions\Unsupported_Value_Exception                            A field that is not supported was passed in `$fields`.
	 * @throws \Sgdg\Exceptions\API_Exception|\Sgdg\Exceptions\API_Rate_Limit_Exception A problem with the API.
	 *
	 * @return array A list of files in the format `[ 'id' => '', 'name' => '' ]`- the fields of each directory are givent by the parameter `$fields`.
	 */
	private static function list_files( $parent_id, $fields, $mime_type_prefix ) {
		$unsupported_fields = array_diff( $fields, array( 'id', 'name' ) );
		if ( ! empty( $unsupported_fields ) ) {
			throw new \Sgdg\Exceptions\Unsupported_Value_Exception( $unsupported_fields, 'list_directories' );
		}
		$query_fields = $fields;
		if ( in_array( 'id', $fields, true ) ) {
			$query_fields[] = 'mimeType';
			$query_fields[] = 'shortcutDetails(targetId)';
		}
		$ret        = array();
		$page_token = null;
		do {
			$params = array(
				'q'                         => '"' . $parent_id . '" in parents and (mimeType contains "' . $mime_type_prefix . '" or (mimeType contains "application/vnd.google-apps.shortcut" and shortcutDetails.targetMimeType contains "' . $mime_type_prefix . '")) and trashed = false',
				'supportsAllDrives'         => true,
				'includeItemsFromAllDrives' => true,
				'pageToken'                 => $page_token,
				'pageSize'                  => 1000,
				'fields'                    => 'nextPageToken, files(' . implode( ', ', $query_fields ) . ')',
			);
			try {
				$response = self::get_drive_client()->files->listFiles( $params );
			} catch ( \Sgdg\Vendor\Google_Service_Exception $e ) {
				throw self::wrap_exception( $e );
			}
			self::check_response( $response );
			foreach ( $response->getFiles() as $file ) {
				$dir = array();
				foreach ( $fields as $field ) {
					switch ( $field ) {
						case 'id':
							$dir['id'] = $file->getMimeType() === 'application/vnd.google-apps.shortcut' ? $file->getShortcutDetails()->getTargetId() : $file->getId();
							break;
						default:
							$dir[ $field ] = $file->$field;
					}
				}
				$ret[] = $dir;
			}
			$page_token = $response->getNextPageToken();
		} while ( null !== $page_token );
		return $ret;
	}

	/**
	 * Lists all directories inside a given directory.
	 *
	 * @param string $parent_id The ID of the directory to list directories in.
	 * @param array  $fields The fields to list.
	 *
	 * @throws \Sgdg\Exceptions\Unsupported_Value_Exception                            A field that is not supported was passed in `$fields`.
	 * @throws \Sgdg\Exceptions\API_Exception|\Sgdg\Exceptions\API_Rate_Limit_Exception A problem with the API.
	 *
	 * @return array A list of directories in the format `[ 'id' => '', 'name' => '' ]`- the fields of each directory are givent by the parameter `$fields`.
	 */
	public static function list_directories( $parent_id, $fields ) {
		return self::list_files( $parent_id, $fields, 'application/vnd.google-apps.folder' );
	}
}
