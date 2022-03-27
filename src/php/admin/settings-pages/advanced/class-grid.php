<?php
/**
 * Contains the Grid class.
 *
 * @package skaut-google-drive-gallery
 */

namespace Sgdg\Admin\Settings_Pages\Advanced;

if ( ! is_admin() ) {
	return;
}

/**
 * Registers and renders the grid settings section.
 */
class Grid {
	/**
	 * Register all the hooks for the section.
	 */
	public function __construct() {
		add_action( 'admin_init', array( self::class, 'add_section' ) );
		add_action( 'admin_enqueue_scripts', array( self::class, 'register_scripts_styles' ) );
	}

	/**
	 * Adds the settings section and all the fields in it.
	 *
	 * @return void
	 */
	public static function add_section() {
		add_settings_section( 'sgdg_grid', esc_html__( 'Image grid', 'skaut-google-drive-gallery' ), array( self::class, 'html' ), 'sgdg_advanced' );
		\Sgdg\Options::$grid_height->add_field();
		\Sgdg\Options::$grid_spacing->add_field();
		\Sgdg\Options::$dir_title_size->add_field();
		\Sgdg\Options::$dir_counts->add_field();
		\Sgdg\Options::$page_size->add_field();
		\Sgdg\Options::$page_autoload->add_field();
		\Sgdg\Options::$image_ordering->add_field();
		\Sgdg\Options::$dir_ordering->add_field();
		\Sgdg\Options::$dir_prefix->add_field();
	}

	/**
	 * Enqueues styles for the section.
	 *
	 * @return void
	 */
	public static function register_scripts_styles() {
		\Sgdg\Script_And_Style_Helpers::register_and_enqueue_style( 'sgdg_options_grid', 'admin/css/options-grid.min.css' );
	}

	/**
	 * Renders the header for the section.
	 *
	 * Currently no-op.
	 *
	 * @return void
	 */
	public static function html() {}
}
