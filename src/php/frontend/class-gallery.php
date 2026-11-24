<?php
/**
 * Contains the Gallery class.
 *
 * @package skaut-google-drive-gallery
 */

namespace Sgdg\Frontend;

use Sgdg\API_Client;
use Sgdg\Exceptions\API_Exception;
use Sgdg\Exceptions\API_Rate_Limit_Exception;
use Sgdg\Exceptions\Gallery_Expired_Exception;
use Sgdg\Exceptions\Internal_Exception;
use Sgdg\Exceptions\Not_Found_Exception;
use Sgdg\Exceptions\Path_Not_Found_Exception;
use Sgdg\Exceptions\Plugin_Not_Authorized_Exception;
use Sgdg\Exceptions\Unsupported_Value_Exception;
use Sgdg\Frontend\Gallery_Context;
use Sgdg\Frontend\Options_Proxy;
use Sgdg\Frontend\Page;
use Sgdg\Frontend\Paging_Pagination_Helper;
use Sgdg\GET_Helpers;
use Sgdg\Helpers;

/**
 * Contains all the functions used to handle the "gallery" AJAX endpoint.
 *
 * The "gallery" AJAX endpoint gets called when the gallery is initialized and the each time the user navigates the directories of the gallery. The endpoint returns the info about the currently viewed directory and the first page of the content.
 *
 * @phan-constructor-used-for-side-effects
 */
final class Gallery {

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
		Helpers::ajax_wrapper( array( self::class, 'ajax_handler_body' ) );
	}

	/**
	 * Actually handles the "gallery" AJAX endpoint.
	 *
	 * Returns the names of the directories along the user-selected path and the first page of the gallery.
	 *
	 * @throws API_Exception A wrapped API exception.
	 * @throws API_Rate_Limit_Exception Rate limit exceeded.
	 * @throws Gallery_Expired_Exception The gallery has expired.
	 * @throws Internal_Exception The method was called without an initialized batch.
	 * @throws Not_Found_Exception The requested resource couldn't be found.
	 * @throws Path_Not_Found_Exception The path to the gallery couldn't be found.
	 * @throws Plugin_Not_Authorized_Exception Not authorized.
	 * @throws Unsupported_Value_Exception A field that is not supported was passed in `$fields`.
	 *
	 * @return void
	 */
	public static function ajax_handler_body() {
		list( $parent_id, $options, $path_verification ) = Gallery_Context::get();
		$pagination_helper                               = (
			new Paging_Pagination_Helper()
		)->withOptions( $options, true );
		$raw_path                                        = GET_Helpers::get_string_variable( 'path' );
		list($page, $path_names)                         = API_Client::execute(
			array( Page::get( $parent_id, $pagination_helper, $options ), $path_verification )
		);
		$page['path']                                    = self::path_names(
			'' !== $raw_path ? explode( '/', $raw_path ) : array(),
			$path_names,
			$options
		);
		wp_send_json( $page );
	}

	/**
	 * Adds names to a path represented as a list of directory IDs
	 *
	 * The names are the ones under which the directories are listed in their parents, so a directory reached through a shortcut gets the name of the shortcut, the same as in the gallery itself.
	 *
	 * @param array<string> $path A list of directory IDs.
	 * @param array<string> $names The names of the directories in `$path`, in the same order.
	 * @param Options_Proxy $options Gallery options.
	 *
	 * @return array<array{id: string, name: string}> A list of records in the format `['id' => 'id', 'name' => 'name']`.
	 */
	private static function path_names( $path, $names, $options ) {
		$ret   = array();
		$count = count( $path );

		for ( $i = 0; $i < $count; ++$i ) {
			$name = $names[ $i ];
			$pos  = false;

			if ( '' !== $options->get( 'dir_prefix' ) ) {
				$pos = mb_strpos( $name, $options->get( 'dir_prefix' ) );
			}

			$ret[] = array(
				'id'   => $path[ $i ],
				'name' => mb_substr( $name, false !== $pos ? $pos + 1 : 0 ),
			);
		}

		return $ret;
	}
}
