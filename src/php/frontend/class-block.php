<?php
/**
 * Contains the Block class.
 *
 * @package skaut-google-drive-gallery
 */

namespace Sgdg\Frontend;

use Exception as Base_Exception;
use Sgdg\Exceptions\Exception as Sgdg_Exception;
use Sgdg\Frontend\Options_Proxy;
use Sgdg\Frontend\Shortcode;
use Sgdg\Helpers;
use Sgdg\Script_And_Style_Helpers;

/**
 * Adds a gallery block to the Gutenberg editor.
 *
 * @phan-constructor-used-for-side-effects
 */
final class Block {

	/**
	 * Registers all the hooks for the block if the system supports blocks (WP >= 5)
	 *
	 * @return void
	 */
	public function __construct() {
		if ( function_exists( 'register_block_type' ) ) {
			add_action( 'init', array( self::class, 'add' ) );
		}
	}

	/**
	 * Adds the block
	 *
	 * This function registers the Gutenberg block and enqueues all the scripts and style it uses.
	 *
	 * @return void
	 */
	public static function add() {
		Script_And_Style_Helpers::register_script(
			'sgdg_block',
			'frontend/js/block.min.js',
			array( 'wp-blocks', 'wp-components', 'wp-editor', 'wp-element' )
		);

		$options             = new Options_Proxy();
		$get_option          = static function ( $name ) use ( $options ) {
			return array(
				'default' => strval( $options->get( $name ) ),
				'name'    => strval( $options->get_title( $name ) ),
			);
		};
		$get_ordering_option = static function ( $name ) use ( $options ) {
			return array(
				'default_by'    => strval( $options->get_by( $name ) ),
				'default_order' => strval( $options->get_order( $name ) ),
				'name'          => strval( $options->get_title( $name ) ),
			);
		};

		Script_And_Style_Helpers::add_script_configuration(
			'sgdg_block',
			'sgdgBlockLocalize',
			array(
				'ajax_url'                   => admin_url( 'admin-ajax.php' ),
				'block_description'          => esc_html__(
					'A WordPress gallery using Google Drive as file storage',
					'skaut-google-drive-gallery'
				),
				'block_name'                 => esc_html__( 'Google Drive gallery', 'skaut-google-drive-gallery' ),
				'dir_counts'                 => $get_option( 'dir_counts' ),
				'dir_ordering'               => $get_ordering_option( 'dir_ordering' ),
				'grid_height'                => $get_option( 'grid_height' ),
				'grid_section_name'          => esc_html__( 'Image grid', 'skaut-google-drive-gallery' ),
				'grid_spacing'               => $get_option( 'grid_spacing' ),
				'image_ordering'             => $get_ordering_option( 'image_ordering' ),
				'lightbox_section_name'      => esc_html__( 'Image popup', 'skaut-google-drive-gallery' ),
				'nonce'                      => wp_create_nonce( 'sgdg_editor_plugin' ),
				'ordering_option_ascending'  => esc_html__( 'Ascending', 'skaut-google-drive-gallery' ),
				'ordering_option_by_name'    => esc_html__( 'By name', 'skaut-google-drive-gallery' ),
				'ordering_option_by_time'    => esc_html__( 'By time', 'skaut-google-drive-gallery' ),
				'ordering_option_descending' => esc_html__( 'Descending', 'skaut-google-drive-gallery' ),
				'page_autoload'              => $get_option( 'page_autoload' ),
				'page_size'                  => $get_option( 'page_size' ),
				'preview_loop'               => $get_option( 'preview_loop' ),
				'preview_size'               => $get_option( 'preview_size' ),
				'root_name'                  => esc_html__( 'Google Drive gallery', 'skaut-google-drive-gallery' ),
				'settings_override'          => esc_html__( 'Settings override', 'skaut-google-drive-gallery' ),
			)
		);
		Script_And_Style_Helpers::register_and_enqueue_style( 'sgdg_block', 'frontend/css/block.min.css' );
		register_block_type(
			'skaut-google-drive-gallery/gallery',
			array(
				'editor_script'   => 'sgdg_block',
				'render_callback' => array( self::class, 'html' ),
			)
		);
	}

	/**
	 * Renders the block (in frontend)
	 *
	 * @see \Sgdg\Frontend\Options_Proxy
	 *
	 * @param array{path: array<string>, grid_height?: int, grid_spacing?: int, dir_title_size?: string, dir_counts?: string, page_size?: int, page_autoload?: string, dir_prefix?: string, preview_size?: int, preview_speed?: int, preview_arrows?: string, preview_close_button?: string, preview_loop?: string, preview_activity_indicator?: string, preview_captions?: string, image_ordering_by?: string, image_ordering_order?: string, dir_ordering_by?: string, dir_ordering_order?: string} $attributes A list of option overrides, as documented in the Options_Proxy class plus the `path` attribute, which is an array of directory names.
	 *
	 * @return string The HTML code for the block.
	 */
	public static function html( $attributes ) {
		try {
			return Shortcode::html( $attributes );
		} catch ( Sgdg_Exception $e ) {
			return '<div class="sgdg-gallery-container">' . $e->getMessage() . '</div>';
			// @phpstan-ignore catch.neverThrown (Here for safety, even though it should never actually be thrownvariable.undefined)
		} catch ( Base_Exception $e ) {
			if ( Helpers::is_debug_display() ) {
				return '<div class="sgdg-gallery-container">' . $e->getMessage() . '</div>';
			}

			return '<div class="sgdg-gallery-container">' .
				esc_html__( 'Unknown error.', 'skaut-google-drive-gallery' ) .
				'</div>';
		}
	}
}
