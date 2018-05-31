<?php
namespace Sgdg\Frontend\Block;

function register() {
	add_action( 'enqueue_block_editor_assets', '\\Sgdg\\Frontend\\Block\\add' );
}

function add() {
	wp_enqueue_script( 'sgdg_block', plugins_url( '/skaut-google-drive-gallery/frontend/js/block.js' ), [ 'wp-blocks', 'wp-element' ] );
	wp_localize_script( 'sgdg_block', 'sgdg_block_localize', [
		'root_name' => esc_html__( 'Google Drive gallery', 'skaut-google-drive-gallery' ),
		'ajax_url'  => admin_url( 'admin-ajax.php' ),
		'nonce'     => wp_create_nonce( 'sgdg_tinymce_plugin' ), // TODO
	]);
}
