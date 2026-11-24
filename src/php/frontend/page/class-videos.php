<?php
/**
 * Contains the Videos class.
 *
 * @package skaut-google-drive-gallery
 */

namespace Sgdg\Frontend\Page;

use PHPMD\Attribute\SuppressWarnings;
use PHPMD\Rule\Naming\LongVariable;
use Sgdg\API_Facade;
use Sgdg\Exceptions\Internal_Exception;
use Sgdg\Exceptions\Plugin_Not_Authorized_Exception;
use Sgdg\Exceptions\Unsupported_Value_Exception;
use Sgdg\Frontend\API_Fields;
use Sgdg\Frontend\Download;
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
	 * @return PromiseInterface A promise resolving to a list of videos in the format `['id' =>, 'id', 'thumbnail' => 'thumbnail', 'mimeType' => 'mimeType', 'src' => 'src']`, plus `'name' => 'name'` if video names are enabled.
	 *
	 * @throws Internal_Exception The method was called without an initialized batch.
	 * @throws Plugin_Not_Authorized_Exception Not authorized.
	 * @throws Unsupported_Value_Exception A field that is not supported was passed in `$fields`.
	 */
	public static function get( $parent_id, $pagination_helper, $options ) {
		$show_names     = 'true' === $options->get( 'video_names' );
		$allow_download = 'true' === $options->get( 'allow_download' );

		return API_Facade::list_videos(
			$parent_id,
			new API_Fields(
				array(
					'id',
					'name',
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
			static function ( $raw_videos ) use ( $options, $show_names, $allow_download ) {
				$videos             = array_map(
					static function ( $video ) use ( $options, $show_names, $allow_download ) {
						return self::format_video( $video, $options, $show_names, $allow_download );
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
								array_key_exists( 'permissions', $video ) ? $video['permissions'] : array(),
								strval( $video['name'] )
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
	 * Formats a video file name for display in the gallery.
	 *
	 * Removes the file extension and escapes the name for use in HTML.
	 *
	 * @param string $name The raw file name.
	 *
	 * @return string The escaped name without the extension.
	 */
	private static function display_name( $name ) {
		$dot = mb_strrpos( $name, '.' );

		if ( false !== $dot && $dot > 0 ) {
			$name = mb_substr( $name, 0, $dot );
		}

		return esc_html( $name );
	}

	/**
	 * Converts a video returned by the API into the format of the response.
	 *
	 * @param array<string, mixed> $video The video as returned by the API.
	 * @param Options_Proxy        $options The configuration of the gallery.
	 * @param bool                 $show_names Whether the names of the videos are shown.
	 * @param bool                 $allow_download Whether downloading is allowed.
	 *
	 * @return array<string, mixed> The video.
	 */
	private static function format_video( $video, $options, $show_names, $allow_download ) {
		$ret = array(
			'height'    => self::dimension( $video, 'height' ),
			'id'        => $video['id'],
			'mimeType'  => $video['mimeType'],
			// Drive has no thumbnail e.g. for videos that are still being processed. Such videos are shown with a placeholder.
			'thumbnail' => isset( $video['thumbnailLink'] )
				? substr( $video['thumbnailLink'], 0, -4 ) .
					'h' .
					floor( 1.25 * $options->get( 'grid_height' ) )
				: '',
			'width'     => self::dimension( $video, 'width' ),
		);

		if ( $show_names && array_key_exists( 'name', $video ) ) {
			$ret['name'] = self::display_name( $video['name'] );
		}

		if ( $allow_download ) {
			$ret['download'] = self::download_url( $video );
		}

		return $ret;
	}

	/**
	 * Returns the download URL of a video.
	 *
	 * @param array<string, mixed> $video The video as returned by the API.
	 *
	 * @return string The download URL.
	 */
	private static function download_url( $video ) {
		return Download::url(
			$video['id'],
			isset( $video['name'] ) ? strval( $video['name'] ) : '',
			$video['mimeType'],
			isset( $video['size'] ) ? intval( $video['size'] ) : 0
		);
	}

	/**
	 * Returns a dimension of a video.
	 *
	 * @param array<string, mixed> $video The video as returned by the API.
	 * @param string               $dimension Either `width` or `height`.
	 *
	 * @return int|string The dimension or `'0'` if it isn't known.
	 */
	private static function dimension( $video, $dimension ) {
		if (
			! array_key_exists( 'videoMediaMetadata', $video ) ||
			! array_key_exists( $dimension, $video['videoMediaMetadata'] )
		) {
			return '0';
		}

		return $video['videoMediaMetadata'][ $dimension ];
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
	 * @param string                                   $name The file name of the video.
	 *
	 * @return PromiseInterface A promise resolving to the video URL.
	 */
	#[SuppressWarnings( LongVariable::class )]
	private static function resolve_url(
		$video_id,
		$mime_type,
		$size,
		$web_content_url,
		$web_view_url,
		$copy_requires_writer_permission,
		$permissions,
		$name
	) {
		if ( $copy_requires_writer_permission || $size > 25165824 ) {
			return new FulfilledPromise(
				self::get_proxy_video_url( $video_id, $mime_type, $size, $name )
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

		return $http_client->getAsync(
			$web_view_url,
			array(
				'allow_redirects' => false,
				'http_errors'     => false,
			)
		)->then(
			static function ( $response ) use ( $video_id, $mime_type, $size, $web_content_url, $name ) {
				if ( 200 === $response->getStatusCode() ) {
					return self::get_direct_video_url( $web_content_url );
				}

				return new FulfilledPromise(
					self::get_proxy_video_url( $video_id, $mime_type, $size, $name )
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
	 * @param string $name The file name of the video.
	 *
	 * @return string The resolved video URL.
	 */
	private static function get_proxy_video_url( $video_id, $mime_type, $size, $name ) {
		$gallery_hash = GET_Helpers::get_string_variable( 'hash' );
		$video_hash   = hash( 'sha256', $gallery_hash . $video_id );
		set_transient(
			'sgdg_video_proxy_' . $video_hash,
			array(
				'id'       => $video_id,
				'mimeType' => $mime_type,
				'name'     => $name,
				'size'     => $size,
			),
			DAY_IN_SECONDS
		);

		return admin_url( 'admin-ajax.php?action=video_proxy&video_hash=' . $video_hash );
	}
}
