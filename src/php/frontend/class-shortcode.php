<?php
/**
 * Contains the Shortcode class.
 *
 * @package skaut-google-drive-gallery
 */

namespace Sgdg\Frontend;

use Exception as Base_Exception;
use Sgdg\API_Client;
use Sgdg\API_Facade;
use Sgdg\Exceptions\Directory_Not_Found_Exception;
use Sgdg\Exceptions\Exception as Sgdg_Exception;
use Sgdg\Exceptions\Root_Not_Found_Exception;
use Sgdg\Frontend\Options_Proxy;
use Sgdg\Helpers;
use Sgdg\Options;
use Sgdg\Script_And_Style_Helpers;
use Sgdg\Vendor\GuzzleHttp\Promise\PromiseInterface;
use Sgdg\Vendor\GuzzleHttp\Promise\RejectedPromise;
use const DAY_IN_SECONDS;

/**
 * Contains all the functions for the shortcode the plugin provides
 *
 * @phan-constructor-used-for-side-effects
 */
final class Shortcode {

	/**
	 * Registers all the hooks for the shortcode.
	 */
	public function __construct() {
		add_action( 'init', array( self::class, 'add' ) );
		add_action( 'wp_enqueue_scripts', array( self::class, 'register_scripts_styles' ) );
	}

	/**
	 * Adds the shortcode to WordPress.
	 *
	 * @return void
	 */
	public static function add() {
		add_shortcode( 'sgdg', array( self::class, 'render' ) );
	}

	/**
	 * Registers all the scripts and styles used by the shortcode.
	 *
	 * @return void
	 */
	public static function register_scripts_styles() {
		Script_And_Style_Helpers::register_script(
			'sgdg_gallery_init',
			'frontend/js/shortcode.min.js',
			array( 'jquery' )
		);
		Script_And_Style_Helpers::register_style( 'sgdg_gallery_css', 'frontend/css/shortcode.min.css' );

		Script_And_Style_Helpers::register_script(
			'sgdg_imagelightbox_script',
			'bundled/imagelightbox.min.js',
			array( 'jquery' )
		);
		Script_And_Style_Helpers::register_style( 'sgdg_imagelightbox_style', 'bundled/imagelightbox.min.css' );
		Script_And_Style_Helpers::register_script(
			'sgdg_imagesloaded',
			'bundled/imagesloaded.pkgd.min.js',
			array( 'jquery' )
		);
		Script_And_Style_Helpers::register_script( 'sgdg_justified-layout', 'bundled/justified-layout.min.js' );
	}

	/**
	 * Renders the shortcode.
	 *
	 * This function is a wrapper around the `html()` function which converts a slash-delimited path into an array
	 *
	 * @see html()
	 * @see \Sgdg\Frontend\Options_Proxy
	 *
	 * @param array<string, mixed> $atts A list of option overrides, as documented in the Options_Proxy class plus the `path` attribute, which is a slash-delimited string.
	 *
	 * @return string The HTML code for the shortcode.
	 */
	public static function render( $atts ) {
		if ( isset( $atts['path'] ) && '' !== $atts['path'] ) {
			$atts['path'] = explode( '/', trim( $atts['path'], " /\t\n\r\0\x0B" ) );
		}

		try {
			return self::html( $atts );
		} catch ( Sgdg_Exception $e ) {
			return '<div class="sgdg-gallery-container">' . $e->getMessage() . '</div>';
		} catch ( Base_Exception $e ) {
			if ( Helpers::is_debug_display() ) {
				return '<div class="sgdg-gallery-container">' . $e->getMessage() . '</div>';
			}

			return '<div class="sgdg-gallery-container">' .
				esc_html__( 'Unknown error.', 'skaut-google-drive-gallery' ) .
				'</div>';
		}
	}

	/**
	 * Turns the shorcode into HTML.
	 *
	 * @see \Sgdg\Frontend\Options_Proxy
	 *
	 * @param array<string, mixed> $atts A list of option overrides, as documented in the Options_Proxy class plus the `path` attribute, which is an array of directory names.
	 *
	 * @return string The HTML code for the block.
	 */
	public static function html( $atts ) {
		wp_enqueue_script( 'sgdg_imagelightbox_script' );
		wp_enqueue_style( 'sgdg_imagelightbox_style' );
		wp_enqueue_script( 'sgdg_imagesloaded' );
		wp_enqueue_script( 'sgdg_justified-layout' );

		$options = new Options_Proxy( $atts );

		wp_enqueue_script( 'sgdg_gallery_init' );
		Script_And_Style_Helpers::add_script_configuration(
			'sgdg_gallery_init',
			'sgdgShortcodeLocalize',
			array(
				'ajax_url'            => admin_url( 'admin-ajax.php' ),
				'breadcrumbs_top'     => esc_html__( 'Gallery', 'skaut-google-drive-gallery' ),
				'empty_gallery'       => esc_html__( 'The gallery is empty.', 'skaut-google-drive-gallery' ),
				'grid_height'         => $options->get( 'grid_height' ),
				'grid_spacing'        => $options->get( 'grid_spacing' ),
				'load_more'           => esc_html__( 'Load more', 'skaut-google-drive-gallery' ),
				'page_autoload'       => $options->get( 'page_autoload' ),
				'preview_activity'    => $options->get( 'preview_activity_indicator' ),
				'preview_arrows'      => $options->get( 'preview_arrows' ),
				'preview_captions'    => $options->get( 'preview_captions' ),
				'preview_closebutton' => $options->get( 'preview_close_button' ),
				'preview_quitOnEnd'   => 'true' === $options->get( 'preview_loop' ) ? 'false' : 'true',
				'preview_speed'       => $options->get( 'preview_speed' ),
			)
		);
		wp_enqueue_style( 'sgdg_gallery_css' );
		wp_add_inline_style(
			'sgdg_gallery_css',
			'.sgdg-dir-name {font-size: ' . $options->get( 'dir_title_size' ) . ';}'
		);

		$root_path = Options::$root_path->get();
		$root      = end( $root_path );

		if ( isset( $atts['path'] ) && '' !== $atts['path'] && count( $atts['path'] ) > 0 ) {
			$root_promise = self::find_dir( $root, $atts['path'] );
			$root         = API_Client::execute( array( $root_promise ) )[0];
		}

		$hash = hash( 'sha256', $root );
		set_transient(
			'sgdg_hash_' . $hash,
			array(
				'overriden' => $options->export_overriden(),
				'root'      => $root,
			),
			DAY_IN_SECONDS
		);

		return '<div class="sgdg-gallery-container" data-sgdg-hash="' .
			$hash .
			'"><div class="sgdg-loading"><div></div></div></div>';
	}

	/**
	 * Finds the ID of a the last directory in `$path` starting from `$root`.
	 *
	 * @param string        $root The ID of the root directory of the path.
	 * @param array<string> $path An array of directory names forming a path starting from $root and ending with the directory whose ID is to be returned.
	 *
	 * @return PromiseInterface The ID of the directory.
	 */
	private static function find_dir( $root, array $path ) {
		return API_Facade::get_directory_id( $root, $path[0] )->then(
			static function ( $next_dir_id ) use ( $path ) {
				if ( 1 === count( $path ) ) {
					return $next_dir_id;
				}

				array_shift( $path );

				return self::find_dir( $next_dir_id, $path );
			},
			static function ( $exception ) {
				if ( $exception instanceof Directory_Not_Found_Exception ) {
					return new RejectedPromise(
						new Root_Not_Found_Exception()
					);
				}

				return new RejectedPromise( $exception );
			}
		);
	}
}
