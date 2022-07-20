<?php
/**
 * Contains the Images class.
 *
 * @package skaut-google-drive-gallery
 */

namespace Sgdg\Frontend\Page;

/**
 * Contains all the functions used to display images in a gallery.
 */
class Images {

	/**
	 * Returns a list of images in a directory
	 *
	 * @param string                           $parent_id A directory to list items of.
	 * @param \Sgdg\Frontend\Pagination_Helper $pagination_helper An initialized pagination helper.
	 * @param \Sgdg\Frontend\Options_Proxy     $options The configuration of the gallery.
	 *
	 * @return \Sgdg\Vendor\GuzzleHttp\Promise\PromiseInterface A promise resolving to a list of images in the format `['id' =>, 'id', 'description' => 'description', 'image' => 'image', 'thumbnail' => 'thumbnail']`.
	 */
	public static function images( $parent_id, $pagination_helper, $options ) {
		if ( $options->get_by( 'image_ordering' ) === 'time' ) {
			$order_by = 'name';
			$fields   = new \Sgdg\Frontend\API_Fields(
				array(
					'id',
					'thumbnailLink',
					'createdTime',
					'imageMediaMetadata' => array( 'time' ),
					'description',
				)
			);
		} else {
			$order_by = $options->get( 'image_ordering' );
			$fields   = new \Sgdg\Frontend\API_Fields( array( 'id', 'thumbnailLink', 'description' ) );
		}
		return \Sgdg\API_Facade::list_images( $parent_id, $fields, $order_by, $pagination_helper )->then(
			static function( $images ) use ( &$options ) {
				$images = array_map(
					static function( $image ) use ( &$options ) {
						return self::image_preprocess( $image, $options );
					},
					$images
				);
				return self::images_order( $images, $options );
			}
		);
	}

	/**
	 * Processes an image response.
	 *
	 * @param array<string, mixed>         $image An image.
	 * @param \Sgdg\Frontend\Options_Proxy $options The configuration of the gallery.
	 *
	 * @return array{id: string, description: string, image: string, thumbnail: string, timestamp?: int} {
	 *     @type string      $id The ID of the image.
	 *     @type string      $description The description (caption) of the image.
	 *     @type string      $image A URL of the image to be displayed in the lightbox
	 *     @type string      $thumbnail A URL of a thumbnail to be displayed in the image grid.
	 *     @type int|null    $timestamp A timestamp to order the images by. Optional.
	 * }
	 */
	private static function image_preprocess( $image, $options ) {
		$ret = array(
			'id'          => $image['id'],
			'description' => array_key_exists( 'description', $image ) ? esc_attr( $image['description'] ) : '',
			'image'       => substr( $image['thumbnailLink'], 0, -3 ) . $options->get( 'preview_size' ),
			'thumbnail'   => substr( $image['thumbnailLink'], 0, -4 ) . 'h' . floor( 1.25 * $options->get( 'grid_height' ) ),
		);
		if ( $options->get_by( 'image_ordering' ) === 'time' ) {
			if ( array_key_exists( 'imageMediaMetadata', $image ) && array_key_exists( 'time', $image['imageMediaMetadata'] ) ) {
				$timestamp = \DateTime::createFromFormat( 'Y:m:d H:i:s', $image['imageMediaMetadata']['time'] );
			} else {
				$timestamp = \DateTime::createFromFormat( 'Y-m-d\TH:i:s.uP', $image['createdTime'] );
			}
			if ( false !== $timestamp ) {
				$ret['timestamp'] = intval( $timestamp->format( 'U' ) );
			}
		}
		return $ret;
	}

	/**
	 * Orders images.
	 *
	 * @param array<array{id: string, description: string, image: string, thumbnail: string, timestamp?: int}> $images A list of images in the format `['id' =>, 'id', 'description' => 'description', 'image' => 'image', 'thumbnail' => 'thumbnail', 'timestamp' => 1638012797]`.
	 * @param \Sgdg\Frontend\Options_Proxy                                                                     $options The configuration of the gallery.
	 *
	 * @return array<array{id: string, description: string, image: string, thumbnail: string}> An ordered list of images in the format `['id' =>, 'id', 'description' => 'description', 'image' => 'image', 'thumbnail' => 'thumbnail']`.
	 */
	private static function images_order( $images, $options ) {
		if ( $options->get_by( 'image_ordering' ) === 'time' ) {
			usort(
				$images,
				static function( $first, $second ) use ( $options ) {
					$first_timestamp  = array_key_exists( 'timestamp', $first ) ? $first['timestamp'] : time();
					$second_timestamp = array_key_exists( 'timestamp', $second ) ? $second['timestamp'] : time();
					$asc              = $first_timestamp - $second_timestamp;
					return $options->get_order( 'image_ordering' ) === 'ascending' ? $asc : -$asc;
				}
			);
			array_walk(
				$images,
				static function( &$item ) {
					unset( $item['timestamp'] );
				}
			);
		}
		return $images;
	}

}
