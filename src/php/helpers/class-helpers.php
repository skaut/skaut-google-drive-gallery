<?php
/**
 * Contains the Helpers class.
 *
 * @package skaut-google-drive-gallery
 */

namespace Sgdg;

use Exception as Base_Exception;
use Sgdg\Exceptions\Exception as Sgdg_Exception;
use const WP_DEBUG;
use const WP_DEBUG_DISPLAY;

/**
 * Contains various helper functions.
 */
final class Helpers {

	/**
	 * Checks whether debug info should be displayed
	 *
	 * @return bool True to display debug info.
	 */
	public static function is_debug_display() {
		if ( defined( 'WP_DEBUG' ) && defined( 'WP_DEBUG_DISPLAY' ) ) {
			return true === WP_DEBUG && true === WP_DEBUG_DISPLAY;
		}

		return false;
	}

	/**
	 * Runs an AJAX handler and handles errors.
	 *
	 * @param callable $handler The actual handler.
	 *
	 * @return void
	 */
	public static function ajax_wrapper( $handler ) {
		try {
			$handler();
		} catch ( Sgdg_Exception $e ) {
			if ( self::is_debug_display() ) {
				wp_send_json( array( 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString() ) );
			}

			wp_send_json( array( 'error' => $e->getMessage() ) );
		} catch ( Base_Exception $e ) {
			if ( self::is_debug_display() ) {
				wp_send_json( array( 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString() ) );
			}

			wp_send_json( array( 'error' => esc_html__( 'Unknown error.', 'skaut-google-drive-gallery' ) ) );
		}
	}

}
