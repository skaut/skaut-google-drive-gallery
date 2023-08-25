<?php
/**
 * Contains the Gallery_Context class.
 *
 * @package skaut-google-drive-gallery
 */

namespace Sgdg\Frontend;

use Sgdg\API_Facade;
use Sgdg\Exceptions\Directory_Not_Found_Exception;
use Sgdg\Exceptions\Gallery_Expired_Exception;
use Sgdg\Exceptions\Path_Not_Found_Exception;
use Sgdg\Frontend\Options_Proxy;
use Sgdg\GET_Helpers;
use Sgdg\Vendor\GuzzleHttp\Promise\FulfilledPromise;
use Sgdg\Vendor\GuzzleHttp\Promise\PromiseInterface;
use Sgdg\Vendor\GuzzleHttp\Promise\RejectedPromise;

/**
 * Handles the gallery context.
 */
final class Gallery_Context {

	/**
	 * Returns common variables used by different parts of the codebase
	 *
	 * @return array{string, Options_Proxy, PromiseInterface} An array of the form {
	 *     @type string           The root directory of the gallery.
	 *     @type Options_Proxy    The configuration of the gallery.
	 *     @type PromiseInterface A promise rejecting if the path is invalid.
	 * }
	 *
	 * @throws Gallery_Expired_Exception The gallery has expired.
	 */
	public static function get() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! isset( $_GET['hash'] ) ) {
			throw new Gallery_Expired_Exception();
		}

		$transient = get_transient( 'sgdg_hash_' . GET_Helpers::get_string_variable( 'hash' ) );

		if ( false === $transient ) {
			throw new Gallery_Expired_Exception();
		}

		$path    = array( $transient['root'] );
		$options = new Options_Proxy( $transient['overriden'] );

		if ( '' !== GET_Helpers::get_string_variable( 'path' ) ) {
			$path = array_merge( $path, explode( '/', GET_Helpers::get_string_variable( 'path' ) ) );
		}

		return array(
			end( $path ),
			$options,
			self::verify_path( $path ),
		);
	}

	/**
	 * Checks that a path is a valid path on Google Drive.
	 *
	 * @param array<string> $path A list of directory IDs.
	 *
	 * @return PromiseInterface A promise that resolves if the path is valid
	 */
	private static function verify_path( array $path ) {
		if ( 1 === count( $path ) ) {
			return new FulfilledPromise( null );
		}

		return API_Facade::check_directory_in_directory( $path[1], $path[0] )->then(
			static function () use ( $path ) {
				array_shift( $path );

				return self::verify_path( $path );
			},
			static function ( $exception ) {
				if ( $exception instanceof Directory_Not_Found_Exception ) {
					$exception = new Path_Not_Found_Exception();
				}

				return new RejectedPromise( $exception );
			}
		);
	}
}
