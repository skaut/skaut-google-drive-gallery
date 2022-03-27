<?php
/**
 * Contains the Gallery class.
 *
 * @package skaut-google-drive-gallery
 */

namespace Sgdg\Frontend;

/**
 * Contains all the functions used to handle the "gallery" AJAX endpoint.
 *
 * The "gallery" AJAX endpoint gets called when the gallery is initialized and the each time the user navigates the directories of the gallery. The endpoint returns the info about the currently viewed directory and the first page of the content.
 */
class Gallery {
	/**
	 * Registers the "gallery" AJAX endpoint
	 */
	public function __construct() {
		add_action( 'wp_ajax_gallery', array( self::class, 'handle_ajax' ) );
		add_action( 'wp_ajax_nopriv_gallery', array( self::class, 'handle_ajax' ) );
	}

	/**
	 * Handles errors for the "gallery" AJAX endpoint.
	 *
	 * This function is a wrapper around `handle_ajax_body` that handles all the possible errors that can occur and sends them back as error messages.
	 *
	 * @return void
	 */
	public static function handle_ajax() {
		try {
			self::ajax_handler_body();
		} catch ( \Sgdg\Exceptions\Exception $e ) {
			wp_send_json( array( 'error' => $e->getMessage() ) );
		} catch ( \Exception $e ) {
			if ( \Sgdg\is_debug_display() ) {
				wp_send_json( array( 'error' => $e->getMessage() ) );
			}
			wp_send_json( array( 'error' => esc_html__( 'Unknown error.', 'skaut-google-drive-gallery' ) ) );
		}
	}

	/**
	 * Actually handles the "gallery" AJAX endpoint.
	 *
	 * Returns the names of the directories along the user-selected path and the first page of the gallery.
	 *
	 * @return void
	 */
	private static function ajax_handler_body() {
		list( $parent_id, $options, $path_verification ) = \Sgdg\Frontend\Page::get_context();
		$pagination_helper                               = ( new \Sgdg\Frontend\Pagination_Helper() )->withOptions( $options, true );
		$raw_path                                        = \Sgdg\GET_Helpers::get_string_variable( 'path' );
		$path_names                                      = self::path_names( '' !== $raw_path ? explode( '/', $raw_path ) : array(), $options );
		$page_promise                                    = \Sgdg\Vendor\GuzzleHttp\Promise\Utils::all( array( \Sgdg\Frontend\Page::get_page( $parent_id, $pagination_helper, $options ), $path_names ) )->then(
			static function( $wrapper ) {
				list( $page, $path_names ) = $wrapper;
				$page['path']              = $path_names;
				wp_send_json( $page );
			}
		);
		\Sgdg\API_Client::execute( array( $path_verification, $page_promise ) );
	}

	/**
	 * Adds names to a path represented as a list of directory IDs
	 *
	 * @param array<string>                $path A list of directory IDs.
	 * @param \Sgdg\Frontend\Options_Proxy $options Gallery options.
	 *
	 * @return \Sgdg\Vendor\GuzzleHttp\Promise\PromiseInterface A promise resolving to a list of records in the format `['id' => 'id', 'name' => 'name']`.
	 */
	private static function path_names( $path, $options ) {
		return \Sgdg\Vendor\GuzzleHttp\Promise\Utils::all(
			array_map(
				static function( $segment ) use ( &$options ) {
					return \Sgdg\API_Facade::get_file_name( $segment )->then(
						static function( $name ) use ( $segment, &$options ) {
							$pos = false;
							if ( '' !== $options->get( 'dir_prefix' ) ) {
								$pos = mb_strpos( $name, $options->get( 'dir_prefix' ) );
							}
							return array(
								'id'   => $segment,
								'name' => mb_substr( $name, false !== $pos ? $pos + 1 : 0 ),
							);
						}
					);
				},
				$path
			)
		);
	}
}
