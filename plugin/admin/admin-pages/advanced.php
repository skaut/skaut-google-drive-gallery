<?php
namespace Sgdg\Admin\AdminPages\Advanced;

require_once 'advanced/grid.php';
require_once 'advanced/other.php';

if ( ! is_admin() ) {
	return;
}

function register() {
	add_action( 'admin_menu', '\\Sgdg\\Admin\\AdminPages\\Advanced\\add' );
	Grid\register();
	Other\register();
}

function add() {
	add_submenu_page( 'sgdg_basic', __( 'Advanced options', 'skaut-google-drive-gallery' ), esc_html__( 'Advanced options', 'skaut-google-drive-gallery' ), 'manage_options', 'sgdg_advanced', '\\Sgdg\\Admin\\AdminPages\\Advanced\\html' );
}

function html() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	settings_errors();
	echo( '<div class="wrap">' );
	echo( '<h1>' . esc_html( get_admin_page_title() ) . '</h1>' );
	echo( '<form action="options.php?action=update&option_page=sgdg_advanced" method="post">' );
	wp_nonce_field( 'sgdg_advanced-options' );
	do_settings_sections( 'sgdg_advanced' );
	submit_button( esc_html__( 'Save Changes', 'skaut-google-drive-gallery' ) );
	echo( '</form>' );
	echo( '</div>' );
}
