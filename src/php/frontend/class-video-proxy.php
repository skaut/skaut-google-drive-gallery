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
	private static function ajax_handler_body() {
		// < content-length: 5091642
		// < content-type: video/mp4
		// if range request:
		// Accept-ranges
		// < content-range: bytes 0-5091641/5091642
		header( 'Content-Disposition: attachment' );
		if ( isset( $_SERVER['HTTP_RANGE'] ) ) {
			var_dump( $_SERVER['HTTP_RANGE'] );
		}
	}

}
