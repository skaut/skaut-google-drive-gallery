<?php
/**
 * Contains all the functions for the settings pages.
 *
 * @package skaut-google-drive-gallery
 */

namespace Sgdg\Admin;

use Sgdg\Admin\Settings_Pages\Advanced_Settings;
use Sgdg\Admin\Settings_Pages\Basic_Settings;
use Sgdg\GET_Helpers;

require_once __DIR__ . '/settings-pages/class-advanced-settings.php';
require_once __DIR__ . '/settings-pages/class-basic-settings.php';

/**
 * Registers and renders the plugin settings pages.
 *
 * @phan-constructor-used-for-side-effects
 */
final class Settings_Pages {

	/**
	 * Basic settings page.
	 *
	 * @var Basic_Settings
	 */
	private $basic;

	/**
	 * Registers the administration pages of the plugin.
	 *
	 * Registers all the hooks all the pages, registers the plugin into the WordPress admin menu and register a handler for OAuth redirect.
	 */
	public function __construct() {
		if ( ! is_admin() ) {
			return;
		}

		add_action( 'admin_menu', array( $this, 'add' ) );
		$this->basic = new Basic_Settings();
		new Advanced_Settings();
		add_action( 'admin_init', array( self::class, 'action_handler' ) );
	}

	/**
	 * Adds the admin menu section.
	 *
	 * @return void
	 */
	public function add() {
		add_menu_page(
			__( 'Google Drive gallery', 'skaut-google-drive-gallery' ),
			esc_html__( 'Google Drive gallery', 'skaut-google-drive-gallery' ),
			'manage_options',
			'sgdg_basic',
			array( get_class( $this->basic ), 'html' ),
			plugins_url( '/skaut-google-drive-gallery/admin/icon.png' )
		);
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

		switch ( GET_Helpers::get_string_variable( 'action' ) ) {
			case 'oauth_grant':
				if ( self::check_nonce( 'oauth_grant' ) ) {
					OAuth_Helpers::grant_redirect();
				}

				break;

			case 'oauth_revoke':
				if ( false !== get_option( 'sgdg_access_token', false ) && self::check_nonce( 'oauth_revoke' ) ) {
					OAuth_Helpers::revoke();
				}

				break;

			case 'oauth_redirect':
				OAuth_Helpers::grant_return();

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
		return 'sgdg_basic' === GET_Helpers::get_string_variable( 'page' ) && isset( $_GET['action'] );
	}

	/**
	 * Checks the presence and validity of a WordPress nonce
	 *
	 * @param string $action The action for which the nonce should be valid.
	 *
	 * @return bool Whether the nonce is valid.
	 */
	private static function check_nonce( $action ) {
		return false !== wp_verify_nonce( GET_Helpers::get_string_variable( '_wpnonce' ), $action );
	}
}
