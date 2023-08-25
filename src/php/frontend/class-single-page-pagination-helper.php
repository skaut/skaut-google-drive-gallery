<?php
/**
 * Contains the Single_Page_Pagination_Helper class.
 *
 * @package skaut-google-drive-gallery
 */

namespace Sgdg\Frontend;

/**
 * Loads exactly one page worth of data.
 */
final class Single_Page_Pagination_Helper implements Pagination_Helper {

	/**
	 * Returns how many items the next list API call should fetch.
	 *
	 * @param int $maximum The maximum allowed size for this type of request.
	 *
	 * @return int The number of items.
	 */
	public function next_list_size( $maximum ) {
		return $maximum;
	}

	/**
	 * Iterates through a list, skipping items where appropriate.
	 *
	 * @param array<mixed> $arr The list to go through.
	 * @param callable     $iterator The function to call on each unskipped item.
	 */
	public function iterate( $arr, $iterator ) {
		$list_size = count( $arr );

		for ( $i = 0; $i < $list_size; ++$i ) {
			$iterator( $arr[ $i ] );
		}
	}

	/**
	 * Returns `false`.
	 *
	 * @return bool `false`.
	 */
	public function should_continue() {
		return false;
	}
}
