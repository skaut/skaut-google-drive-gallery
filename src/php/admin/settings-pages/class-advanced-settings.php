<?php
/**
 * Contains the Advanced_Settings class.
 *
 * @package skaut-google-drive-gallery
 */

namespace Sgdg\Admin\Settings_Pages;

use Sgdg\Admin\Settings_Pages\Advanced\Grid;
use Sgdg\Admin\Settings_Pages\Advanced\Lightbox;

require_once __DIR__ . '/advanced/class-grid.php';
require_once __DIR__ . '/advanced/class-lightbox.php';

/**
 * Registers and renders the advanced settings page.
 *
 * @phan-constructor-used-for-side-effects
 */
final class Advanced_Settings {

	/**
	 * Register all the hooks for the page.
	 */
	public function __construct() {
		if ( ! is_admin() ) {
			return;
		}

		add_action( 'admin_menu', array( self::class, 'add_page' ) );
		new Grid();
		new Lightbox();
	}

	/**
	 * Adds the settings page.
	 *
	 * @return void
	 */
	public static function add_page() {
		add_submenu_page(
			'sgdg_basic',
			__( 'Advanced options', 'skaut-google-drive-gallery' ),
			esc_html__( 'Advanced options', 'skaut-google-drive-gallery' ),
			'manage_options',
			'sgdg_advanced',
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
		echo '<form action="options.php?action=update&option_page=sgdg_advanced" method="post">';
		wp_nonce_field( 'sgdg_advanced-options' );
		do_settings_sections( 'sgdg_advanced' );
		submit_button( esc_html__( 'Save Changes', 'skaut-google-drive-gallery' ) );
		echo '</form>';
		echo '</div>';
	}
}
