<?php
namespace Sgdg\Admin\AdminPages;

if ( ! is_admin() ) {
	return;
}

function register() {
	add_action( 'admin_menu', '\\Sgdg\\Admin\\AdminPages\\add' );
}

function add() {
	add_menu_page( __( 'Google Drive gallery', 'skaut-google-drive-gallery' ), esc_html__( 'Google Drive gallery', 'skaut-google-drive-gallery' ), 'manage_options', 'sgdg', '\\Sgdg\\Admin\\AdminPages\\basic', plugins_url( '/skaut-google-drive-gallery/admin/icon.png' ) );
	add_submenu_page( 'sgdg', __( 'Basic options', 'skaut-google-drive-gallery' ), esc_html__( 'Basic options', 'skaut-google-drive-gallery' ), 'manage_options', 'sgdg', '\\Sgdg\\Admin\\AdminPages\\basic' );
	add_submenu_page( 'sgdg', __( 'Advanced options', 'skaut-google-drive-gallery' ), esc_html__( 'Advanced options', 'skaut-google-drive-gallery' ), 'manage_options', 'sgdg_advanced', '\\Sgdg\\Admin\\AdminPages\\advanced' );
}

function basic() {
}

function advanced() {
}
