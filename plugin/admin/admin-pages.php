<?php
namespace Sgdg\Admin\AdminPages;

if ( ! is_admin() ) {
	return;
}

function register() {
	add_action( 'admin_menu', '\\Sgdg\\Admin\\AdminPages\\add' );
	add_action( 'admin_init', '\\Sgdg\\Admin\\AdminPages\\action_handler' );
	if ( ! get_option( 'sgdg_access_token' ) ) {
		\Sgdg\Admin\OptionsPage\OAuthGrant\register();
	} else {
		\Sgdg\Admin\OptionsPage\OAuthRevoke\register();
		\Sgdg\Admin\OptionsPage\RootSelection\register();
		\Sgdg\Admin\OptionsPage\Other\register();
	}
}

function add() {
	add_menu_page( __( 'Google Drive gallery', 'skaut-google-drive-gallery' ), esc_html__( 'Google Drive gallery', 'skaut-google-drive-gallery' ), 'manage_options', 'sgdg', '\\Sgdg\\Admin\\AdminPages\\basic', plugins_url( '/skaut-google-drive-gallery/admin/icon.png' ) );
	add_submenu_page( 'sgdg', __( 'Basic options', 'skaut-google-drive-gallery' ), esc_html__( 'Basic options', 'skaut-google-drive-gallery' ), 'manage_options', 'sgdg', '\\Sgdg\\Admin\\AdminPages\\basic' );
	add_submenu_page( 'sgdg', __( 'Advanced options', 'skaut-google-drive-gallery' ), esc_html__( 'Advanced options', 'skaut-google-drive-gallery' ), 'manage_options', 'sgdg_advanced', '\\Sgdg\\Admin\\AdminPages\\advanced' );
}

function basic() {
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

function advanced() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	settings_errors();
	echo( '<div class="wrap">' );
	echo( '<h1>' . esc_html( get_admin_page_title() ) . '</h1>' );
	echo( '<form action="options.php?action=update&option_page=sgdg" method="post">' );
	wp_nonce_field( 'sgdg-options' );
	do_settings_sections( 'sgdg_advanced' );
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
				// translators: %s is a link to the Google developers console.
				add_settings_error( 'skaut-google-drive-gallery', 'sgdg-error', sprintf( esc_html__( 'Google Drive API not enabled. Please enable it at %s and try again after a while.', 'skaut-google-drive-gallery' ), '<a href="https://console.developers.google.com/apis/library/drive.googleapis.com" target="_blank">https://console.developers.google.com/apis/library/drive.googleapis.com</a>' ) );
			} else {
				add_settings_error( 'skaut-google-drive-gallery', 'sgdg-error', esc_html__( 'An unknown error has been encountered: ', 'skaut-google-drive-gallery' ) . $_GET['error'] );
			}
		}
	}
}
