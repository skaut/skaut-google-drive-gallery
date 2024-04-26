<?php
/**
 * Contains the Page class.
 *
 * @package skaut-google-drive-gallery
 */

namespace Sgdg\Frontend;

use Sgdg\API_Client;
use Sgdg\Frontend\Gallery_Context;
use Sgdg\Frontend\Options_Proxy;
use Sgdg\Frontend\Page\Directories;
use Sgdg\Frontend\Page\Images;
use Sgdg\Frontend\Page\Videos;
use Sgdg\Frontend\Paging_Pagination_Helper;
use Sgdg\Helpers;
use Sgdg\Vendor\GuzzleHttp\Promise\PromiseInterface;
use Sgdg\Vendor\GuzzleHttp\Promise\Utils;

/**
 * Contains all the functions used to handle the "page" AJAX endpoint.
 *
 * The "page" AJAX enpoint gets called each time the user needs to fetch items for a gallery.
 *
 * @phan-constructor-used-for-side-effects
 */
final class Page {

	/**
	 * Registers the "page" AJAX endpoint
	 */
	public function __construct() {
		add_action( 'wp_ajax_page', array( self::class, 'handle_ajax' ) );
		add_action( 'wp_ajax_nopriv_page', array( self::class, 'handle_ajax' ) );
	}

	/**
	 * Handles errors for the "page" AJAX endpoint.
	 *
	 * This function is a wrapper around `handle_ajax_body` that handles all the possible errors that can occur and sends them back as error messages.
	 *
	 * @return void
	 */
	public static function handle_ajax() {
		Helpers::ajax_wrapper( array( self::class, 'ajax_handler_body' ) );
	}

	/**
	 * Actually handles the "page" AJAX endpoint.
	 *
	 * Returns a list of directories and a list of images.
	 *
	 * @see get()
	 *
	 * @return void
	 */
	public static function ajax_handler_body() {
		list( $parent_id, $options, $path_verification ) = Gallery_Context::get();
		$pagination_helper                               = (
			new Paging_Pagination_Helper()
		)->withOptions( $options, false );

		$page_promise = self::get( $parent_id, $pagination_helper, $options );
		list( $page ) = API_Client::execute( array( $page_promise, $path_verification ) );
		wp_send_json( $page );
	}

	/**
	 * Return one page worth of items
	 *
	 * Lists one page of items - first directories and then images, up until the number of items per page is reached.
	 *
	 * @param string                   $parent_id A directory to list items of.
	 * @param Paging_Pagination_Helper $pagination_helper An initialized pagination helper.
	 * @param Options_Proxy            $options The configuration of the gallery.
	 *
	 * @return PromiseInterface A promise resolving to the page return value.
	 */
	public static function get( $parent_id, $pagination_helper, $options ) {
		$page = array(
			'directories' => Directories::get( $parent_id, $pagination_helper, $options ),
		);

		return Utils::all( $page )->then(
			static function ( $page ) use ( $parent_id, $pagination_helper, $options ) {
				if ( $pagination_helper->should_continue() ) {
					$page['images'] = Images::get( $parent_id, $pagination_helper, $options );
				}

				return Utils::all( $page );
			}
		)->then(
			static function ( $page ) use ( $parent_id, $pagination_helper, $options ) {
				if ( $pagination_helper->should_continue() ) {
					$page['videos'] = Videos::get( $parent_id, $pagination_helper, $options );
				}

				return Utils::all( $page );
			}
		)->then(
			static function ( $page ) use ( $pagination_helper ) {
				$page['more'] = $pagination_helper->has_more();

				return $page;
			}
		);
	}
}
