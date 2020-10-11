<?php
/**
 * Contains the Pagination_Helper class.
 *
 * @package skaut-google-drive-gallery
 */

namespace Sgdg\Frontend;

/**
 * Stores pagination info and provides methods to access and use it easily.
 */
class Pagination_Helper {
	/**
	 * Which page to show.
	 *
	 * @var int $page
	 */
	private $page;

	/**
	 * The number of items per page.
	 *
	 * @var int $page_size
	 */
	private $page_size;

	/**
	 * How many items remain to be skipped (to get to the desired page).
	 *
	 * @var int $to_skip
	 */
	private $to_skip;

	/**
	 * How many items remain to be shown.
	 *
	 * @var int $to_show
	 */
	private $to_show;

	/**
	 * Pagination_Helper class constructor
	 *
	 * @param \Sgdg\Frontend\Options_Proxy $options Gallery options.
	 * @param bool                         $show_previous Whether to also show all previous pages.
	 */
	public function __construct( $options, $show_previous ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$this->page      = isset( $_GET['page'] ) ? max( 1, intval( $_GET['page'] ) ) : 1;
		$this->page_size = intval( $options->get( 'page_size' ) );
		$this->to_skip   = $show_previous ? 0 : $this->page_size * ( intval( $this->page ) - 1 );
		$this->to_show   = $show_previous ? $this->page_size * intval( $this->page ) : $this->page_size;
	}
}
