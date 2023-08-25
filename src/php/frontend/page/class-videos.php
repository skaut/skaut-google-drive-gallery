<?php
/**
 * Contains the Videos class.
 *
 * @package skaut-google-drive-gallery
 */

namespace Sgdg\Frontend\Page;

use Sgdg\API_Facade;
use Sgdg\Frontend\API_Fields;
use Sgdg\Frontend\Options_Proxy;
use Sgdg\Frontend\Pagination_Helper;
use Sgdg\GET_Helpers;
use Sgdg\Vendor\GuzzleHttp\Client;
use Sgdg\Vendor\GuzzleHttp\Cookie\CookieJar;
use Sgdg\Vendor\GuzzleHttp\Promise\FulfilledPromise;
use Sgdg\Vendor\GuzzleHttp\Promise\PromiseInterface;
use Sgdg\Vendor\GuzzleHttp\Promise\Utils;
use const DAY_IN_SECONDS;

/**
 * Contains all the functions used to display videos in a gallery.
 */
final class Videos {

	/**
	 * Returns a list of videos in a directory
	 *
	 * @param string            $parent_id A directory to list items of.
	 * @param Pagination_Helper $pagination_helper An initialized pagination helper.
	 * @param Options_Proxy     $options The configuration of the gallery.
	 *
	 * @return PromiseInterface A promise resolving to a list of videos in the format `['id' =>, 'id', 'thumbnail' => 'thumbnail', 'mimeType' => 'mimeType', 'src' => 'src']`.
	 */
	public static function get( $parent_id, $pagination_helper, $options ) {
		return API_Facade::list_videos(
			$parent_id,
			new API_Fields(
				array(
					'id',
					'mimeType',
					'size',
					'webContentLink',
					'webViewLink',
					'thumbnailLink',
					'videoMediaMetadata' => array( 'width', 'height' ),
					'copyRequiresWriterPermission',
					'permissions'        => array( 'type', 'role' ),
				)
			),
			$pagination_helper,
			$options->get( 'image_ordering' )
		)->then(
			static function ( $raw_videos ) use ( $options ) {
				$raw_videos         = array_values(
					array_filter(
						$raw_videos,
						static function ( $video ) {
							return ! is_null( $video['thumbnailLink'] );
						}
					)
				);
				$videos             = array_map(
					static function ( $video ) use ( $options ) {
						return array(
							'height'    => array_key_exists( 'videoMediaMetadata', $video ) &&
								array_key_exists( 'height', $video['videoMediaMetadata'] )
								? $video['videoMediaMetadata']['height']
								: '0',
							'id'        => $video['id'],
							'mimeType'  => $video['mimeType'],
							'thumbnail' => substr( $video['thumbnailLink'], 0, -4 ) .
								'h' .
								floor( 1.25 * $options->get( 'grid_height' ) ),
							'width'     => array_key_exists( 'videoMediaMetadata', $video ) &&
								array_key_exists( 'width', $video['videoMediaMetadata'] )
								? $video['videoMediaMetadata']['width']
								: '0',
						);
					},
					$raw_videos
				);
				$video_url_promises = array_map(
					static function ( $video ) {
							return self::resolve_url(
								$video['id'],
								$video['mimeType'],
								$video['size'],
								$video['webContentLink'],
								$video['webViewLink'],
								$video['copyRequiresWriterPermission'],
								array_key_exists( 'permissions', $video ) ? $video['permissions'] : array()
							);
					},
					$raw_videos
				);

				return Utils::all(
					array( $videos, Utils::all( $video_url_promises ) )
				);
			}
		)->then(
			static function ( $tuple ) {
				list( $videos, $video_urls ) = $tuple;
				$count                       = count( $videos );

				for ( $i = 0; $i < $count; ++$i ) {
					$videos[ $i ]['src'] = $video_urls[ $i ];
				}

				return $videos;
			}
		);
	}

	/**
	 * Resolves the correct URL for a video.
	 *
	 * Finds the correct URL so that a video would load in the browser.
	 *
	 * @param string                                   $video_id The ID of the video.
	 * @param string                                   $mime_type The MIME type of the video.
	 * @param int                                      $size The size of the video in bytes.
	 * @param string                                   $web_content_url The webContentLink returned by Google Drive API.
	 * @param string                                   $web_view_url The webViewLink returned by Google Drive API.
	 * @param bool                                     $copy_requires_writer_permission Whether the option to download the file is disabled for readers.
	 * @param array<array{type: string, role: string}> $permissions The file permissions.
	 *
	 * @return PromiseInterface A promise resolving to the video URL.
	 *
	 * @SuppressWarnings(PHPMD.LongVariable)
	 */
	private static function resolve_url(
		$video_id,
		$mime_type,
		$size,
		$web_content_url,
		$web_view_url,
		$copy_requires_writer_permission,
		$permissions
	) {
		if ( $copy_requires_writer_permission || $size > 25165824 ) {
			return new FulfilledPromise(
				self::get_proxy_video_url( $video_id, $mime_type, $size )
			);
		}

		foreach ( $permissions as $permission ) {
			if (
				'anyone' === $permission['type'] &&
				in_array( $permission['role'], array( 'reader', 'writer' ), true )
			) {
				return self::get_direct_video_url( $web_content_url );
			}
		}

		$http_client = new Client();

		return $http_client->getAsync( $web_view_url, array( 'allow_redirects' => false ) )->then(
			static function ( $response ) use ( $video_id, $mime_type, $size, $web_content_url ) {
				if ( 200 === $response->getStatusCode() ) {
					return self::get_direct_video_url( $web_content_url );
				}

				return new FulfilledPromise(
					self::get_proxy_video_url( $video_id, $mime_type, $size )
				);
			}
		);
	}

	/**
	 * Returns the direct URL for a video.
	 *
	 * Goes through the download warning and returns the direct download URL for a video.
	 *
	 * @param string $web_content_url The webContentLink returned by Google Drive API.
	 *
	 * @return PromiseInterface A promise resolving to the video URL.
	 */
	private static function get_direct_video_url( $web_content_url ) {
		$http_client = new Client();

		return $http_client->getAsync( $web_content_url, array( 'allow_redirects' => false ) )->then(
			static function ( $response ) use ( $http_client, $web_content_url ) {
				$url = $web_content_url;

				if (
					// @phan-suppress-next-line PhanPluginNonBoolInLogicalArith
					! $response->hasHeader( 'Set-Cookie' ) ||
					0 !== mb_strpos( $response->getHeader( 'Set-Cookie' )[0], 'download_warning' )
				) {
					return new FulfilledPromise( $web_content_url );
				}

				// Handle virus scan warning.
				mb_ereg(
					'(download_warning[^=]*)=([^;]*).*Domain=([^;]*)',
					$response->getHeader( 'Set-Cookie' )[0],
					$regs
				);
				$name       = $regs[1];
				$confirm    = $regs[2];
				$domain     = $regs[3];
				$cookie_jar = CookieJar::fromArray(
					array( $name => $confirm ),
					$domain
				);

				return $http_client->headAsync(
					$url . '&confirm=' . $confirm,
					array(
						'allow_redirects' => false,
						'cookies'         => $cookie_jar,
					)
				)->then(
					static function ( $response ) {
						return $response->getHeader( 'Location' )[0];
					}
				);
			}
		);
	}

	/**
	 * Returns the proxy URL for a video.
	 *
	 * Sets up a proxy in WordPress and returns the address of this proxy.
	 *
	 * @param string $video_id The ID of the video.
	 * @param string $mime_type The MIME type of the video.
	 * @param int    $size The size of the video in bytes.
	 *
	 * @return string The resolved video URL.
	 */
	private static function get_proxy_video_url( $video_id, $mime_type, $size ) {
		$gallery_hash = GET_Helpers::get_string_variable( 'hash' );
		$video_hash   = hash( 'sha256', $gallery_hash . $video_id );
		set_transient(
			'sgdg_video_proxy_' . $video_hash,
			array(
				'id'       => $video_id,
				'mimeType' => $mime_type,
				'size'     => $size,
			),
			DAY_IN_SECONDS
		);

		return admin_url( 'admin-ajax.php?action=video_proxy&video_hash=' . $video_hash );
	}
}
