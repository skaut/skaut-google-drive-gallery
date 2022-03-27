<?php
/**
 * Contains all the functions for the settings pages.
 *
 * @package skaut-google-drive-gallery
 */

namespace Sgdg\Admin;

require_once __DIR__ . '/settings-pages/class-basic-settings.php';
require_once __DIR__ . '/admin-pages/advanced.php';

if ( ! is_admin() ) {
	return;
}

/**
 * Registers and renders the plugin settings pages.
 */
class Settings_Pages {
	/**
	 * Basic settings page.
	 *
	 * @var Settings_Pages\Basic_Settings
	 */
	private $basic;

	/**
	 * Registers the administration pages of the plugin.
	 *
	 * Registers all the hooks all the pages, registers the plugin into the WordPress admin menu and register a handler for OAuth redirect.
	 *
	 * @return void
	 */
	public function __construct() {
		$this->basic = new \Sgdg\Admin\Settings_Pages\Basic_Settings();
		add_action( 'admin_menu', array( $this, 'add' ) );
		AdminPages\Advanced\register();
		add_action( 'admin_init', array( self::class, 'action_handler' ) );
	}

	/**
	 * Adds the admin menu section.
	 *
	 * @return void
	 */
	public function add() {
		add_menu_page( __( 'Google Drive gallery', 'skaut-google-drive-gallery' ), esc_html__( 'Google Drive gallery', 'skaut-google-drive-gallery' ), 'manage_options', 'sgdg_basic', array( $this->basic::class, 'html' ), plugins_url( '/skaut-google-drive-gallery/admin/icon.png' ) );
	}

	/**
	 * Handles OAuth redirects.
	 *
	 * @return void
	 */
	public static function action_handler() {
		if ( ! self::check_action_handler_context() ) {
			return;
		}
		switch ( \Sgdg\safe_get_string_variable( 'action' ) ) {
			case 'oauth_grant':
				if ( self::check_nonce( 'oauth_grant' ) ) {
					\Sgdg\Admin\GoogleAPILib\oauth_grant();
				}
				break;
			case 'oauth_revoke':
				if ( false !== get_option( 'sgdg_access_token', false ) && self::check_nonce( 'oauth_revoke' ) ) {
					\Sgdg\Admin\GoogleAPILib\oauth_revoke();
				}
				break;
			case 'oauth_redirect':
				\Sgdg\Admin\GoogleAPILib\oauth_redirect();
				break;
		}
	}

	/**
	 * Verifies the correct context for the action handler.
	 *
	 * @see action_handler
	 *
	 * @return bool Whether the context is valid.
	 */
	private static function check_action_handler_context() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return 'sgdg_basic' === \Sgdg\safe_get_string_variable( 'page' ) && isset( $_GET['action'] );
	}

	/**
	 * Checks the presence and validity of a WordPress nonce
	 *
	 * @param string $action The action for which the nonce should be valid.
	 *
	 * @return bool Whether the nonce is valid.
	 */
	private static function check_nonce( $action ) {
		return false !== wp_verify_nonce( \Sgdg\safe_get_string_variable( '_wpnonce' ), $action );
	}
}
