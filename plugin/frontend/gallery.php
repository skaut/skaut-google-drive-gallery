<?php
namespace Sgdg\Frontend\Gallery;

function register() {
	add_action( 'wp_ajax_gallery', '\\Sgdg\\Frontend\\Gallery\\handle_ajax' );
	add_action( 'wp_ajax_nopriv_gallery', '\\Sgdg\\Frontend\\Gallery\\handle_ajax' );
}

function handle_ajax() {
	try {
		ajax_handler_body();
	} catch ( \Sgdg\Vendor\Google_Service_Exception $e ) {
		if ( 'userRateLimitExceeded' === $e->getErrors()[0]['reason'] ) {
			wp_send_json( [ 'error' => esc_html__( 'The maximum number of requests has been exceeded. Please try again in a minute.', 'skaut-google-drive-gallery' ) ] );
		} else {
			wp_send_json( [ 'error' => $e->getErrors()[0]['message'] ] );
		}
	} catch ( \Exception $e ) {
		wp_send_json( [ 'error' => $e->getMessage() ] );
	}
}

function ajax_handler_body() {
	list( $client, $dir, $options ) = \Sgdg\Frontend\Page\getContext();

	$ret = [];
	if ( isset( $_GET['path'] ) && '' !== $_GET['path'] ) {
		$ret['path'] = path_names( $client, explode( '/', $_GET['path'] ) );
	}
	$ret = array_merge( $ret, \Sgdg\Frontend\Page\getPage( $client, $dir, 1, $options ) );
	wp_send_json( $ret );
}

function path_names( $client, array $path, array $used_path = [] ) {
	$client->getClient()->setUseBatch( true );
	$batch = $client->createBatch();
	foreach ( $path as $segment ) {
		$request = $client->files->get(
			$segment,
			[
				'supportsTeamDrives' => true,
				'fields'             => 'name',
			]
		);
		$batch->add( $request, $segment );
	}
	$responses = $batch->execute();
	$client->getClient()->setUseBatch( false );
	$ret = [];
	foreach ( $path as $segment ) {
		$response = $responses[ 'response-' . $segment ];
		if ( $response instanceof \Sgdg\Vendor\Google_Service_Exception ) {
			throw $response;
		}
		$ret[] = [
			'id'   => $segment,
			'name' => $response->getName(),
		];
	}
	return $ret;
}
