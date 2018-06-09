<?php
namespace Sgdg\Admin\AdminPages\Basic;

require_once 'basic/oauth-grant.php';
require_once 'basic/oauth-revoke.php';
require_once 'basic/root-selection.php';

if ( ! is_admin() ) {
	return;
}

function register() {
	add_action( 'admin_menu', '\\Sgdg\\Admin\\AdminPages\\Basic\\add' );
	if ( ! get_option( 'sgdg_access_token' ) ) {
		OAuthGrant\register();
	} else {
		OAuthRevoke\register();
		RootSelection\register();
	}
}

function add() {
	add_submenu_page( 'sgdg', __( 'Basic options', 'skaut-google-drive-gallery' ), esc_html__( 'Basic options', 'skaut-google-drive-gallery' ), 'manage_options', 'sgdg', '\\Sgdg\\Admin\\AdminPages\\Basic\\html' );
}

function html() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	settings_errors();
	echo( '<div class="wrap">' );
	echo( '<h1>' . esc_html( get_admin_page_title() ) . '</h1>' );
	echo( '<form action="options.php?action=update&option_page=sgdg" method="post">' );
	wp_nonce_field( 'sgdg-options' );
	do_settings_sections( 'sgdg_basic' );
	submit_button( esc_html__( 'Save Changes', 'skaut-google-drive-gallery' ) );
	echo( '</form>' );
	echo( '</div>' );
}
