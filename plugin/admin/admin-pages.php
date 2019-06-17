<?php
/**
 * Contains all the functions for the settings pages.
 *
 * @package skaut-google-drive-gallery
 */

namespace Sgdg\Admin\AdminPages;

require_once __DIR__ . '/admin-pages/basic.php';
require_once __DIR__ . '/admin-pages/advanced.php';

if ( ! is_admin() ) {
	return;
}

/**
 * Register the administration pages of the plugin.
 *
 * Registers all the hooks all the pages, registers the plugin into the WordPress admin menu and register a handler for OAuth redirect.
 */
function register() {
	Basic\register();
	add_action( 'admin_menu', '\\Sgdg\\Admin\\AdminPages\\add' );
	Advanced\register();
	add_action( 'admin_init', '\\Sgdg\\Admin\\AdminPages\\action_handler' );
}

/**
 * Adds the admin menu section.
 */
function add() {
	add_menu_page( __( 'Google Drive gallery', 'skaut-google-drive-gallery' ), esc_html__( 'Google Drive gallery', 'skaut-google-drive-gallery' ), 'manage_options', 'sgdg_basic', '\\Sgdg\\Admin\\AdminPages\\Basic\\html', plugins_url( '/skaut-google-drive-gallery/admin/icon.png' ) );
}

/**
 * Handles OAuth redirects.
 */
function action_handler() {
	// phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification
	if ( isset( $_GET['page'] ) && 'sgdg_basic' === $_GET['page'] && isset( $_GET['action'] ) ) {
		// phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification
		if ( 'oauth_grant' === $_GET['action'] && isset( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'oauth_grant' ) ) {
			\Sgdg\Admin\GoogleAPILib\oauth_grant();
		} elseif ( 'oauth_redirect' === $_GET['action'] ) {
			\Sgdg\Admin\GoogleAPILib\oauth_redirect();
		} elseif ( 'oauth_revoke' === $_GET['action'] && false !== get_option( 'sgdg_access_token', false ) && isset( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'oauth_revoke' ) ) {
			\Sgdg\Admin\GoogleAPILib\oauth_revoke();
		}
	}
}
