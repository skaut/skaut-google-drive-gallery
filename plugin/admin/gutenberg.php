<?php
namespace Sgdg\Admin\Gutenberg;

if ( ! is_admin() ) {
	return;
}

function register() {
	add_action( 'enqueue_block_editor_assets', '\\Sgdg\\Admin\\Gutenberg\\add_editor' );
}

function add_editor() {
	wp_enqueue_script( 'sgdg_gutenberg', plugins_url( '/skaut-google-drive-gallery/admin/js/gutenberg.js' ), ['wp-blocks', 'wp-element'] );
}
