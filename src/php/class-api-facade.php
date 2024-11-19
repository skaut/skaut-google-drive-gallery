<?php
/**
 * Contains the API_Facade class.
 *
 * @package skaut-google-drive-gallery
 */

namespace Sgdg;

use Sgdg\API_Client;
use Sgdg\Exceptions\Directory_Not_Found_Exception;
use Sgdg\Exceptions\Drive_Not_Found_Exception;
use Sgdg\Exceptions\File_Not_Found_Exception;
use Sgdg\Exceptions\Internal_Exception;
use Sgdg\Exceptions\Not_Found_Exception;
use Sgdg\Exceptions\Plugin_Not_Authorized_Exception;
use Sgdg\Exceptions\Unsupported_Value_Exception;
use Sgdg\Frontend\API_Fields;
use Sgdg\Frontend\Pagination_Helper;
use Sgdg\Frontend\Single_Page_Pagination_Helper;
use Sgdg\Vendor\GuzzleHttp\Promise\PromiseInterface;
use Sgdg\Vendor\GuzzleHttp\Promise\RejectedPromise;

/**
 * API call facade
 */
final class API_Facade {

	/**
	 * Searches for a directory ID by its parent and its name
	 *
	 * @param string $parent_id The ID of the directory to search in.
	 * @param string $name The name of the directory.
	 *
	 * @return PromiseInterface A promise resolving to the ID of the directory.
	 *
	 * @throws Internal_Exception The method was called without an initialized batch.
	 * @throws Plugin_Not_Authorized_Exception Not authorized.
	 */
	public static function get_directory_id( $parent_id, $name ) {
		$params = array(
			'fields'                    => 'files(id, name, mimeType, shortcutDetails(targetId))',
			'includeItemsFromAllDrives' => true,
			'pageSize'                  => 2,
			// phpcs:ignore SlevomatCodingStandard.Functions.RequireMultiLineCall.RequiredMultiLineCall
			'q'                         => '"' .
				$parent_id .
				'" in parents and name = "' .
				str_replace( '"', '\\"', $name ) .
				'" and (mimeType = "application/vnd.google-apps.folder" or ' .
				'(mimeType = "application/vnd.google-apps.shortcut" and ' .
				'shortcutDetails.targetMimeType = "application/vnd.google-apps.folder")) and trashed = false',
			'supportsAllDrives'         => true,
		);

		/**
		 * `$transform` transforms the raw Google API response into the structured response this function returns.
		 *
		 * @throws Directory_Not_Found_Exception The directory wasn't found.
		 */
		return API_Client::async_request(
			// @phan-suppress-next-line PhanTypeMismatchArgument
			API_Client::get_drive_client()->files->listFiles( $params ),
			static function ( $response ) use ( $name ) {
				if ( 1 !== count( $response->getFiles() ) ) {
					throw new Directory_Not_Found_Exception( esc_html( $name ) );
				}

				$file = $response->getFiles()[0];

				return 'application/vnd.google-apps.shortcut' === $file->getMimeType()
					? $file->getShortcutDetails()->getTargetId()
					: $file->getId();
			}
		);
	}

	/**
	 * Searches for a drive name by its ID
	 *
	 * @param string $id The of the drive.
	 *
	 * @return PromiseInterface A promise resolving to the name of the drive.
	 *
	 * @throws Internal_Exception The method was called without an initialized batch.
	 * @throws Plugin_Not_Authorized_Exception Not authorized.
	 *
	 * @SuppressWarnings("PHPMD.ShortVariable")
	 */
	public static function get_drive_name( $id ) {
		return API_Client::async_request(
			// @phan-suppress-next-line PhanTypeMismatchArgument
			API_Client::get_drive_client()->drives->get(
				$id,
				array(
					'fields' => 'name',
				)
			),
			static function ( $response ) {
				return $response->getName();
			},
			static function ( $exception ) {
				if ( $exception instanceof Not_Found_Exception ) {
					$exception = new Drive_Not_Found_Exception();
				}

				return new RejectedPromise( $exception );
			}
		);
	}

	/**
	 * Searches for a file/directory name by its ID
	 *
	 * @param string $id The ID of the file/directory.
	 *
	 * @return PromiseInterface A promise resolving to the name of the directory.
	 *
	 * @throws Internal_Exception The method was called without an initialized batch.
	 * @throws Plugin_Not_Authorized_Exception Not authorized.
	 *
	 * @SuppressWarnings("PHPMD.ShortVariable")
	 */
	public static function get_file_name( $id ) {
		return API_Client::async_request(
			// @phan-suppress-next-line PhanTypeMismatchArgument
			API_Client::get_drive_client()->files->get(
				$id,
				array(
					'fields'            => 'name, trashed',
					'supportsAllDrives' => true,
				)
			),
			/**
			 * `$transform` transforms the raw Google API response into the structured response this function returns.
			 *
			 * @throws File_Not_Found_Exception The file/directory wasn't found.
			 */
			static function ( $response ) {
				if ( $response->getTrashed() ) {
					throw new File_Not_Found_Exception();
				}

				return $response->getName();
			},
			static function ( $exception ) {
				if ( $exception instanceof Not_Found_Exception ) {
					$exception = new File_Not_Found_Exception();
				}

				return new RejectedPromise( $exception );
			}
		);
	}

	/**
	 * Checks whether an ID points to a valid directory inside another directory
	 *
	 * @param string $id The ID of the directory.
	 * @param string $parent_id The ID of the parent directory.
	 *
	 * @return PromiseInterface A promise resolving if the directory is valid.
	 *
	 * @throws Internal_Exception The method was called without an initialized batch.
	 * @throws Plugin_Not_Authorized_Exception Not authorized.
	 * @throws Unsupported_Value_Exception A field that is not supported was passed in `$fields`.
	 *
	 * @SuppressWarnings("PHPMD.ShortVariable")
	 */
	public static function check_directory_in_directory( $id, $parent_id ) {
		return self::list_directories(
			$parent_id,
			new API_Fields( array( 'id', 'trashed' ) ),
			new Single_Page_Pagination_Helper()
		)->then(
			/**
			 * `$transform` transforms the raw Google API response into the structured response this function returns.
			 *
			 * @throws Directory_Not_Found_Exception The directory wasn't found.
			 */
			static function ( $directories ) use ( $id ) {
				foreach ( $directories as $directory ) {
					if ( $directory['id'] === $id && ! boolval( $directory['trashed'] ) ) {
						return;
					}
				}

				throw new Directory_Not_Found_Exception();
			},
			static function ( $exception ) {
				if ( $exception instanceof Not_Found_Exception ) {
					$exception = new Directory_Not_Found_Exception();
				}

				return new RejectedPromise( $exception );
			}
		);
	}

	/**
	 * Lists all drives.
	 *
	 * @param Pagination_Helper $pagination_helper An initialized pagination helper. Optional.
	 *
	 * @return PromiseInterface A promise resolving to a list of drives in the format `[ 'id' => '', 'name' => '' ]`.
	 *
	 * @throws Internal_Exception The method was called without an initialized batch.
	 * @throws Plugin_Not_Authorized_Exception Not authorized.
	 */
	public static function list_drives( $pagination_helper ) {
		return API_Client::async_paginated_request(
			static function ( $page_token ) {
				return API_Client::get_drive_client()->drives->listDrives(
					array(
						'fields'    => 'nextPageToken, drives(id, name)',
						'pageSize'  => 100,
						'pageToken' => $page_token,
					)
				);
			},
			static function ( $response ) {
				return array_map(
					static function ( $drive ) {
						return array(
							'id'   => $drive->getId(),
							'name' => $drive->getName(),
						);
					},
					$response->getDrives()
				);
			},
			$pagination_helper
		);
	}

	/**
	 * Lists all directories inside a given directory.
	 *
	 * @param string            $parent_id The ID of the directory to list directories in.
	 * @param API_Fields        $fields The fields to list.
	 * @param Pagination_Helper $pagination_helper An initialized pagination helper. Optional.
	 * @param string            $order_by Sets the ordering of the results. Valid options are `createdTime`, `folder`, `modifiedByMeTime`, `modifiedTime`, `name`, `name_natural`, `quotaBytesUsed`, `recency`, `sharedWithMeTime`, `starred`, and `viewedByMeTime`. Default `name`.
	 *
	 * @return PromiseInterface A promise resolving to a list of directories in the format `[ 'id' => '', 'name' => '' ]`- the fields of each directory are givent by the parameter `$fields`.
	 *
	 * @throws Internal_Exception The method was called without an initialized batch.
	 * @throws Plugin_Not_Authorized_Exception Not authorized.
	 * @throws Unsupported_Value_Exception A field that is not supported was passed in `$fields`.
	 */
	public static function list_directories( $parent_id, $fields, $pagination_helper, $order_by = 'name' ) {
		return self::list_files(
			$parent_id,
			$fields,
			$order_by,
			$pagination_helper,
			'application/vnd.google-apps.folder'
		);
	}

	/**
	 * Lists all images inside a given directory.
	 *
	 * @param string            $parent_id The ID of the directory to list directories in.
	 * @param API_Fields        $fields The fields to list.
	 * @param Pagination_Helper $pagination_helper An initialized pagination helper. Optional.
	 * @param string            $order_by Sets the ordering of the results. Valid options are `createdTime`, `folder`, `modifiedByMeTime`, `modifiedTime`, `name`, `name_natural`, `quotaBytesUsed`, `recency`, `sharedWithMeTime`, `starred`, and `viewedByMeTime`. Default `name`.
	 *
	 * @return PromiseInterface A promise resolving to a list of images in the format `[ 'id' => '', 'name' => '' ]`- the fields of each directory are givent by the parameter `$fields`.
	 *
	 * @throws Internal_Exception The method was called without an initialized batch.
	 * @throws Plugin_Not_Authorized_Exception Not authorized.
	 * @throws Unsupported_Value_Exception A field that is not supported was passed in `$fields`.
	 */
	public static function list_images( $parent_id, $fields, $pagination_helper, $order_by = 'name' ) {
		return self::list_files( $parent_id, $fields, $order_by, $pagination_helper, 'image/' );
	}

	/**
	 * Lists all videos inside a given directory.
	 *
	 * @param string            $parent_id The ID of the directory to list directories in.
	 * @param API_Fields        $fields The fields to list.
	 * @param Pagination_Helper $pagination_helper An initialized pagination helper. Optional.
	 * @param string            $order_by Sets the ordering of the results. Valid options are `createdTime`, `folder`, `modifiedByMeTime`, `modifiedTime`, `name`, `name_natural`, `quotaBytesUsed`, `recency`, `sharedWithMeTime`, `starred`, and `viewedByMeTime`. Default `name`.
	 *
	 * @return PromiseInterface A promise resolving to a list of images in the format `[ 'id' => '', 'name' => '' ]`- the fields of each directory are givent by the parameter `$fields`.
	 *
	 * @throws Internal_Exception The method was called without an initialized batch.
	 * @throws Plugin_Not_Authorized_Exception Not authorized.
	 * @throws Unsupported_Value_Exception A field that is not supported was passed in `$fields`.
	 */
	public static function list_videos( $parent_id, $fields, $pagination_helper, $order_by = 'name' ) {
		return self::list_files( $parent_id, $fields, $order_by, $pagination_helper, 'video/' );
	}

	/**
	 * Lists all files of a given type inside a given directory.
	 *
	 * @param string            $parent_id The ID of the directory to list the files in.
	 * @param API_Fields        $fields The fields to list.
	 * @param string            $order_by Sets the ordering of the results. Valid options are `createdTime`, `folder`, `modifiedByMeTime`, `modifiedTime`, `name`, `name_natural`, `quotaBytesUsed`, `recency`, `sharedWithMeTime`, `starred`, and `viewedByMeTime`.
	 * @param Pagination_Helper $pagination_helper An initialized pagination helper.
	 * @param string            $mime_type_prefix The mimeType prefix to filter the files for.
	 *
	 * @return PromiseInterface A promise resolving to a list of files in the format `[ 'id' => '', 'name' => '' ]`- the fields of each file are given by the parameter `$fields`.
	 *
	 * @throws Internal_Exception The method was called without an initialized batch.
	 * @throws Plugin_Not_Authorized_Exception Not authorized.
	 * @throws Unsupported_Value_Exception A field that is not supported was passed in `$fields`.
	 */
	private static function list_files( $parent_id, $fields, $order_by, $pagination_helper, $mime_type_prefix ) {
		if ( ! $fields->check(
			array(
				'id',
				'name',
				'mimeType',
				'trashed',
				'size',
				'createdTime',
				'copyRequiresWriterPermission',
				'imageMediaMetadata' => array( 'width', 'height', 'time' ),
				'videoMediaMetadata' => array( 'width', 'height' ),
				'webContentLink',
				'webViewLink',
				'thumbnailLink',
				'description',
				'permissions'        => array( 'type', 'role' ),
			)
		) ) {
			// phpcs:disable WordPress.Security.EscapeOutput.ExceptionNotEscaped
			throw new Unsupported_Value_Exception( $fields, 'list_files' );
		}

		return API_Client::async_paginated_request(
			static function (
				$page_token
			) use (
				$parent_id,
				$order_by,
				$pagination_helper,
				$mime_type_prefix,
				$fields
			) {
				$mime_type_check = "(mimeType contains '" .
					$mime_type_prefix .
					"' or (mimeType contains 'application/vnd.google-apps.shortcut' and " .
					"shortcutDetails.targetMimeType contains '" .
					$mime_type_prefix .
					"'))";

				return API_Client::get_drive_client()->files->listFiles(
					array(
						'fields'                    => 'nextPageToken, files(' . $fields->format() . ')',
						'includeItemsFromAllDrives' => true,
						'orderBy'                   => $order_by,
						'pageSize'                  => $pagination_helper->next_list_size( 1000 ),
						'pageToken'                 => $page_token,
						'q'                         => "'" .
							$parent_id .
							"' in parents and " .
							$mime_type_check .
							' and trashed = false',
						'supportsAllDrives'         => true,
					)
				);
			},
			static function ( $response ) use ( $fields, $pagination_helper ) {
				$dirs = array();
				$pagination_helper->iterate(
					$response->getFiles(),
					// phpcs:ignore SlevomatCodingStandard.PHP.DisallowReference.DisallowedInheritingVariableByReference
					static function ( $file ) use ( $fields, &$dirs ) {
						$dirs[] = $fields->parse_response( $file );
					}
				);

				return $dirs;
			},
			$pagination_helper
		);
	}
}
