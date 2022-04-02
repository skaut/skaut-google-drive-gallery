<?php
/**
 * Contains the Root_Selection class.
 *
 * @package skaut-google-drive-gallery
 */

namespace Sgdg\Admin\Settings_Pages\Basic;

/**
 * Registers and renders the root selection settings section.
 *
 * @phan-constructor-used-for-side-effects
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Root_Selection {
	/**
	 * Register all the hooks for this section.
	 */
	public function __construct() {
		if ( ! is_admin() ) {
			return;
		}

		add_action( 'admin_init', array( self::class, 'add_section' ) );
		add_action( 'admin_enqueue_scripts', array( self::class, 'register_scripts_styles' ) );
		add_action( 'wp_ajax_list_gdrive_dir', array( self::class, 'handle_ajax' ) );
	}

	/**
	 * Adds the settings section and all the fields in it.
	 *
	 * @return void
	 */
	public static function add_section() {
		add_settings_section( 'sgdg_root_selection', esc_html__( 'Step 2: Root directory selection', 'skaut-google-drive-gallery' ), array( self::class, 'html' ), 'sgdg_basic' );
		\Sgdg\Options::$root_path->register();
	}

	/**
	 * Renders the header for the section.
	 *
	 * @return void
	 */
	public static function html() {
		\Sgdg\Options::$root_path->html();
		echo( '<table class="widefat sgdg_root_selection">' );
		echo( '<thead>' );
		echo( '<tr>' );
		echo( '<th class="sgdg-root-selection-path"></th>' );
		echo( '</tr>' );
		echo( '</thead>' );
		echo( '<tbody id="sgdg_root_selection_body"></tbody>' );
		echo( '<tfoot>' );
		echo( '<tr>' );
		echo( '<td class="sgdg-root-selection-path"></td>' );
		echo( '</tr>' );
		echo( '</tfoot>' );
		echo( '</table>' );
	}

	/**
	 * Enqueues scripts and styles for the section.
	 *
	 * @param string $hook The current admin page.
	 *
	 * @return void
	 */
	public static function register_scripts_styles( $hook ) {
		\Sgdg\Script_And_Style_Helpers::register_and_enqueue_style( 'sgdg_options_root', 'admin/css/options-root.min.css' );
		if ( 'toplevel_page_sgdg_basic' === $hook ) {
			\Sgdg\Script_And_Style_Helpers::register_and_enqueue_script( 'sgdg_root_selection_ajax', 'admin/js/root_selection.min.js', array( 'jquery' ) );
			wp_localize_script(
				'sgdg_root_selection_ajax',
				'sgdgRootpathLocalize',
				array(
					'ajax_url'   => admin_url( 'admin-ajax.php' ),
					'nonce'      => wp_create_nonce( 'sgdg_root_selection' ),
					'root_dir'   => \Sgdg\Options::$root_path->get( array() ),
					'drive_list' => esc_html__( 'Shared drive list', 'skaut-google-drive-gallery' ),
				)
			);
		}
	}

	/**
	 * Ajax call handler wrapper.
	 *
	 * This funtion is a wrapper for `ajax_handler_body()`. This function handles exceptions and returns them in a meaningful form.
	 *
	 * @return void
	 *
	 * @see ajax_handler_body()
	 */
	public static function handle_ajax() {
		try {
			self::ajax_handler_body();
		} catch ( \Sgdg\Exceptions\Exception $e ) {
			wp_send_json( array( 'error' => $e->getMessage() ) );
		} catch ( \Exception $e ) { // @phpstan-ignore-line
			if ( \Sgdg\Main::is_debug_display() ) {
				wp_send_json( array( 'error' => $e->getMessage() ) );
			}
			wp_send_json( array( 'error' => esc_html__( 'Unknown error.', 'skaut-google-drive-gallery' ) ) );
		}
	}

	/**
	 * Handles ajax requests for the root selector.
	 *
	 * Returns a list of all subdirectories of a directory, or a list of all drives if a directory is not provided. Additionaly, returns all the directory names for the current path.
	 *
	 * @return void
	 *
	 * @throws \Sgdg\Exceptions\Cant_Manage_Exception Insufficient role.
	 */
	private static function ajax_handler_body() {
		check_ajax_referer( 'sgdg_root_selection' );
		if ( ! current_user_can( 'manage_options' ) ) {
			throw new \Sgdg\Exceptions\Cant_Manage_Exception();
		}

		$path_ids = \Sgdg\GET_Helpers::get_array_variable( 'path' );
		\Sgdg\API_Client::preamble();

		$promise = \Sgdg\Vendor\GuzzleHttp\Promise\Utils::all(
			array(
				'path_ids' => $path_ids,
				'path'     => self::path_ids_to_names( $path_ids ),
			)
		)->then(
			null,
			static function ( $e ) {
				if ( $e instanceof \Sgdg\Exceptions\File_Not_Found_Exception || $e instanceof \Sgdg\Exceptions\Drive_Not_Found_Exception ) {
					return array(
						'path_ids'  => array(),
						'path'      => array(),
						'resetWarn' => esc_html__( 'Root directory wasn\'t found. The plugin may be broken until a new one is chosen.', 'skaut-google-drive-gallery' ),
					);
				} else {
					return new \Sgdg\Vendor\GuzzleHttp\Promise\RejectedPromise( $e );
				}
			}
		)->then(
			static function( $ret ) {
				$path_ids = $ret['path_ids'];
				unset( $ret['path_ids'] );
				$ret['directories'] =
					count( $path_ids ) === 0
					? self::list_drives()
					: \Sgdg\API_Facade::list_directories( end( $path_ids ), new \Sgdg\Frontend\API_Fields( array( 'id', 'name' ) ) );
				return \Sgdg\Vendor\GuzzleHttp\Promise\Utils::all( $ret );
			}
		)->then(
			static function( $ret ) {
				wp_send_json( $ret );
			}
		);
		\Sgdg\API_Client::execute( array( $promise ) );
	}

	/**
	 * Converts an array of directory IDs to directory names.
	 *
	 * @param array<string> $path An array of Gooogle Drive directory IDs.
	 *
	 * @return \Sgdg\Vendor\GuzzleHttp\Promise\PromiseInterface An array of directory names.
	 */
	private static function path_ids_to_names( $path ) {
		$promises = array();
		if ( count( $path ) > 0 ) {
			if ( 'root' === $path[0] ) {
				$promises[] = new \Sgdg\Vendor\GuzzleHttp\Promise\FulfilledPromise( esc_html__( 'My Drive', 'skaut-google-drive-gallery' ) );
			} else {
				$promises[] = \Sgdg\API_Facade::get_drive_name( $path[0] );
			}
		}
		foreach ( array_slice( $path, 1 ) as $path_element ) {
			$promises[] = \Sgdg\API_Facade::get_file_name( $path_element );
		}
		return \Sgdg\Vendor\GuzzleHttp\Promise\Utils::all( $promises );
	}

	/**
	 * Lists all the drives for a user.
	 *
	 * Returns a list of all Shared drives plus "My Drive".
	 *
	 * @return \Sgdg\Vendor\GuzzleHttp\Promise\PromiseInterface An array of drive records in the format `['name' => '', 'id' => '']`
	 */
	private static function list_drives() {
		return \Sgdg\API_Facade::list_drives()->then(
			static function( $drives ) {
				return array_merge(
					array(
						array(
							'name' => esc_html__( 'My Drive', 'skaut-google-drive-gallery' ),
							'id'   => 'root',
						),
					),
					$drives
				);
			}
		);
	}
}
