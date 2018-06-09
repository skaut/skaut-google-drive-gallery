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
	add_options_page( __( 'Google Drive gallery', 'skaut-google-drive-gallery' ), esc_html__( 'Google Drive gallery', 'skaut-google-drive-gallery' ), 'manage_options', 'sgdg', '\\Sgdg\\Admin\\OptionsPage\\html' );
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
	// phpcs:ignore WordPress.CSRF.NonceVerification.NoNonceVerification
	if ( isset( $_GET['page'] ) && 'sgdg' === $_GET['page'] ) {
		if ( isset( $_GET['action'] ) ) {
			// phpcs:ignore WordPress.CSRF.NonceVerification.NoNonceVerification
			if ( 'oauth_grant' === $_GET['action'] ) {
				wp_verify_nonce( $_GET['_wpnonce'], 'oauth_grant' );
				\Sgdg\Admin\GoogleAPILib\oauth_grant();
			} elseif ( 'oauth_redirect' === $_GET['action'] ) {
				\Sgdg\Admin\GoogleAPILib\oauth_redirect();
			} elseif ( 'oauth_revoke' === $_GET['action'] && get_option( 'sgdg_access_token' ) ) {
				wp_verify_nonce( $_GET['_wpnonce'], 'oauth_revoke' );
				\Sgdg\Admin\GoogleAPILib\oauth_revoke();
			}
		} elseif ( isset( $_GET['success'] ) ) {
			add_settings_error( 'skaut-google-drive-gallery', 'sgdg-notice', esc_html__( 'Settings saved.', 'skaut-google-drive-gallery' ), 'updated' );
		} elseif ( isset( $_GET['error'] ) ) {
			if ( 'not-enabled' === $_GET['error'] ) {
				// translators: %s: Link to the Google developers console
				add_settings_error( 'skaut-google-drive-gallery', 'sgdg-error', sprintf( esc_html__( 'Google Drive API not enabled. Please enable it at %s and try again after a while.', 'skaut-google-drive-gallery' ), '<a href="https://console.developers.google.com/apis/library/drive.googleapis.com" target="_blank">https://console.developers.google.com/apis/library/drive.googleapis.com</a>' ) );
			} else {
				add_settings_error( 'skaut-google-drive-gallery', 'sgdg-error', esc_html__( 'An unknown error has been encountered: ', 'skaut-google-drive-gallery' ) . $_GET['error'] );
			}
		}
	}
}
