<?php
namespace Sgdg\Admin\TinyMCE;

if ( ! is_admin() ) {
	return;
}

function register() {
	add_action( 'admin_head', '\\Sgdg\\Admin\\TinyMCE\\add' );
}

function add() {
	if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) ) {
		return;
	}
	if ( 'true' === get_user_option( 'rich_editing' ) ) {
		add_filter( 'mce_external_plugins', '\\Sgdg\\Admin\\TinyMCE\\plugin' );
		add_filter( 'mce_buttons', '\\Sgdg\\Admin\\TinyMCE\\buttons' );
	}
}

function plugin( $plugin_array ) {
	$plugin_array['sgdg_tinymce_button'] = plugins_url( 'skaut-google-drive-gallery/admin/js/tinymce_plugin.js' );
	return $plugin_array;
}

function buttons( $buttons ) {
	array_push( $buttons, 'sgdg_tinymce_button' );
	return $buttons;
}
