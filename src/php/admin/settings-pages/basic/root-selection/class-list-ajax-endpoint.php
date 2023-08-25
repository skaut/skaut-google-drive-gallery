<?php
/**
 * Contains the List_Ajax_Endpoint class.
 *
 * @package skaut-google-drive-gallery
 */

namespace Sgdg\Admin\Settings_Pages\Basic\Root_Selection;

use Sgdg\API_Client;
use Sgdg\API_Facade;
use Sgdg\Exceptions\Cant_Manage_Exception;
use Sgdg\Exceptions\Drive_Not_Found_Exception;
use Sgdg\Exceptions\File_Not_Found_Exception;
use Sgdg\Frontend\API_Fields;
use Sgdg\Frontend\Single_Page_Pagination_Helper;
use Sgdg\GET_Helpers;
use Sgdg\Helpers;
use Sgdg\Vendor\GuzzleHttp\Promise\FulfilledPromise;
use Sgdg\Vendor\GuzzleHttp\Promise\PromiseInterface;
use Sgdg\Vendor\GuzzleHttp\Promise\RejectedPromise;
use Sgdg\Vendor\GuzzleHttp\Promise\Utils;

/**
 * Handles the list_gdrive_dir ajax endpoint.
 *
 * @phan-constructor-used-for-side-effects
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
final class List_Ajax_Endpoint {

	/**
	 * Register all the hooks for this section.
	 */
	public function __construct() {
		if ( ! is_admin() ) {
			return;
		}

		add_action( 'wp_ajax_list_gdrive_dir', array( self::class, 'handle_ajax' ) );
	}

	/**
	 * Ajax call handler wrapper.
	 *
	 * This funtion is a wrapper for `ajax_handler_body()`. This function handles exceptions and returns them in a meaningful form.
	 *
	 * @see ajax_handler_body()
	 *
	 * @return void
	 */
	public static function handle_ajax() {
		Helpers::ajax_wrapper( array( self::class, 'ajax_handler_body' ) );
	}

	/**
	 * Handles ajax requests for the root selector.
	 *
	 * Returns a list of all subdirectories of a directory, or a list of all drives if a directory is not provided. Additionaly, returns all the directory names for the current path.
	 *
	 * @return void
	 *
	 * @throws Cant_Manage_Exception Insufficient role.
	 */
	public static function ajax_handler_body() {
		check_ajax_referer( 'sgdg_root_selection' );

		if ( ! current_user_can( 'manage_options' ) ) {
			throw new Cant_Manage_Exception();
		}

		$path_ids = GET_Helpers::get_array_variable( 'path' );
		API_Client::preamble();

		$promise = Utils::all(
			array(
				'path'     => self::path_ids_to_names( $path_ids ),
				'path_ids' => $path_ids,
			)
		)->then(
			null,
			static function ( $e ) {
				if ( $e instanceof File_Not_Found_Exception || $e instanceof Drive_Not_Found_Exception ) {
					return array(
						'path'      => array(),
						'path_ids'  => array(),
						'resetWarn' => esc_html__(
							'Root directory wasn\'t found. The plugin may be broken until a new one is chosen.',
							'skaut-google-drive-gallery'
						),
					);
				}

				return new RejectedPromise( $e );
			}
		)->then(
			static function ( $ret ) {
				$path_ids = $ret['path_ids'];
				unset( $ret['path_ids'] );
				$ret['directories'] =
					0 === count( $path_ids )
					? self::list_drives()
					: API_Facade::list_directories(
						end( $path_ids ),
						new API_Fields( array( 'id', 'name' ) ),
						new Single_Page_Pagination_Helper()
					);

				return Utils::all( $ret );
			}
		)->then(
			static function ( $ret ) {
				wp_send_json( $ret );
			}
		);
		API_Client::execute( array( $promise ) );
	}

	/**
	 * Converts an array of directory IDs to directory names.
	 *
	 * @param array<string> $path An array of Gooogle Drive directory IDs.
	 *
	 * @return PromiseInterface An array of directory names.
	 */
	private static function path_ids_to_names( $path ) {
		$promises = array();

		if ( count( $path ) > 0 ) {
			$promises[] = 'root' === $path[0]
				? new FulfilledPromise(
					esc_html__( 'My Drive', 'skaut-google-drive-gallery' )
				)
				: API_Facade::get_drive_name( $path[0] );
		}

		foreach ( array_slice( $path, 1 ) as $path_element ) {
			$promises[] = API_Facade::get_file_name( $path_element );
		}

		return Utils::all( $promises );
	}

	/**
	 * Lists all the drives for a user.
	 *
	 * Returns a list of all Shared drives plus "My Drive".
	 *
	 * @return PromiseInterface An array of drive records in the format `['name' => '', 'id' => '']`
	 */
	private static function list_drives() {
		return API_Facade::list_drives(
			new Single_Page_Pagination_Helper()
		)->then(
			static function ( $drives ) {
				array_unshift(
					$drives,
					array(
						'id'   => 'root',
						'name' => esc_html__( 'My Drive', 'skaut-google-drive-gallery' ),
					)
				);

				return $drives;
			}
		);
	}
}
