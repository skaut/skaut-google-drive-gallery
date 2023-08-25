<?php
/**
 * Contains the Grid class.
 *
 * @package skaut-google-drive-gallery
 */

namespace Sgdg\Admin\Settings_Pages\Advanced;

use Sgdg\Options;
use Sgdg\Script_And_Style_Helpers;

/**
 * Registers and renders the grid settings section.
 *
 * @phan-constructor-used-for-side-effects
 */
final class Grid {

	/**
	 * Register all the hooks for the section.
	 */
	public function __construct() {
		if ( ! is_admin() ) {
			return;
		}

		add_action( 'admin_init', array( self::class, 'add_section' ) );
		add_action( 'admin_enqueue_scripts', array( self::class, 'register_scripts_styles' ) );
	}

	/**
	 * Adds the settings section and all the fields in it.
	 *
	 * @return void
	 */
	public static function add_section() {
		add_settings_section(
			'sgdg_grid',
			esc_html__( 'Image grid', 'skaut-google-drive-gallery' ),
			array( self::class, 'html' ),
			'sgdg_advanced'
		);
		Options::$grid_height->add_field();
		Options::$grid_spacing->add_field();
		Options::$dir_title_size->add_field();
		Options::$dir_counts->add_field();
		Options::$page_size->add_field();
		Options::$page_autoload->add_field();
		Options::$image_ordering->add_field();
		Options::$dir_ordering->add_field();
		Options::$dir_prefix->add_field();
	}

	/**
	 * Enqueues styles for the section.
	 *
	 * @return void
	 */
	public static function register_scripts_styles() {
		Script_And_Style_Helpers::register_and_enqueue_style( 'sgdg_options_grid', 'admin/css/options-grid.min.css' );
	}

	/**
	 * Renders the header for the section.
	 *
	 * @return void
	 */
	public static function html() {
		// No header.
	}
}
