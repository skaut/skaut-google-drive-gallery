<?php
/**
 * Contains the Page class.
 *
 * @package skaut-google-drive-gallery
 */

namespace Sgdg\Frontend;

/**
 * Contains all the functions used to handle the "page" AJAX endpoint.
 *
 * The "page" AJAX enpoint gets called each time the user needs to fetch items for a gallery.
 *
 * @phan-constructor-used-for-side-effects
 */
class Page {

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
		\Sgdg\Helpers::ajax_wrapper( array( self::class, 'ajax_handler_body' ) );
	}

	/**
	 * Actually handles the "page" AJAX endpoint.
	 *
	 * Returns a list of directories and a list of images.
	 *
	 * @return void
	 *
	 * @see get_page()
	 */
	public static function ajax_handler_body() {
		list( $parent_id, $options, $path_verification ) = \Sgdg\Frontend\Gallery_Context::get();
		$pagination_helper                               = ( new \Sgdg\Frontend\Pagination_Helper() )->withOptions( $options, false );

		$page_promise = self::get_page( $parent_id, $pagination_helper, $options )->then(
			static function( $page ) {
				wp_send_json( $page );
			}
		);
		\Sgdg\API_Client::execute( array( $path_verification, $page_promise ) );
	}

	/**
	 * Return one page worth of items
	 *
	 * Lists one page of items - first directories and then images, up until the number of items per page is reached.
	 *
	 * @param string                           $parent_id A directory to list items of.
	 * @param \Sgdg\Frontend\Pagination_Helper $pagination_helper An initialized pagination helper.
	 * @param \Sgdg\Frontend\Options_Proxy     $options The configuration of the gallery.
	 *
	 * @return \Sgdg\Vendor\GuzzleHttp\Promise\PromiseInterface A promise resolving to the page return value.
	 */
	public static function get_page( $parent_id, $pagination_helper, $options ) {
		$page = array(
			'directories' => Page\Directories::directories( $parent_id, $pagination_helper, $options ),
		);
		return \Sgdg\Vendor\GuzzleHttp\Promise\Utils::all( $page )->then(
			static function( $page ) use ( $parent_id, $pagination_helper, $options ) {
				if ( $pagination_helper->should_continue() ) {
					$page['images'] = Page\Images::images( $parent_id, $pagination_helper, $options );
				}
				return \Sgdg\Vendor\GuzzleHttp\Promise\Utils::all( $page );
			}
		)->then(
			static function( $page ) use ( $parent_id, $pagination_helper, $options ) {
				if ( $pagination_helper->should_continue() ) {
					$page['videos'] = Page\Videos::videos( $parent_id, $pagination_helper, $options );
				}
				return \Sgdg\Vendor\GuzzleHttp\Promise\Utils::all( $page );
			}
		)->then(
			static function( $page ) use ( $pagination_helper ) {
				$page['more'] = $pagination_helper->has_more();
				return $page;
			}
		);
	}

}
