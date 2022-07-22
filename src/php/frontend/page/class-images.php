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
final class Images {

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
		if ( 'time' === $options->get_by( 'image_ordering' ) ) {
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

		return \Sgdg\API_Facade::list_images( $parent_id, $fields, $pagination_helper, $order_by )->then(
			static function( $images ) use ( &$options ) {
				$images = array_map(
					static function( $image ) use ( &$options ) {
						return array(
							'id'          => $image['id'],
							'description' => array_key_exists( 'description', $image )
								? esc_attr( $image['description'] )
								: '',
							'image'       => substr( $image['thumbnailLink'], 0, -3 ) . $options->get( 'preview_size' ),
							'thumbnail'   => substr( $image['thumbnailLink'], 0, -4 ) .
								'h' .
								floor( 1.25 * $options->get( 'grid_height' ) ),
						);
					},
					$images
				);

				$image_timestamps = array_map(
					static function( $image ) use ( &$options ) {
						return self::image_extract_timestamp( $image, $options );
					},
					$images
				);

				return self::images_order( $images, $image_timestamps, $options );
			}
		);
	}

	/**
	 * Extracts a timestamp from an image
	 *
	 * @param array<string, mixed>         $image An image.
	 * @param \Sgdg\Frontend\Options_Proxy $options The configuration of the gallery.
	 *
	 * @return int The timestamp.
	 */
	private static function image_extract_timestamp( $image, $options ) {
		if ( 'time' !== $options->get_by( 'image_ordering' ) ) {
			return time();
		}

		$timestamp = array_key_exists( 'imageMediaMetadata', $image ) &&
			array_key_exists( 'time', $image['imageMediaMetadata'] )
			? \DateTime::createFromFormat( 'Y:m:d H:i:s', $image['imageMediaMetadata']['time'] )
			: \DateTime::createFromFormat( 'Y-m-d\TH:i:s.uP', $image['createdTime'] );

		return false !== $timestamp ? intval( $timestamp->format( 'U' ) ) : time();
	}

	/**
	 * Orders images.
	 *
	 * @param array<array{id: string, description: string, image: string, thumbnail: string, timestamp?: int}> $images A list of images in the format `['id' =>, 'id', 'description' => 'description', 'image' => 'image', 'thumbnail' => 'thumbnail']`.
	 * @param array<int>                                                                                       $image_timestamps The timestamps for each image.
	 * @param \Sgdg\Frontend\Options_Proxy                                                                     $options The configuration of the gallery.
	 *
	 * @return array<array{id: string, description: string, image: string, thumbnail: string}> An ordered list of images in the format `['id' =>, 'id', 'description' => 'description', 'image' => 'image', 'thumbnail' => 'thumbnail']`.
	 */
	private static function images_order( $images, $image_timestamps, $options ) {
		if ( 'time' === $options->get_by( 'image_ordering' ) ) {
			uksort(
				$images,
				static function( $first_index, $second_index ) use ( $image_timestamps, $options ) {
					$asc = $image_timestamps[ $first_index ] - $image_timestamps[ $second_index ];

					return 'ascending' === $options->get_order( 'image_ordering' ) ? $asc : -$asc;
				}
			);
		}

		return $images;
	}

}
