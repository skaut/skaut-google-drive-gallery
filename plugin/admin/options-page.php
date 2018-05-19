<?php
namespace Sgdg\Admin\OptionsPage;

require_once 'options-page/oauth-grant.php';
require_once 'options-page/oauth-revoke.php';
require_once 'options-page/root-selection.php';
require_once 'options-page/other.php';

if ( ! is_admin() ) {
	return;
}

function register() {
	add_action( 'admin_menu', '\\Sgdg\\Admin\\OptionsPage\\add' );
	add_action( 'admin_init', '\\Sgdg\\Admin\\OptionsPage\\action_handler' );
	if ( ! get_option( 'sgdg_access_token' ) ) {
		OAuthGrant\register();
	} else {
		OAuthRevoke\register();
		RootSelection\register();
		Other\register();
	}
}

function add() {
	add_options_page( __( 'Google drive gallery', 'skaut-google-drive-gallery' ), esc_html__( 'Google drive gallery', 'skaut-google-drive-gallery' ), 'manage_options', 'sgdg', '\\Sgdg\\Admin\\OptionsPage\\html' );
}

function html() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	settings_errors( 'sgdg_messages' );
	echo( '<div class="wrap">' );
	echo( '<h1>' . esc_html( get_admin_page_title() ) . '</h1>' );
	echo( '<form action="options.php" method="post">' );
	settings_fields( 'sgdg' );
	do_settings_sections( 'sgdg' );
	submit_button( esc_html__( 'Save Changes', 'skaut-google-drive-gallery' ) );
	echo( '</form>' );
	echo( '</div>' );
}

function action_handler() {
	if ( isset( $_GET['page'] ) && 'sgdg' === $_GET['page'] && isset( $_GET['action'] ) ) {
		if ( 'oauth_grant' === $_GET['action'] ) {
			\Sgdg\Admin\GoogleAPILib\oauth_grant();
		} elseif ( 'oauth_redirect' === $_GET['action'] ) {
			\Sgdg\Admin\GoogleAPILib\oauth_redirect();
		} elseif ( 'oauth_revoke' === $_GET['action'] && get_option( 'sgdg_access_token' ) ) {
			\Sgdg\Admin\GoogleAPILib\oauth_revoke();
		}
	}
}
