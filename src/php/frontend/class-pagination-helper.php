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
	 * Whether the given gallery has more items to show after finishing the current page. Null means undecided.
	 *
	 * @var bool|null $has_more
	 */
	private $has_more;

	/**
	 * Pagination_Helper class constructor
	 *
	 * @param \Sgdg\Frontend\Options_Proxy $options Gallery options.
	 * @param bool                         $show_previous Whether to also show all previous pages.
	 */
	public function __construct( $options, $show_previous ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$this->page      = isset( $_GET['page'] ) ? intval( max( 1, intval( $_GET['page'] ) ) ) : 1;
		$this->page_size = intval( $options->get( 'page_size' ) );
		$this->to_skip   = $show_previous ? 0 : $this->page_size * ( $this->page - 1 );
		$this->to_show   = $show_previous ? $this->page_size * $this->page : $this->page_size;
		$this->has_more  = null;
	}

	/**
	 * Returns how many items the next list API call should fetch.
	 *
	 * @param int $maximum The maximum allowed size for this type of request.
	 *
	 * @return int The number of items.
	 */
	public function next_list_size( $maximum ) {
		return min( $maximum, $this->to_skip + $this->to_show + ( is_null( $this->has_more ) ? 1 : 0 ) );
	}

	/**
	 * Iterates through a list, skipping items where appropriate.
	 *
	 * @param \ArrayAccess&\Countable $list The list to go through.
	 * @param callable                $iterator The function to call on each unskipped item.
	 */
	public function iterate( $list, $iterator ) {
		$list_size = count( $list );
		if ( $list_size <= $this->to_skip ) {
			$this->to_skip -= $list_size;
			return;
		}
		$start         = $this->to_skip;
		$this->to_skip = 0;
		if ( $list_size - $start > $this->to_show ) {
			$this->has_more = true;
		}
		$stop           = intval( min( $list_size, $start + $this->to_show ) );
		$this->to_show -= $stop - $start;
		for ( $i = $start; $i < $stop; ++$i ) {
			$iterator( $list[ $i ] );
		}
	}
}
