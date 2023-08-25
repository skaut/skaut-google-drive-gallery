<?php
/**
 * Contains the Main class.
 *
 * @package skaut-google-drive-gallery
 */

namespace Sgdg;

use Sgdg\Admin\Settings_Pages;
use Sgdg\Admin\TinyMCE_Plugin;
use Sgdg\Frontend\Block;
use Sgdg\Frontend\Gallery;
use Sgdg\Frontend\Page;
use Sgdg\Frontend\Shortcode;
use Sgdg\Frontend\Video_Proxy;

/**
 * Main plugin class.
 *
 * @phan-constructor-used-for-side-effects
 */
final class Main {

	/**
	 * Initializes the plugin
	 */
	public function __construct() {
		register_activation_hook( __FILE__, array( self::class, 'activate' ) );
		add_action( 'plugins_loaded', array( '\\Sgdg\\Options', 'init' ) );
		add_action( 'admin_notices', array( self::class, 'activation_notice' ) );
		new Shortcode();
		new Block();
		new Page();
		new Gallery();
		new Video_Proxy();
		new Settings_Pages();
		new TinyMCE_Plugin();
	}

	/**
	 * Plugin activation function
	 *
	 * This function is called on plugin activation (i.e. usually once right after the user has installed the plugin). It checks whether the version of PHP and WP is sufficient and deactivates the plugin if they aren't.
	 *
	 * @return void
	 */
	public static function activate() {
		if ( ! isset( $GLOBALS['wp_version'] ) || version_compare( $GLOBALS['wp_version'], '4.9.6', '<' ) ) {
			deactivate_plugins( plugin_basename( __FILE__ ) );
			wp_die(
				esc_html__( 'Google Drive gallery requires at least WordPress 4.9.6', 'skaut-google-drive-gallery' )
			);
		}

		if ( version_compare( phpversion(), '5.6', '<' ) ) {
			deactivate_plugins( plugin_basename( __FILE__ ) );
			wp_die( esc_html__( 'Google Drive gallery requires at least PHP 5.6', 'skaut-google-drive-gallery' ) );
		}

		set_transient( 'sgdg_activation_notice', true, 30 );
	}

	/**
	 * Renders the post-activation notice
	 *
	 * This function is called after the plugin has been successfully activated and points the user to the docs.
	 *
	 * @return void
	 */
	public static function activation_notice() {
		if ( false === get_transient( 'sgdg_activation_notice' ) ) {
			return;
		}

		echo '<div class="notice notice-info is-dismissible"><p>';
		$help_link = 'https://napoveda.skaut.cz/dobryweb/' .
			substr( get_locale(), 0, 2 ) .
			'-skaut-google-drive-gallery';
		printf(
			/* translators: 1: Start of a link to the settings 2: End of the link to the settings 3: Start of a help link 4: End of the help link */
			esc_html__(
				// phpcs:ignore SlevomatCodingStandard.Files.LineLength.LineTooLong
				'Google Drive gallery needs to be %1$sconfigured%2$s before it can be used. See the %3$sdocumentation%4$s for more information.',
				'skaut-google-drive-gallery'
			),
			'<a href="' . esc_url( admin_url( 'admin.php?page=sgdg_basic' ) ) . '">',
			'</a>',
			'<a href="' . esc_url( $help_link ) . '" target="_blank">',
			'</a>'
		);
		echo '</p></div>';
		delete_transient( 'sgdg_activation_notice' );
	}
}
