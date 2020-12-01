<?php
/**
 * Contains all the functions used to handle the "page" AJAX endpoint.
 *
 * The "page" AJAX enpoint gets called each time the user needs to fetch items for a gallery.
 *
 * @package skaut-google-drive-gallery
 */

namespace Sgdg\Frontend\Page;

/**
 * Registers the "page" AJAX endpoint
 */
function register() {
	add_action( 'wp_ajax_page', '\\Sgdg\\Frontend\\Page\\handle_ajax' );
	add_action( 'wp_ajax_nopriv_page', '\\Sgdg\\Frontend\\Page\\handle_ajax' );
}

/**
 * Handles errors for the "page" AJAX endpoint.
 *
 * This function is a wrapper around `handle_ajax_body` that handles all the possible errors that can occur and sends them back as error messages.
 */
function handle_ajax() {
	try {
		ajax_handler_body();
	} catch ( \Sgdg\Vendor\Google_Service_Exception $e ) {
		if ( 'userRateLimitExceeded' === $e->getErrors()[0]['reason'] || 'rateLimitExceeded' === $e->getErrors()[0]['reason'] ) {
			wp_send_json( array( 'error' => esc_html__( 'The maximum number of requests has been exceeded. Please try again in a minute.', 'skaut-google-drive-gallery' ) ) );
		} else {
			wp_send_json( array( 'error' => $e->getErrors()[0]['message'] ) );
		}
	} catch ( \Exception $e ) {
		wp_send_json( array( 'error' => $e->getMessage() ) );
	}
}

/**
 * Actually handles the "gallery" AJAX endpoint.
 *
 * Returns a list of directories and a list of images.
 *
 * @see get_page()
 */
function ajax_handler_body() {
	$context_promise = get_context()->then( // TODO: Fix this hacky solution.
		static function( $context ) {
			list( $client, $dir, $options ) = $context;
			$pagination_helper              = ( new \Sgdg\Frontend\Pagination_Helper() )->withOptions( $options, false );

			return get_page( $client, $dir, $pagination_helper, $options );
		}
	)->then(
		static function( $page ) {
			wp_send_json( $page );
		}
	);
	\Sgdg\API_Client::execute( array( $context_promise ) );
}

/**
 * Returns common variables used by different parts of the codebase
 *
 * @throws \Exception The gallery has expired.
 *
 * @return \Sgdg\Vendor\GuzzleHttp\Promise\PromiseInterface A promise resolving to an array of the form {
 *     @type \Sgdg\Vendor\Google_Drive_service A Google Drive API client.
 *     @type string The root directory of the gallery.
 *     @type \Sgdg\Frontend\Options_Proxy The configuration of the gallery.
 * }
 */
function get_context() {
	$client = \Sgdg\API_Client::get_drive_client();

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! isset( $_GET['hash'] ) ) {
		throw new \Exception( esc_html__( 'The gallery has expired.', 'skaut-google-drive-gallery' ) );
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$transient = get_transient( 'sgdg_hash_' . sanitize_text_field( wp_unslash( $_GET['hash'] ) ) );

	if ( false === $transient ) {
		throw new \Exception( esc_html__( 'The gallery has expired.', 'skaut-google-drive-gallery' ) );
	}

	$path    = array( $transient['root'] );
	$options = new \Sgdg\Frontend\Options_Proxy( $transient['overriden'] );

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( isset( $_GET['path'] ) && '' !== $_GET['path'] ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$path = array_merge( $path, explode( '/', sanitize_text_field( wp_unslash( $_GET['path'] ) ) ) );
	}

	return verify_path( $path )->then(
		static function() use ( $client, $path, $options ) {
			return array( $client, end( $path ), $options );
		}
	);
}

/**
 * Checks that a path is a valid path on Google Drive.
 *
 * @param array $path A list of directory IDs.
 *
 * @return \Sgdg\Vendor\GuzzleHttp\Promise\PromiseInterface A promise that resolves if the path is valid
 */
function verify_path( array $path ) {
	if ( count( $path ) === 1 ) {
		\Sgdg\API_Client::preamble(); // TODO: Remove?
		return new \Sgdg\Vendor\GuzzleHttp\Promise\FulfilledPromise( null );
	}
	return \Sgdg\API_Client::check_directory_in_directory( $path[1], $path[0] )->then(
		static function() use ( $path ) {
			array_shift( $path );
			return verify_path( $path );
		},
		static function( $exception ) {
			if ( $exception instanceof \Sgdg\Exceptions\Directory_Not_Found_Exception ) {
				$exception = new \Sgdg\Exceptions\Path_Not_Found_Exception();
			}
			return new \Sgdg\Vendor\GuzzleHttp\Promise\RejectedPromise( $exception );
		}
	);
}

/**
 * Return one page worth of items
 *
 * Lists one page of items - first directories and then images, up until the number of items per page is reached.
 *
 * @param \Sgdg\Vendor\Google_Service_Drive $client A Google Drive API client.
 * @param string                            $dir A directory to list items of.
 * @param \Sgdg\Frontend\Pagination_Helper  $pagination_helper An initialized pagination helper.
 * @param \Sgdg\Frontend\Options_Proxy      $options The configuration of the gallery.
 *
 * @return \Sgdg\Vendor\GuzzleHttp\Promise\PromiseInterface A promise resolving to the page return value.
 */
function get_page( $client, $dir, $pagination_helper, $options ) {
	return \Sgdg\Vendor\GuzzleHttp\Promise\Utils::all( array() )->then(
		static function( $page ) use ( $dir, $pagination_helper, $options ) {
			if ( $pagination_helper->should_continue() ) {
				$page['directories'] = directories( $dir, $pagination_helper, $options );
			}
			return \Sgdg\Vendor\GuzzleHttp\Promise\Utils::all( $page );
		}
	)->then(
		static function( $page ) use ( $dir, $pagination_helper, $options ) {
			if ( $pagination_helper->should_continue() ) {
				$page['images'] = images( $dir, $pagination_helper, $options );
			}
			return \Sgdg\Vendor\GuzzleHttp\Promise\Utils::all( $page );
		}
	);

	/*
	$ret = array();
	if ( $pagination_helper->should_continue() ) {
		$ret['videos'] = videos( $client, $dir, $pagination_helper, $options );
	}
	$ret['more'] = $pagination_helper->has_more();
	return $ret;
	*/
}

/**
 * Returns a list of subdirectories in a directory.
 *
 * @param string                           $dir A directory to list items of.
 * @param \Sgdg\Frontend\Pagination_Helper $pagination_helper An initialized pagination helper.
 * @param \Sgdg\Frontend\Options_Proxy     $options The configuration of the gallery.
 *
 * @return \Sgdg\Vendor\GuzzleHttp\Promise\Promise A promise resolving to a list of directories in the format `['id' =>, 'id', 'name' => 'name', 'thumbnail' => 'thumbnail', 'dircount' => 1, 'imagecount' => 1]`.
 */
function directories( $dir, $pagination_helper, $options ) {
	return ( \Sgdg\API_Client::list_directories( $dir, new \Sgdg\Frontend\API_Fields( array( 'id', 'name' ) ), $options->get( 'dir_ordering' ), $pagination_helper )->then(
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

			return \Sgdg\Vendor\GuzzleHttp\Promise\Utils::all( array( $files, dir_images( $ids, $options ), dir_counts( $ids ) ) );
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
			return $files;
		}
	) );
}

/**
 * Creates API requests for directory thumbnails
 *
 * Takes a batch and adds to it a request for the first image in each directory.
 *
 * @param array                        $dirs A list of directory IDs.
 * @param \Sgdg\Frontend\Options_Proxy $options The configuration of the gallery.
 *
 * @return \Sgdg\Vendor\GuzzleHttp\Promise\PromiseInterface A promise resolving to a list of directory images
 */
function dir_images( $dirs, $options ) {
	return \Sgdg\Vendor\GuzzleHttp\Promise\Utils::all(
		array_map(
			static function( $dir ) use ( &$options ) {
				return \Sgdg\API_Client::list_images(
					$dir,
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
 * @param array $dirs A list of directory IDs.
 *
 * @return \Sgdg\Vendor\GuzzleHttp\Promise\PromiseInterface A promise resolving to a list of subdirectory, image and video counts of format `['dircount' => 1, 'imagecount' => 1, 'videocount' => 1]` for each directory.
 */
function dir_counts( $dirs ) {
	return \Sgdg\Vendor\GuzzleHttp\Promise\Utils::all(
		array_map(
			static function( $dir ) {
				return \Sgdg\Vendor\GuzzleHttp\Promise\Utils::all(
					array(
						\Sgdg\API_Client::list_directories(
							$dir,
							new \Sgdg\Frontend\API_Fields( array( 'id' ) ), // TODO: Is it really needed?
							'name',
							( new \Sgdg\Frontend\Pagination_Helper() )->withValues( 0, 1000 ) // TODO: Specialize.
						),
						\Sgdg\API_Client::list_images(
							$dir,
							new \Sgdg\Frontend\API_Fields( array( 'id' ) ), // TODO: Is it really needed?
							'name',
							( new \Sgdg\Frontend\Pagination_Helper() )->withValues( 0, 1000 ) // TODO: Specialize.
						),
						\Sgdg\API_Client::list_videos(
							$dir,
							new \Sgdg\Frontend\API_Fields( array( 'id' ) ), // TODO: Is it really needed?
							'name',
							( new \Sgdg\Frontend\Pagination_Helper() )->withValues( 0, 1000 ) // TODO: Specialize.
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
 * @param string                           $dir A directory to list items of.
 * @param \Sgdg\Frontend\Pagination_Helper $pagination_helper An initialized pagination helper.
 * @param \Sgdg\Frontend\Options_Proxy     $options The configuration of the gallery.
 *
 * @return \Sgdg\Vendor\GuzzleHttp\Promise\Promise A promise resolving to a list of images in the format `['id' =>, 'id', 'description' => 'description', 'image' => 'image', 'thumbnail' => 'thumbnail']`.
 */
function images( $dir, $pagination_helper, $options ) {
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
	return \Sgdg\API_Client::list_images( $dir, $fields, $order_by, $pagination_helper )->then(
		static function( $images ) use ( $options ) {
			$images = array_map(
				static function( $image ) use ( $options ) {
					return image_preprocess( $image, $options );
				},
				$images
			);
			return images_order( $images, $options );
		}
	);
}

/**
 * Processes an image response.
 *
 * @param array                        $image A Google Drive file response.
 * @param \Sgdg\Frontend\Options_Proxy $options The configuration of the gallery.
 *
 * @return array {
 *     @type string      $id The ID of the image.
 *     @type string      $description The description (caption) of the image.
 *     @type string      $image A URL of the image to be displayed in the lightbox
 *     @type string      $thumbnail A URL of a thumbnail to be displayed in the image grid.
 *     @type string|null $timestamp A timestamp to order the images by. Optional.
 * }
 */
function image_preprocess( $image, $options ) {
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
			$ret['timestamp'] = $timestamp->format( 'U' );
		}
	}
	return $ret;
}

/**
 * Orders images.
 *
 * @param array                        $images A list of images in the format `['id' =>, 'id', 'description' => 'description', 'image' => 'image', 'thumbnail' => 'thumbnail', 'timestamp' => new \DateTime()]`.
 * @param \Sgdg\Frontend\Options_Proxy $options The configuration of the gallery.
 *
 * @return array An ordered list of images in the format `['id' =>, 'id', 'description' => 'description', 'image' => 'image', 'thumbnail' => 'thumbnail']`.
 */
function images_order( $images, $options ) {
	if ( $options->get_by( 'image_ordering' ) === 'time' ) {
		usort(
			$images,
			static function( $first, $second ) use ( $options ) {
				$asc = $first['timestamp'] - $second['timestamp'];
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
 * @param \Sgdg\Vendor\Google_Service_Drive $client A Google Drive API client.
 * @param string                            $dir A directory to list items of.
 * @param \Sgdg\Frontend\Pagination_Helper  $pagination_helper An initialized pagination helper.
 * @param \Sgdg\Frontend\Options_Proxy      $options The configuration of the gallery.
 *
 * @throws \Sgdg\Vendor\Google_Service_Exception A Google Drive API exception.
 *
 * @return array A list of videos in the format `['id' =>, 'id', 'thumbnail' => 'thumbnail', 'mimeType' => 'mimeType', 'src' => 'src']`.
 */
function videos( $client, $dir, $pagination_helper, $options ) {
	$ret        = array();
	$page_token = null;
	do {
		$params   = array(
			'q'                         => '"' . $dir . '" in parents and mimeType contains "video/" and trashed = false',
			'supportsAllDrives'         => true,
			'includeItemsFromAllDrives' => true,
			'orderBy'                   => $options->get( 'image_ordering' ),
			'pageToken'                 => $page_token,
			'pageSize'                  => $pagination_helper->next_list_size( 1000 ),
			'fields'                    => 'nextPageToken, files(id, mimeType, webContentLink, thumbnailLink, videoMediaMetadata(width, height))',
		);
		$response = $client->files->listFiles( $params );
		if ( $response instanceof \Sgdg\Vendor\Google_Service_Exception ) {
			throw $response;
		}
		$pagination_helper->iterate(
			$response->getFiles(),
			static function( $file ) use ( &$ret, &$options ) {
				$ret[] = video_preprocess( $file, $options );
			}
		);
		$page_token = $response->getNextPageToken();
	} while ( null !== $page_token && $pagination_helper->should_continue() );
	return $ret;
}

/**
 * Processes an image response.
 *
 * @param \Sgdg\Vendor\Google_Service_Drive_DriveFile $file A Google Drive file response.
 * @param \Sgdg\Frontend\Options_Proxy                $options The configuration of the gallery.
 *
 * @return array {
 *     @type string $id The ID of the image.
 *     @type string $thumbnail A URL of a thumbnail to be displayed in the image grid.
 *     @type string $mimeType The MIME type of the video file.
 *     @type int    $width The width of the video.
 *     @type int    $height The height of the video.
 *     @type src    $src The URL of the video file.
 * }
 */
function video_preprocess( $file, $options ) {
	$video_metadata = $file->getVideoMediaMetadata();
	$width          = is_null( $video_metadata ) ? '0' : $video_metadata->getWidth();
	$height         = is_null( $video_metadata ) ? '0' : $video_metadata->getHeight();
	return array(
		'id'        => $file->getId(),
		'thumbnail' => substr( $file->getThumbnailLink(), 0, -4 ) . 'h' . floor( 1.25 * $options->get( 'grid_height' ) ),
		'mimeType'  => $file->getMimeType(),
		'width'     => $width,
		'height'    => $height,
		'src'       => resolve_video_url( $file->getWebContentLink() ),
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
function resolve_video_url( $web_content_url ) {
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
