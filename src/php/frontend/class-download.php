<?php
/**
 * Contains the Download class.
 *
 * @package skaut-google-drive-gallery
 */

namespace Sgdg\Frontend;

use PHPMD\Attribute\SuppressWarnings;
use PHPMD\Rule\Design\ExitExpression;
use Sgdg\API_Client;
use Sgdg\Exceptions\Plugin_Not_Authorized_Exception;
use Sgdg\GET_Helpers;
use Sgdg\Helpers;
use const DAY_IN_SECONDS;

/**
 * Contains all the functions used to handle the "download" AJAX endpoint.
 *
 * The "download" AJAX endpoint serves the original file of an image or a video through the website, so that it can be downloaded (even if it isn't shared publicly) under its name from Drive. It is only available for galleries that allow downloading.
 *
 * @phan-constructor-used-for-side-effects
 */
final class Download {

	/**
	 * Registers the "download" AJAX endpoint
	 */
	public function __construct() {
		add_action( 'wp_ajax_download', array( self::class, 'handle_ajax' ) );
		add_action( 'wp_ajax_nopriv_download', array( self::class, 'handle_ajax' ) );
	}

	/**
	 * Returns the download URL for a file of the current gallery.
	 *
	 * The URL only contains a hash, the file itself is stored in a transient, so that only files shown in a gallery that allows downloading can be downloaded.
	 *
	 * @param string $file_id The ID of the file.
	 * @param string $name The file name.
	 * @param string $mime_type The MIME type of the file.
	 * @param int    $size The size of the file in bytes, 0 if unknown.
	 *
	 * @return string The download URL.
	 */
	public static function url( $file_id, $name, $mime_type, $size ) {
		$file_hash = hash( 'sha256', GET_Helpers::get_string_variable( 'hash' ) . $file_id );
		set_transient(
			'sgdg_download_' . $file_hash,
			array(
				'id'       => $file_id,
				'mimeType' => $mime_type,
				'name'     => $name,
				'size'     => $size,
			),
			DAY_IN_SECONDS
		);

		return admin_url( 'admin-ajax.php?action=download&file=' . $file_hash );
	}

	/**
	 * Handles errors for the "download" AJAX endpoint.
	 *
	 * @return void
	 */
	public static function handle_ajax() {
		Helpers::ajax_wrapper( array( self::class, 'ajax_handler_body' ) );
	}

	/**
	 * Actually handles the "download" AJAX endpoint.
	 *
	 * Streams the file from Google Drive through the website server.
	 *
	 * @return void
	 *
	 * @throws Plugin_Not_Authorized_Exception Not authorized.
	 */
	#[SuppressWarnings( ExitExpression::class )]
	public static function ajax_handler_body() {
		$file = get_transient( 'sgdg_download_' . GET_Helpers::get_string_variable( 'file' ) );

		if ( false === $file ) {
			http_response_code( 404 );
			die;
		}

		$http     = API_Client::get_authorized_raw_client()->authorize();
		$response = $http->request(
			'GET',
			'drive/v3/files/' . $file['id'],
			array(
				'query'  => array(
					'alt' => 'media',
				),
				'stream' => true,
			)
		);
		$stream   = $response->getBody()->detach();

		if ( is_null( $stream ) || 200 !== $response->getStatusCode() ) {
			http_response_code( 502 );
			die;
		}

		header( 'Content-Type: ' . $file['mimeType'] );
		header( 'Content-Disposition: ' . Video_Proxy::content_disposition( $file['name'] ) );

		if ( 0 < intval( $file['size'] ) ) {
			header( 'Content-Length: ' . intval( $file['size'] ) );
		}

		Video_Proxy::stream_to_output( $stream );
	}
}
