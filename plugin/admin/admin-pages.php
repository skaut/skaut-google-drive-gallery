<?php
namespace Sgdg\Admin\AdminPages;

require_once 'admin-pages/basic.php';
require_once 'admin-pages/advanced.php';

if ( ! is_admin() ) {
	return;
}

function register() {
	Basic\register();
	add_action( 'admin_menu', '\\Sgdg\\Admin\\AdminPages\\add' );
	Advanced\register();
	add_action( 'admin_init', '\\Sgdg\\Admin\\AdminPages\\action_handler' );
}

function add() {
	add_menu_page( __( 'Google Drive gallery', 'skaut-google-drive-gallery' ), esc_html__( 'Google Drive gallery', 'skaut-google-drive-gallery' ), 'manage_options', 'sgdg_basic', '\\Sgdg\\Admin\\AdminPages\\Basic\\html', plugins_url( '/skaut-google-drive-gallery/admin/icon.png' ) );
}

function action_handler() {
	// phpcs:ignore WordPress.CSRF.NonceVerification.NoNonceVerification
	if ( isset( $_GET['page'] ) && 'sgdg_basic' === $_GET['page'] ) {
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
