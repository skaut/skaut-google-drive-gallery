<?php
/**
 * Contains the Root_Selection class.
 *
 * @package skaut-google-drive-gallery
 */

namespace Sgdg\Admin\Settings_Pages\Basic;

use Sgdg\Admin\Settings_Pages\Basic\Root_Selection\List_Ajax_Endpoint;
use Sgdg\Options;
use Sgdg\Script_And_Style_Helpers;

require_once __DIR__ . '/root-selection/class-list-ajax-endpoint.php';

/**
 * Registers and renders the root selection settings section.
 *
 * @phan-constructor-used-for-side-effects
 */
final class Root_Selection {

	/**
	 * Register all the hooks for this section.
	 */
	public function __construct() {
		if ( ! is_admin() ) {
			return;
		}

		add_action( 'admin_init', array( self::class, 'add_section' ) );
		add_action( 'admin_enqueue_scripts', array( self::class, 'register_scripts_styles' ) );
		new List_Ajax_Endpoint();
	}

	/**
	 * Adds the settings section and all the fields in it.
	 *
	 * @return void
	 */
	public static function add_section() {
		add_settings_section(
			'sgdg_root_selection',
			esc_html__( 'Step 2: Root directory selection', 'skaut-google-drive-gallery' ),
			array( self::class, 'html' ),
			'sgdg_basic'
		);
		Options::$root_path->register();
	}

	/**
	 * Renders the header for the section.
	 *
	 * @return void
	 */
	public static function html() {
		Options::$root_path->html();
		echo '<table class="widefat sgdg_root_selection">';
		echo '<thead>';
		echo '<tr>';
		echo '<th class="sgdg-root-selection-path"></th>';
		echo '</tr>';
		echo '</thead>';
		echo '<tbody id="sgdg_root_selection_body"></tbody>';
		echo '<tfoot>';
		echo '<tr>';
		echo '<td class="sgdg-root-selection-path"></td>';
		echo '</tr>';
		echo '</tfoot>';
		echo '</table>';
	}

	/**
	 * Enqueues scripts and styles for the section.
	 *
	 * @param string $hook The current admin page.
	 *
	 * @return void
	 */
	public static function register_scripts_styles( $hook ) {
		Script_And_Style_Helpers::register_and_enqueue_style( 'sgdg_options_root', 'admin/css/options-root.min.css' );

		if ( 'toplevel_page_sgdg_basic' !== $hook ) {
			return;
		}

		Script_And_Style_Helpers::register_and_enqueue_script(
			'sgdg_root_selection_ajax',
			'admin/js/root_selection.min.js',
			array( 'jquery' )
		);
		Script_And_Style_Helpers::add_script_configuration(
			'sgdg_root_selection_ajax',
			'sgdgRootpathLocalize',
			array(
				'ajax_url'           => admin_url( 'admin-ajax.php' ),
				'drive_list'         => esc_html__( 'Shared drive list', 'skaut-google-drive-gallery' ),
				'error_header'       => esc_html__(
					'The Image and video gallery from Google Drive plugin has encountered an error. Error message:',
					'skaut-google-drive-gallery'
				),
				'error_trace_header' => esc_html__( 'Stack trace:', 'skaut-google-drive-gallery' ),
				'nonce'              => wp_create_nonce( 'sgdg_root_selection' ),
				'root_dir'           => Options::$root_path->get( array() ),
			)
		);
	}
}
