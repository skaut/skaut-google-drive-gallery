<?php
/**
 * Contains the Video_Proxy class.
 *
 * @package skaut-google-drive-gallery
 */

namespace Sgdg\Frontend;

/**
 * Contains all the functions used to handle the "video_proxy" AJAX endpoint.
 *
 * The "video_proxy" AJAX enpoint gets called for private videos over a certain size and serves the video through the webiste server.
 *
 * @phan-constructor-used-for-side-effects
 */
class Video_Proxy {

	/**
	 * Registers the "video_proxy" AJAX endpoint
	 */
	public function __construct() {
		add_action( 'wp_ajax_video_proxy', array( self::class, 'handle_ajax' ) );
		add_action( 'wp_ajax_nopriv_video_proxy', array( self::class, 'handle_ajax' ) );
	}

	/**
	 * Handles errors for the "video_proxy" AJAX endpoint.
	 *
	 * This function is a wrapper around `handle_ajax_body` that handles all the possible errors that can occur and prints or discards them.
	 *
	 * @return void
	 */
	public static function handle_ajax() {
		\Sgdg\Helpers::ajax_wrapper( array( self::class, 'ajax_handler_body' ) );
	}

	/**
	 * Actually handles the "video_proxy" AJAX endpoint.
	 *
	 * Streams the video from Google Drive through the website server.
	 *
	 * @return void
	 */
	public static function ajax_handler_body() {
		$video_hash = \Sgdg\GET_Helpers::get_string_variable( 'video_hash' );
		$transient  = get_transient( 'sgdg_video_proxy_' . $video_hash );
		if ( false === $transient ) {
			http_response_code( 404 );
			die();
		}
		$video_id  = $transient['id'];
		$mime_type = $transient['mimeType'];
		$size      = $transient['size'];

		header( 'Accept-Ranges: bytes' );
		header( 'Content-Disposition: attachment' );
		header( 'Content-Length: ' . $size );
		header( 'Content-Type: ' . $mime_type );
		// The headers above should be set even when the range request fails.
		list( $start, $end ) = self::resolve_range( $size );
		http_response_code( 206 );
		header( 'Content-Range: bytes ' . $start . '-' . $end . '/' . $size );
		$raw_client = \Sgdg\API_Client::get_unauthorized_raw_client();
		// TODO: Move this into API_Client
		$access_token = get_option( 'sgdg_access_token', false );
		if ( false === $access_token ) {
			throw new \Sgdg\Exceptions\Plugin_Not_Authorized_Exception();
		}
		$raw_client->setAccessToken( $access_token );

		if ( $raw_client->isAccessTokenExpired() ) {
			$raw_client->fetchAccessTokenWithRefreshToken( $raw_client->getRefreshToken() );
			$new_access_token    = $raw_client->getAccessToken();
			$merged_access_token = array_merge( $access_token, $new_access_token );
			update_option( 'sgdg_access_token', $merged_access_token );
		}
		$http = $raw_client->authorize();
		// X-Goog-Drive-Resource-Keys header
		$response = $http->request(
			'GET',
			'drive/v3/files/' . $video_id,
			array(
				'query' => array(
					'alt' => 'media'
				),
				'headers' => array(
					'Range' => 'bytes=' . $start . '-' . $end,
				),
				'stream' => true,
			)
		);
		$stream = $response->getBody()->detach();
		if ( is_null( $stream ) ) {
			http_response_code( 500 );
			die();
		}
		ob_end_clean();
		fpassthru( $stream );
	}

	/**
	 * Resolves the start and end of a HTTP range request.
	 *
	 * @param int $size The size of the video file in bytes.
	 *
	 * @return array{0: int, 1: int}|never The start and end of the range.
	 */
	private static function resolve_range( $size ) {
		if ( ! isset( $_SERVER['HTTP_RANGE'] ) ) {
			return array( 0, $size - 1 );
		}
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$header = sanitize_text_field( wp_unslash( strval( $_SERVER['HTTP_RANGE'] ) ) );
		if ( ! str_starts_with( $header, 'bytes=' ) ) {
			http_response_code( 416 );
			die();
		}
		$header = substr( $header, 6 );
		// Multipart range requests are not supported.
		if ( str_contains( $header, ',' ) ) {
			http_response_code( 416 );
			die();
		}
		$limits = explode( '-', $header );
		if ( 2 !== count( $limits ) ) {
			http_response_code( 416 );
			die();
		}
		$raw_start = $limits[0];
		$raw_end = $limits[1];
		$start = is_numeric( $raw_start ) ? intval( $raw_start ) : 0;
		$end = is_numeric( $raw_end ) ? intval( $raw_end ) : $size - 1;
		if ( $start < 0 ) {
			$start = 0;
		}
		if ( $end >= $size ) {
			$end = $size - 1;
		}
		if ( $start > $end ) {
			http_response_code( 416 );
			die();
		}
		return array( $start, $end );
	}
}
