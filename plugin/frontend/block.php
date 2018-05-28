<?php
namespace Sgdg\Frontend\Block;

function register() {
	add_action( 'enqueue_block_editor_assets', '\\Sgdg\\Frontend\\Block\\add' );
}

function add() {
	wp_enqueue_script( 'sgdg_gutenberg', plugins_url( '/skaut-google-drive-gallery/frontend/js/block.js' ), [ 'wp-blocks', 'wp-element' ] );
}
