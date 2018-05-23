<?php
namespace Sgdg\Admin\TinyMCE;

if ( ! is_admin() ) {
	return;
}

function register() {
	add_action( 'admin_head', '\\Sgdg\\Admin\\TinyMCE\\add' );
	add_action( 'wp_ajax_list_gallery_dir', '\\Sgdg\\Admin\\TinyMCE\\handle_ajax' );
}

function add() {
	if ( (! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' )) || 'true' !== get_user_option( 'rich_editing' ) ) {
		return;
	}
	add_filter( 'mce_external_plugins', '\\Sgdg\\Admin\\TinyMCE\\plugin' );
	add_filter( 'mce_buttons', '\\Sgdg\\Admin\\TinyMCE\\buttons' );
	add_filter( 'tiny_mce_before_init', '\\Sgdg\\Admin\\TinyMCE\\localize' );
}

function plugin( $plugin_array ) {
	$plugin_array['sgdg_tinymce_button'] = plugins_url( 'skaut-google-drive-gallery/admin/js/tinymce_plugin.js' );
	return $plugin_array;
}

function buttons( $buttons ) {
	array_push( $buttons, 'sgdg_tinymce_button' );
	return $buttons;
}

function localize( $settings ) {
	$values = array(
		'ajax_url'     => admin_url( 'admin-ajax.php' ),
		'nonce'        => wp_create_nonce( 'sgdg_tinymce_plugin' ),
	);
	$settings['sgdg_localize'] = wp_json_encode($values, JSON_UNESCAPED_UNICODE);
	return $settings;
}

function handle_ajax() {
	check_ajax_referer( 'sgdg_tinymce_plugin' );
	if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) ) {
		return;
	}
	$client = \Sgdg\Frontend\GoogleAPILib\get_drive_client();

	$path = isset( $_GET['path'] ) ? $_GET['path'] : [];
	$ret  = walk_path($client, $path);

	wp_send_json( $ret );
}

function walk_path( $client, array $path, $root = null ) {
	if( ! isset( $root ) ) {
		$rootPath = \Sgdg\Options::$root_path->get();
		$root     = end( $rootPath );
	}
	if( 0 === count( $path ) ) {
		return list_files( $client, $root );
	}
	$page_token = null;
	do {
		$params   = [
			'q'                     => '"' . $root . '" in parents and mimeType = "application/vnd.google-apps.folder" and trashed = false',
			'supportsTeamDrives'    => true,
			'includeTeamDriveItems' => true,
			'pageToken'             => $page_token,
			'pageSize'              => 1000,
			'fields'                => 'nextPageToken, files(id, name)',
		];
		$response = $client->files->listFiles( $params );
		foreach ( $response->getFiles() as $file ) {
			if ( $file->getName() === $path[0] ) {
				array_shift($path);
				return walk_path( $client, $path, $file->getId() );
			}
		}
	} while ( null !== $page_token );
}

function list_files( $client, $root ) {
	$ret        = [];
	$page_token = null;
	do {
		$params   = [
			'q'                     => '"' . $root . '" in parents and mimeType = "application/vnd.google-apps.folder" and trashed = false',
			'supportsTeamDrives'    => true,
			'includeTeamDriveItems' => true,
			'pageToken'             => $page_token,
			'pageSize'              => 1000,
			'fields'                => 'nextPageToken, files(id, name)',
		];
		$response = $client->files->listFiles( $params );
		foreach ( $response->getFiles() as $file ) {
			$ret[] = $file->getName();
		}
		$page_token = $response->getNextPageToken();
	} while ( null !== $page_token );
	return $ret;
}
