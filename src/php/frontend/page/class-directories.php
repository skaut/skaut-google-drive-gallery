<?php
/**
 * Contains the Directories class.
 *
 * @package skaut-google-drive-gallery
 */

namespace Sgdg\Frontend\Page;

use Sgdg\API_Facade;
use Sgdg\Exceptions\Internal_Exception;
use Sgdg\Exceptions\Plugin_Not_Authorized_Exception;
use Sgdg\Exceptions\Unsupported_Value_Exception;
use Sgdg\Frontend\API_Fields;
use Sgdg\Frontend\Options_Proxy;
use Sgdg\Frontend\Pagination_Helper;
use Sgdg\Frontend\Paging_Pagination_Helper;
use Sgdg\Frontend\Single_Page_Pagination_Helper;
use Sgdg\Vendor\GuzzleHttp\Promise\PromiseInterface;
use Sgdg\Vendor\GuzzleHttp\Promise\Utils;

/**
 * Contains all the functions used to display directories in a gallery.
 */
final class Directories {

	/**
	 * Returns a list of subdirectories in a directory.
	 *
	 * @param string            $parent_id A directory to list items of.
	 * @param Pagination_Helper $pagination_helper An initialized pagination helper.
	 * @param Options_Proxy     $options The configuration of the gallery.
	 *
	 * @return PromiseInterface A promise resolving to a list of directories in the format `['id' =>, 'id', 'name' => 'name', 'thumbnail' => 'thumbnail', 'dircount' => 1, 'imagecount' => 1, 'videocount' => 1]`.
	 *
	 * @throws Internal_Exception The method was called without an initialized batch.
	 * @throws Plugin_Not_Authorized_Exception Not authorized.
	 * @throws Unsupported_Value_Exception A field that is not supported was passed in `$fields`.
	 */
	public static function get( $parent_id, $pagination_helper, $options ) {
		return API_Facade::list_directories(
			$parent_id,
			new API_Fields( array( 'id', 'name' ) ),
			$pagination_helper,
			$options->get( 'dir_ordering' )
		)->then(
			static function ( $files ) use ( $options ) {
				$files = array_map(
					static function ( $file ) use ( $options ) {
						if ( '' !== $options->get( 'dir_prefix' ) ) {
							$pos          = mb_strpos( $file['name'], $options->get( 'dir_prefix' ) );
							$file['name'] = mb_substr( $file['name'], false !== $pos ? $pos + 1 : 0 );
						}

						return $file;
					},
					$files
				);
				$ids   = array_column( $files, 'id' );

				return Utils::all(
					array( $files, self::thumbnail_images( $ids, $options ), self::item_counts( $ids ) )
				);
			}
		)->then(
			static function ( $tuple ) use ( $options ) {
				list( $files, $images, $counts ) = $tuple;
				$count                           = count( $files );

				for ( $i = 0; $i < $count; ++$i ) {
					$files[ $i ]['thumbnail'] = $images[ $i ];

					if ( 'true' === $options->get( 'dir_counts' ) ) {
						$files[ $i ] = array_merge( $files[ $i ], $counts[ $i ] );
					}

					if ( 0 === $counts[ $i ]['dircount'] + $counts[ $i ]['imagecount'] + $counts[ $i ]['videocount'] ) {
						unset( $files[ $i ] );
					}
				}

				// Needed because of the unset not re-indexing.
				return array_values( $files );
			}
		);
	}

	/**
	 * Creates API requests for directory thumbnails
	 *
	 * Takes a batch and adds to it a request for the first image in each directory.
	 *
	 * @param array<string> $dirs A list of directory IDs.
	 * @param Options_Proxy $options The configuration of the gallery.
	 *
	 * @return PromiseInterface A promise resolving to a list of directory images.
	 *
	 * @throws Internal_Exception The method was called without an initialized batch.
	 * @throws Plugin_Not_Authorized_Exception Not authorized.
	 * @throws Unsupported_Value_Exception A field that is not supported was passed in `$fields`.
	 */
	private static function thumbnail_images( $dirs, $options ) {
		return Utils::all(
			array_map(
				static function ( $directory ) use ( $options ) {
					return API_Facade::list_images(
						$directory,
						new API_Fields(
							array(
								'imageMediaMetadata' => array( 'width', 'height' ),
								'thumbnailLink',
							)
						),
						( new Paging_Pagination_Helper() )->withValues( 0, 1 ),
						$options->get( 'image_ordering' )
					)->then(
						static function ( $images ) use ( $options ) {
							if ( 0 === count( $images ) ) {
								return false;
							}

							$image_metadata = $images[0]['imageMediaMetadata'];
							$dimension      = $image_metadata['width'] > $image_metadata['height'] ? 'h' : 'w';

							return substr( $images[0]['thumbnailLink'], 0, -4 ) .
								$dimension .
								floor( 1.25 * $options->get( 'grid_height' ) );
						}
					);
				},
				$dirs
			)
		);
	}

	/**
	 * Creates API requests for directory item counts
	 *
	 * Takes a batch and adds to it requests for the counts of subdirectories and images for each directory.
	 *
	 * @param array<string> $dirs A list of directory IDs.
	 *
	 * @return PromiseInterface A promise resolving to a list of subdirectory, image and video counts of format `['dircount' => 1, 'imagecount' => 1, 'videocount' => 1]` for each directory.
	 *
	 * @throws Internal_Exception The method was called without an initialized batch.
	 * @throws Plugin_Not_Authorized_Exception Not authorized.
	 * @throws Unsupported_Value_Exception A field that is not supported was passed in `$fields`.
	 */
	private static function item_counts( $dirs ) {
		return Utils::all(
			array_map(
				static function ( $dir ) {
					return Utils::all(
						array(
							API_Facade::list_directories(
								$dir,
								new API_Fields( array( 'createdTime' ) ),
								new Single_Page_Pagination_Helper(),
								'name'
							),
							API_Facade::list_images(
								$dir,
								new API_Fields( array( 'createdTime' ) ),
								new Single_Page_Pagination_Helper(),
								'name'
							),
							API_Facade::list_videos(
								$dir,
								new API_Fields( array( 'createdTime' ) ),
								new Single_Page_Pagination_Helper(),
								'name'
							),
						)
					)->then(
						static function ( $items ) {
							return array(
								'dircount'   => count( $items[0] ),
								'imagecount' => count( $items[1] ),
								'videocount' => count( $items[2] ),
							);
						}
					);
				},
				$dirs
			)
		);
	}
}
