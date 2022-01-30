<?php
/**
 * Contains all the functions used to handle the "video_proxy" AJAX endpoint.
 *
 * The "video_proxy" AJAX enpoint gets called for private videos over a certain size and serves the video through the webiste server.
 *
 * @package skaut-google-drive-gallery
 */

namespace Sgdg\Frontend\Video_Proxy;

/**
 * Registers the "video_proxy" AJAX endpoint
 *
 * @return void
 */
function register() {
	add_action( 'wp_ajax_video_proxy', '\\Sgdg\\Frontend\\Video_Proxy\\handle_ajax' );
	add_action( 'wp_ajax_nopriv_video_proxy', '\\Sgdg\\Frontend\\Video_Proxy\\handle_ajax' );
}

/**
 * Handles errors for the "video_proxy" AJAX endpoint.
 *
 * This function is a wrapper around `handle_ajax_body` that handles all the possible errors that can occur and prints or discards them.
 *
 * @return void
 */
function handle_ajax() {
	try {
		ajax_handler_body();
	} catch ( \Exception $e ) {
		if ( \Sgdg\is_debug_display() ) {
			wp_die( esc_html( $e->getMessage() ) );
		}
		wp_die();
	}
}

/**
 * Actually handles the "video_proxy" AJAX endpoint.
 *
 * Streams the video from Google Drive through the website server.
 *
 * @return void
 */
function ajax_handler_body() {
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
