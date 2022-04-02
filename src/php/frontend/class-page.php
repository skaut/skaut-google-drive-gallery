<?php
/**
 * Contains the Page class.
 *
 * @package skaut-google-drive-gallery
 */

namespace Sgdg\Frontend;

/**
 * Contains all the functions used to handle the "page" AJAX endpoint.
 *
 * The "page" AJAX enpoint gets called each time the user needs to fetch items for a gallery.
 *
 * @phan-constructor-used-for-side-effects
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Page {
	/**
	 * Registers the "page" AJAX endpoint
	 */
	public function __construct() {
		add_action( 'wp_ajax_page', array( self::class, 'handle_ajax' ) );
		add_action( 'wp_ajax_nopriv_page', array( self::class, 'handle_ajax' ) );
	}

	/**
	 * Handles errors for the "page" AJAX endpoint.
	 *
	 * This function is a wrapper around `handle_ajax_body` that handles all the possible errors that can occur and sends them back as error messages.
	 *
	 * @return void
	 */
	public static function handle_ajax() {
		\Sgdg\Helpers::ajax_wrapper( array( self::class, 'ajax_handler_body' ) );
	}

	/**
	 * Actually handles the "gallery" AJAX endpoint.
	 *
	 * Returns a list of directories and a list of images.
	 *
	 * @return void
	 *
	 * @see get_page()
	 */
	public static function ajax_handler_body() {
		list( $parent_id, $options, $path_verification ) = \Sgdg\Frontend\Gallery_Context::get();
		$pagination_helper                               = ( new \Sgdg\Frontend\Pagination_Helper() )->withOptions( $options, false );

		$page_promise = self::get_page( $parent_id, $pagination_helper, $options )->then(
			static function( $page ) {
				wp_send_json( $page );
			}
		);
		\Sgdg\API_Client::execute( array( $path_verification, $page_promise ) );
	}

	/**
	 * Return one page worth of items
	 *
	 * Lists one page of items - first directories and then images, up until the number of items per page is reached.
	 *
	 * @param string                           $parent_id A directory to list items of.
	 * @param \Sgdg\Frontend\Pagination_Helper $pagination_helper An initialized pagination helper.
	 * @param \Sgdg\Frontend\Options_Proxy     $options The configuration of the gallery.
	 *
	 * @return \Sgdg\Vendor\GuzzleHttp\Promise\PromiseInterface A promise resolving to the page return value.
	 */
	public static function get_page( $parent_id, $pagination_helper, $options ) {
		$page = array(
			'directories' => self::directories( $parent_id, $pagination_helper, $options ),
		);
		return \Sgdg\Vendor\GuzzleHttp\Promise\Utils::all( $page )->then(
			static function( $page ) use ( $parent_id, $pagination_helper, $options ) {
				if ( $pagination_helper->should_continue() ) {
					$page['images'] = self::images( $parent_id, $pagination_helper, $options );
				}
				return \Sgdg\Vendor\GuzzleHttp\Promise\Utils::all( $page );
			}
		)->then(
			static function( $page ) use ( $parent_id, $pagination_helper, $options ) {
				if ( $pagination_helper->should_continue() ) {
					$page['videos'] = self::videos( $parent_id, $pagination_helper, $options );
				}
				return \Sgdg\Vendor\GuzzleHttp\Promise\Utils::all( $page );
			}
		)->then(
			static function( $page ) use ( $pagination_helper ) {
				$page['more'] = $pagination_helper->has_more();
				return $page;
			}
		);
	}

	/**
	 * Returns a list of subdirectories in a directory.
	 *
	 * @param string                           $parent_id A directory to list items of.
	 * @param \Sgdg\Frontend\Pagination_Helper $pagination_helper An initialized pagination helper.
	 * @param \Sgdg\Frontend\Options_Proxy     $options The configuration of the gallery.
	 *
	 * @return \Sgdg\Vendor\GuzzleHttp\Promise\PromiseInterface A promise resolving to a list of directories in the format `['id' =>, 'id', 'name' => 'name', 'thumbnail' => 'thumbnail', 'dircount' => 1, 'imagecount' => 1]`.
	 */
	private static function directories( $parent_id, $pagination_helper, $options ) {
		return ( \Sgdg\API_Facade::list_directories( $parent_id, new \Sgdg\Frontend\API_Fields( array( 'id', 'name' ) ), $options->get( 'dir_ordering' ), $pagination_helper )->then(
			static function( $files ) use ( &$options ) {
				$files = array_map(
					static function( $file ) use ( &$options ) {
						if ( '' !== $options->get( 'dir_prefix' ) ) {
							$pos          = mb_strpos( $file['name'], $options->get( 'dir_prefix' ) );
							$file['name'] = mb_substr( $file['name'], false !== $pos ? $pos + 1 : 0 );
						}
						return $file;
					},
					$files
				);
				$ids   = array_column( $files, 'id' );

				return \Sgdg\Vendor\GuzzleHttp\Promise\Utils::all( array( $files, self::dir_images( $ids, $options ), self::dir_counts( $ids ) ) );
			}
		)->then(
			static function( $list ) use ( &$options ) {
				list( $files, $images, $counts ) = $list;
				$count                           = count( $files );
				for ( $i = 0; $i < $count; $i++ ) {
					$files[ $i ]['thumbnail'] = $images[ $i ];
					if ( 'true' === $options->get( 'dir_counts' ) ) {
						$files[ $i ] = array_merge( $files[ $i ], $counts[ $i ] );
					}
					if ( 0 === $counts[ $i ]['dircount'] + $counts[ $i ]['imagecount'] + $counts[ $i ]['videocount'] ) {
						unset( $files[ $i ] );
					}
				}
				return array_values( $files ); // Needed because of the unset not re-indexing.
			}
		) );
	}

	/**
	 * Creates API requests for directory thumbnails
	 *
	 * Takes a batch and adds to it a request for the first image in each directory.
	 *
	 * @param array<string>                $dirs A list of directory IDs.
	 * @param \Sgdg\Frontend\Options_Proxy $options The configuration of the gallery.
	 *
	 * @return \Sgdg\Vendor\GuzzleHttp\Promise\PromiseInterface A promise resolving to a list of directory images
	 */
	private static function dir_images( $dirs, $options ) {
		return \Sgdg\Vendor\GuzzleHttp\Promise\Utils::all(
			array_map(
				static function( $directory ) use ( &$options ) {
					return \Sgdg\API_Facade::list_images(
						$directory,
						new \Sgdg\Frontend\API_Fields(
							array(
								'imageMediaMetadata' => array( 'width', 'height' ),
								'thumbnailLink',
							)
						),
						$options->get( 'image_ordering' ),
						( new \Sgdg\Frontend\Pagination_Helper() )->withValues( 0, 1 )
					)->then(
						static function( $images ) use ( &$options ) {
							if ( count( $images ) === 0 ) {
								return false;
							}
							return substr( $images[0]['thumbnailLink'], 0, -4 ) . ( $images[0]['imageMediaMetadata']['width'] > $images[0]['imageMediaMetadata']['height'] ? 'h' : 'w' ) . floor( 1.25 * $options->get( 'grid_height' ) );
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
	 * @return \Sgdg\Vendor\GuzzleHttp\Promise\PromiseInterface A promise resolving to a list of subdirectory, image and video counts of format `['dircount' => 1, 'imagecount' => 1, 'videocount' => 1]` for each directory.
	 */
	private static function dir_counts( $dirs ) {
		return \Sgdg\Vendor\GuzzleHttp\Promise\Utils::all(
			array_map(
				static function( $dir ) {
					return \Sgdg\Vendor\GuzzleHttp\Promise\Utils::all(
						array(
							\Sgdg\API_Facade::list_directories(
								$dir,
								new \Sgdg\Frontend\API_Fields( array( 'createdTime' ) ),
								'name',
								new \Sgdg\Frontend\Single_Page_Pagination_Helper()
							),
							\Sgdg\API_Facade::list_images(
								$dir,
								new \Sgdg\Frontend\API_Fields( array( 'createdTime' ) ),
								'name',
								new \Sgdg\Frontend\Single_Page_Pagination_Helper()
							),
							\Sgdg\API_Facade::list_videos(
								$dir,
								new \Sgdg\Frontend\API_Fields( array( 'createdTime' ) ),
								'name',
								new \Sgdg\Frontend\Single_Page_Pagination_Helper()
							),
						)
					)->then(
						static function( $items ) {
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

	/**
	 * Returns a list of images in a directory
	 *
	 * @param string                           $parent_id A directory to list items of.
	 * @param \Sgdg\Frontend\Pagination_Helper $pagination_helper An initialized pagination helper.
	 * @param \Sgdg\Frontend\Options_Proxy     $options The configuration of the gallery.
	 *
	 * @return \Sgdg\Vendor\GuzzleHttp\Promise\PromiseInterface A promise resolving to a list of images in the format `['id' =>, 'id', 'description' => 'description', 'image' => 'image', 'thumbnail' => 'thumbnail']`.
	 */
	private static function images( $parent_id, $pagination_helper, $options ) {
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

	/**
	 * Returns a list of images in a directory
	 *
	 * @param string                           $parent_id A directory to list items of.
	 * @param \Sgdg\Frontend\Pagination_Helper $pagination_helper An initialized pagination helper.
	 * @param \Sgdg\Frontend\Options_Proxy     $options The configuration of the gallery.
	 *
	 * @return \Sgdg\Vendor\GuzzleHttp\Promise\PromiseInterface A promise resolving to a list of videos in the format `['id' =>, 'id', 'thumbnail' => 'thumbnail', 'mimeType' => 'mimeType', 'src' => 'src']`.
	 */
	private static function videos( $parent_id, $pagination_helper, $options ) {
		return \Sgdg\API_Facade::list_videos(
			$parent_id,
			new \Sgdg\Frontend\API_Fields(
				array(
					'id',
					'mimeType',
					'webContentLink',
					'thumbnailLink',
					'videoMediaMetadata' => array( 'width', 'height' ),
				)
			),
			$options->get( 'image_ordering' ),
			$pagination_helper
		)->then(
			static function( $videos ) use ( &$options ) {
				return array_map(
					static function( $video ) use ( &$options ) {
						return array(
							'id'        => $video['id'],
							'thumbnail' => substr( $video['thumbnailLink'], 0, -4 ) . 'h' . floor( 1.25 * $options->get( 'grid_height' ) ),
							'mimeType'  => $video['mimeType'],
							'width'     => array_key_exists( 'videoMediaMetadata', $video ) && array_key_exists( 'width', $video['videoMediaMetadata'] ) ? $video['videoMediaMetadata']['width'] : '0',
							'height'    => array_key_exists( 'videoMediaMetadata', $video ) && array_key_exists( 'height', $video['videoMediaMetadata'] ) ? $video['videoMediaMetadata']['height'] : '0',
							'src'       => self::resolve_video_url( $video['webContentLink'] ),
						);
					},
					$videos
				);
			}
		);
	}

	/**
	 * Resolves the correct URL for a video.
	 *
	 * Finds the correct URL so that a video would load in the browser.
	 *
	 * @param string $web_content_url The webContentLink returned by Google Drive API.
	 *
	 * @return string The resolved video URL.
	 */
	private static function resolve_video_url( $web_content_url ) {
		$http_client = new \Sgdg\Vendor\GuzzleHttp\Client();
		$url         = $web_content_url;
		$response    = $http_client->get( $url, array( 'allow_redirects' => false ) );

		if ( $response->hasHeader( 'Set-Cookie' ) && 0 === mb_strpos( $response->getHeader( 'Set-Cookie' )[0], 'download_warning' ) ) {
			// Handle virus scan warning.
			mb_ereg( '(download_warning[^=]*)=([^;]*).*Domain=([^;]*)', $response->getHeader( 'Set-Cookie' )[0], $regs );
			$name       = $regs[1];
			$confirm    = $regs[2];
			$domain     = $regs[3];
			$cookie_jar = \Sgdg\Vendor\GuzzleHttp\Cookie\CookieJar::fromArray( array( $name => $confirm ), $domain );

			$response = $http_client->head(
				$url . '&confirm=' . $confirm,
				array(
					'allow_redirects' => false,
					'cookies'         => $cookie_jar,
				)
			);
			$url      = $response->getHeader( 'Location' )[0];
		}
		return $url;
	}
}
