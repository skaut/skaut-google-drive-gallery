<?php
/**
 * Contains the Images class.
 *
 * @package skaut-google-drive-gallery
 */

namespace Sgdg\Frontend\Page;

use ArrayObject;
use DateTime;
use Sgdg\API_Facade;
use Sgdg\Exceptions\Internal_Exception;
use Sgdg\Exceptions\Plugin_Not_Authorized_Exception;
use Sgdg\Exceptions\Unsupported_Value_Exception;
use Sgdg\Frontend\API_Fields;
use Sgdg\Frontend\Download;
use Sgdg\Frontend\Options_Proxy;
use Sgdg\Frontend\Pagination_Helper;
use Sgdg\Frontend\Paging_Pagination_Helper;
use Sgdg\Vendor\GuzzleHttp\Promise\PromiseInterface;
use const PHP_INT_MAX;

/**
 * Contains all the functions used to display images in a gallery.
 */
final class Images {

	/**
	 * Returns a list of images in a directory
	 *
	 * @param string            $parent_id A directory to list items of.
	 * @param Pagination_Helper $pagination_helper An initialized pagination helper.
	 * @param Options_Proxy     $options The configuration of the gallery.
	 *
	 * @return PromiseInterface A promise resolving to a list of images in the format `['id' =>, 'id', 'description' => 'description', 'image' => 'image', 'thumbnail' => 'thumbnail']`.
	 *
	 * @throws Internal_Exception The method was called without an initialized batch.
	 * @throws Plugin_Not_Authorized_Exception Not authorized.
	 * @throws Unsupported_Value_Exception A field that is not supported was passed in `$fields`.
	 */
	public static function get( $parent_id, $pagination_helper, $options ) {
		$by_time        = 'time' === $options->get_by( 'image_ordering' );
		$allow_download = 'true' === $options->get( 'allow_download' );
		$fields         = array( 'id', 'thumbnailLink', 'description' );

		if ( $allow_download ) {
			$fields = array_merge( $fields, array( 'name', 'mimeType', 'size' ) );
		}

		if ( $by_time ) {
			$order_by                     = 'name';
			$fields[]                     = 'createdTime';
			$fields['imageMediaMetadata'] = array( 'time' );
		} else {
			$order_by = $options->get( 'image_ordering' );
		}

		$fields = new API_Fields( $fields );

		// Drive can't order the images by the time they were taken, so all of them are listed and ordered here, and only then is the page picked. Otherwise, each page would only be ordered on its own.
		$list_helper = $by_time
			? ( new Paging_Pagination_Helper() )->withValues( 0, PHP_INT_MAX )
			: $pagination_helper;

		return API_Facade::list_images( $parent_id, $fields, $list_helper, $order_by )->then(
			static function ( $image_response ) use ( $options, $by_time, $pagination_helper, $allow_download ) {
				$images = array_map(
					static function ( $image ) use ( $options, $allow_download ) {
						return self::format_image( $image, $options, $allow_download );
					},
					$image_response
				);

				$image_timestamps = array_map(
					static function ( $image ) use ( $options ) {
						return self::extract_timestamp( $image, $options );
					},
					$image_response
				);

				$page = self::order( $images, $image_timestamps, $options );

				if ( $by_time ) {
					$paged = new ArrayObject();
					$pagination_helper->iterate(
						$page,
						static function ( $image ) use ( $paged ) {
							$paged->append( $image );
						}
					);
					$page = $paged->getArrayCopy();
				}

				return array_map(
					static function ( $image ) {
						return self::add_download_url( $image );
					},
					$page
				);
			}
		);
	}

	/**
	 * Converts an image returned by the API into the format of the response.
	 *
	 * @param array<string, mixed> $image The image as returned by the API.
	 * @param Options_Proxy        $options The configuration of the gallery.
	 * @param bool                 $allow_download Whether downloading is allowed.
	 *
	 * @return array<string, mixed> The image.
	 */
	private static function format_image( $image, $options, $allow_download ) {
			$ret = array(
				'description' => array_key_exists( 'description', $image )
					? esc_attr( $image['description'] )
					: '',
				'id'          => $image['id'],
				'image'       => substr( $image['thumbnailLink'], 0, -3 ) . $options->get( 'preview_size' ),
				'thumbnail'   => substr( $image['thumbnailLink'], 0, -4 ) .
					'h' .
					floor( 1.25 * $options->get( 'grid_height' ) ),
			);

			if ( $allow_download ) {
				// The download URL is only created for the images on the returned page, see below.
				$ret['download'] = array(
					$image['id'],
					isset( $image['name'] ) ? strval( $image['name'] ) : '',
					isset( $image['mimeType'] ) ? strval( $image['mimeType'] ) : 'application/octet-stream',
					isset( $image['size'] ) ? intval( $image['size'] ) : 0,
				);
			}

			return $ret;
	}

	/**
	 * Replaces the download information of an image with its download URL.
	 *
	 * @param array<string, mixed> $image The image.
	 *
	 * @return array<string, mixed> The image with the download URL, if downloading is allowed.
	 */
	private static function add_download_url( $image ) {
		if ( isset( $image['download'] ) && is_array( $image['download'] ) ) {
			list( $file_id, $name, $mime_type, $size ) = $image['download'];
			$image['download']                         = Download::url( $file_id, $name, $mime_type, $size );
		}

		return $image;
	}

	/**
	 * Extracts a timestamp from an image
	 *
	 * @param array<string, mixed> $image An image.
	 * @param Options_Proxy        $options The configuration of the gallery.
	 *
	 * @return int The timestamp.
	 */
	private static function extract_timestamp( $image, $options ) {
		if ( 'time' !== $options->get_by( 'image_ordering' ) ) {
			return time();
		}

		$timestamp = false;

		// The time the image was taken (from EXIF). Drive returns it as null when the image doesn't have it.
		if ( isset( $image['imageMediaMetadata']['time'] ) && is_string( $image['imageMediaMetadata']['time'] ) ) {
			$timestamp = DateTime::createFromFormat( 'Y:m:d H:i:s', $image['imageMediaMetadata']['time'] );
		}

		// Otherwise the time the file was created in Drive.
		if ( false === $timestamp && isset( $image['createdTime'] ) && is_string( $image['createdTime'] ) ) {
			$timestamp = DateTime::createFromFormat( 'Y-m-d\TH:i:s.uP', $image['createdTime'] );
		}

		return false !== $timestamp ? intval( $timestamp->format( 'U' ) ) : time();
	}

	/**
	 * Orders images.
	 *
	 * @param array<array{id: string, description: string, image: string, thumbnail: string, download?: array{0: string, 1: string, 2: string, 3: int}, timestamp?: int}> $images A list of images in the format `['id' =>, 'id', 'description' => 'description', 'image' => 'image', 'thumbnail' => 'thumbnail']`.
	 * @param array<int>                                                                                                                                                  $image_timestamps The timestamps for each image.
	 * @param Options_Proxy                                                                                                                                               $options The configuration of the gallery.
	 *
	 * @return array<array{id: string, description: string, image: string, thumbnail: string, download?: array{0: string, 1: string, 2: string, 3: int}}> An ordered list of images in the format `['id' =>, 'id', 'description' => 'description', 'image' => 'image', 'thumbnail' => 'thumbnail']`.
	 */
	private static function order( $images, $image_timestamps, $options ) {
		if ( 'time' === $options->get_by( 'image_ordering' ) ) {
			uksort(
				$images,
				static function ( $first_index, $second_index ) use ( $image_timestamps, $options ) {
					$asc = $image_timestamps[ $first_index ] - $image_timestamps[ $second_index ];

					return 'ascending' === $options->get_order( 'image_ordering' ) ? $asc : -$asc;
				}
			);
		}

		return array_values( $images );
	}
}
