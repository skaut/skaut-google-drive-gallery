<?php
/**
 * Contains the Basic_Settings class.
 *
 * @package skaut-google-drive-gallery
 */

namespace Sgdg\Admin\Settings_Pages;

use Sgdg\Admin\Settings_Pages\Basic\OAuth_Grant;
use Sgdg\Admin\Settings_Pages\Basic\OAuth_Revoke;
use Sgdg\Admin\Settings_Pages\Basic\Root_Selection;

require_once __DIR__ . '/basic/class-oauth-grant.php';
require_once __DIR__ . '/basic/class-oauth-revoke.php';
require_once __DIR__ . '/basic/class-root-selection.php';

/**
 * Registers and renders the basic settings page.
 */
final class Basic_Settings {

	/**
	 * Register all the hooks for the page.
	 */
	public function __construct() {
		if ( ! is_admin() ) {
			return;
		}

		add_action( 'admin_menu', array( self::class, 'add_page' ) );

		if ( false === get_option( 'sgdg_access_token', false ) ) {
			new OAuth_Grant();
		} else {
			new OAuth_Revoke();
			new Root_Selection();
		}
	}

	/**
	 * Adds the settings page to administration.
	 *
	 * @return void
	 */
	public static function add_page() {
		add_submenu_page(
			'sgdg_basic',
			__( 'Basic options', 'skaut-google-drive-gallery' ),
			esc_html__( 'Basic options', 'skaut-google-drive-gallery' ),
			'manage_options',
			'sgdg_basic',
			array( self::class, 'html' )
		);
	}

	/**
	 * Renders the settings page.
	 *
	 * @return void
	 */
	public static function html() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$help_link = 'https://napoveda.skaut.cz/dobryweb/' .
			substr( get_locale(), 0, 2 ) .
			'-skaut-google-drive-gallery';
		add_settings_error(
			'general',
			'help',
			sprintf(
				/* translators: 1: Start of a help link 2: End of the help link */
				esc_html__(
					'See the %1$sdocumentation%2$s for more information about how to configure the plugin.',
					'skaut-google-drive-gallery'
				),
				'<a href="' . esc_url( $help_link ) . '" target="_blank">',
				'</a>'
			),
			'notice-info'
		);

		settings_errors();
		echo '<div class="wrap">';
		echo '<h1>' . esc_html( get_admin_page_title() ) . '</h1>';
		echo '<form action="options.php?action=update&option_page=sgdg_basic" method="post">';
		wp_nonce_field( 'sgdg_basic-options' );
		do_settings_sections( 'sgdg_basic' );
		submit_button( esc_html__( 'Save Changes', 'skaut-google-drive-gallery' ) );
		echo '</form>';
		echo '</div>';
	}
}
