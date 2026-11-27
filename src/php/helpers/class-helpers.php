<?php
/**
 * Contains the Helpers class.
 *
 * @package skaut-google-drive-gallery
 */

namespace Sgdg;

use Exception as Base_Exception;
use Sgdg\Exceptions\Exception as Sgdg_Exception;
use const FILTER_VALIDATE_IP;
use const WP_DEBUG;
use const WP_DEBUG_DISPLAY;

/**
 * Contains various helper functions.
 */
final class Helpers {

	/**
	 * Returns the domain to register as an authorized domain of the Google app.
	 *
	 * Google only accepts the top private domain (e.g. `skauting.cz` for a website at `stredisko.skauting.cz`), so subdomains are removed. Second-level domains under country domains (e.g. `example.co.uk`) are kept. IP addresses and single-label hosts are returned as they are.
	 *
	 * @param string $host The host of the website.
	 *
	 * @return string The domain.
	 */
	public static function authorized_domain( $host ) {
		if ( false !== filter_var( $host, FILTER_VALIDATE_IP ) ) {
			return $host;
		}

		$labels = explode( '.', strtolower( rtrim( $host, '.' ) ) );
		$count  = count( $labels );

		if ( $count <= 2 ) {
			return implode( '.', $labels );
		}

		$is_second_level = 2 === strlen( $labels[ $count - 1 ] ) && in_array(
			$labels[ $count - 2 ],
			array(
				'ac',
				'co',
				'com',
				'edu',
				'gob',
				'gov',
				'ltd',
				'mil',
				'net',
				'nic',
				'org',
				'plc',
				'sch',
			),
			true
		);
		$length          = $is_second_level ? 3 : 2;

		return implode( '.', array_slice( $labels, -$length ) );
	}

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
				wp_send_json(
					array(
						'error' => esc_html( $e->getMessage() ),
						'trace' => esc_html( $e->getTraceAsString() ),
					)
				);
			}

			wp_send_json( array( 'error' => esc_html( $e->getMessage() ) ) );
		} catch ( Base_Exception $e ) {
			if ( self::is_debug_display() ) {
				wp_send_json(
					array(
						'error' => esc_html( $e->getMessage() ),
						'trace' => esc_html( $e->getTraceAsString() ),
					)
				);
			}

			wp_send_json( array( 'error' => esc_html__( 'Unknown error.', 'skaut-google-drive-gallery' ) ) );
		}
	}
}
