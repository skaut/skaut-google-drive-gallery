<?php
/**
 * Contains the API_Client class.
 *
 * @package skaut-google-drive-gallery
 */

namespace Sgdg;

use ArrayAccess;
use Countable;
use Iterator;
use Sgdg\Exceptions\API_Exception;
use Sgdg\Exceptions\API_Rate_Limit_Exception;
use Sgdg\Exceptions\Exception as Sgdg_Exception;
use Sgdg\Exceptions\Internal_Exception;
use Sgdg\Exceptions\Not_Found_Exception;
use Sgdg\Exceptions\Plugin_Not_Authorized_Exception;
use Sgdg\Frontend\Pagination_Helper;
use Sgdg\Options;
use Sgdg\Vendor\Google\Client;
use Sgdg\Vendor\Google\Collection;
use Sgdg\Vendor\Google\Http\Batch;
use Sgdg\Vendor\Google\Model;
use Sgdg\Vendor\Google\Service\Drive;
use Sgdg\Vendor\Google\Service\Drive\FileList;
use Sgdg\Vendor\Google\Service\Exception as Google_Service_Exception;
use Sgdg\Vendor\Google\Task\Runner;
use Sgdg\Vendor\GuzzleHttp\Promise\Promise;
use Sgdg\Vendor\GuzzleHttp\Promise\PromiseInterface;
use Sgdg\Vendor\GuzzleHttp\Promise\Utils;
use Sgdg\Vendor\GuzzleHttp\Psr7\Request;
use Traversable;

/**
 * API client
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
final class API_Client {

	/**
	 * Google API client
	 *
	 * @var Client|null $raw_client
	 */
	private static $raw_client = null;

	/**
	 * Google Drive API client
	 *
	 * @var Drive|null $drive_client
	 */
	private static $drive_client = null;

	/**
	 * The current Google API batch
	 *
	 * @var Batch|null $current_batch
	 */
	private static $current_batch = null;

	/**
	 * The currently pending API requests as a list of callbacks.
	 *
	 * @var array<callable> $pending_requests
	 */
	private static $pending_requests;

	/**
	 * Returns a Google client with set-up app info, but without authorization.
	 *
	 * @return Client
	 */
	public static function get_unauthorized_raw_client() {
		$raw_client = self::$raw_client;

		if ( null === $raw_client ) {
			$raw_client = new Client();
			$raw_client->setAuthConfig(
				array(
					'client_id'     => Options::$client_id->get(),
					'client_secret' => Options::$client_secret->get(),
					'redirect_uris' => array(
						esc_url_raw( admin_url( 'admin.php?page=sgdg_basic&action=oauth_redirect' ) ),
					),
				)
			);
			$raw_client->setAccessType( 'offline' );
			$raw_client->setApprovalPrompt( 'force' );
			$raw_client->addScope( Drive::DRIVE_READONLY );
			self::$raw_client = $raw_client;
		}

		return $raw_client;
	}

	/**
	 * Returns a fully configured and authorized Google client.
	 *
	 * @return Client
	 *
	 * @throws Plugin_Not_Authorized_Exception Not authorized.
	 */
	public static function get_authorized_raw_client() {
		$raw_client   = self::get_unauthorized_raw_client();
		$access_token = get_option( 'sgdg_access_token', false );

		if ( false === $access_token ) {
			throw new Plugin_Not_Authorized_Exception();
		}

		$raw_client->setAccessToken( $access_token );

		if ( $raw_client->isAccessTokenExpired() ) {
			$raw_client->fetchAccessTokenWithRefreshToken( $raw_client->getRefreshToken() );
			$new_access_token    = $raw_client->getAccessToken();
			$merged_access_token = array_merge( $access_token, $new_access_token );
			update_option( 'sgdg_access_token', $merged_access_token );
		}

		return $raw_client;
	}

	/**
	 * Returns a fully set-up Google Drive API client.
	 *
	 * @return Drive
	 */
	public static function get_drive_client() {
		$drive_client = self::$drive_client;

		if ( null === $drive_client ) {
			$raw_client         = self::get_authorized_raw_client();
			$drive_client       = new Drive( $raw_client );
			self::$drive_client = $drive_client;
		}

		return $drive_client;
	}

	/**
	 * Sets up request batching.
	 *
	 * @return void
	 */
	public static function preamble() {
		if ( ! is_null( self::$current_batch ) ) {
			return;
		}

		self::get_drive_client()->getClient()->setUseBatch( true );
		self::$current_batch    = self::get_drive_client()->createBatch();
		self::$pending_requests = array();
	}

	/**
	 * Registers a request to be executed later.
	 *
	 * @param Request       $request The Google API request.
	 * @param callable      $transform A function to be executed when the request completes, in the format `function( $response ): $output` where `$response` is the Google API response. The function should do any transformations on the output data necessary.
	 * @param callable|null $rejection_handler A function to be executed when the request fails, in the format `function( $exception ): $output` where `$exception` is the exception in question and `$output` should be a RejectedPromise.
	 *
	 * @return PromiseInterface A promise that will be resolved in `$callback`.
	 *
	 * @throws Internal_Exception The method was called without the preamble.
	 */
	public static function async_request( $request, $transform, $rejection_handler = null ) {
		if ( null === self::$current_batch ) {
			throw new Internal_Exception();
		}

		$key = wp_rand();
		// @phan-suppress-next-line PhanPossiblyNonClassMethodCall
		self::$current_batch->add( $request, $key );
		$promise                                      = new Promise();
		self::$pending_requests[ 'response-' . $key ] = static function( $response ) use ( $transform, $promise ) {
			try {
				self::check_response( $response );
				$promise->resolve( $transform( $response ) );
			} catch ( Sgdg_Exception $e ) {
				$promise->reject( $e );
			}
		};

		return $promise->then( null, $rejection_handler );
	}

	/**
	 * Registers a paginated request to be executed later.
	 *
	 * @param callable          $request A function which makes the Google API request. In the format `function( $page_token )` where `$page_token` is the pagination token to use.
	 * @param callable          $transform A function to be executed when the request completes, in the format `function( $response ): $output` where `$response` is the Google API response. The function should do any transformations on the output data necessary.
	 * @param Pagination_Helper $pagination_helper An initialized pagination helper.
	 * @param callable|null     $rejection_handler A function to be executed when the request fails, in the format `function( $exception ): $output` where `$exception` is the exception in question and `$output` should be a RejectedPromise.
	 *
	 * @return PromiseInterface A promise that will be resolved in `$callback`.
	 */
	public static function async_paginated_request(
		$request,
		$transform,
		$pagination_helper,
		$rejection_handler = null
	) {
		/**
		 * Gets one page.
		 *
		 * @throws Internal_Exception The method was called without the preamble.
		 *
		 * phpcs:disable SlevomatCodingStandard.PHP.DisallowReference.DisallowedInheritingVariableByReference
		 */
		$page = static function(
			$page_token,
			$promise,
			$previous_output
		) use ( $request, $transform, $pagination_helper, &$page ) {
			if ( null === self::$current_batch ) {
				throw new Internal_Exception();
			}

			$key = wp_rand();
			// @phan-suppress-next-line PhanPossiblyNonClassMethodCall
			self::$current_batch->add( $request( $page_token ), $key );
			self::$pending_requests[ 'response-' . $key ] = static function(
				$response
			) use ( $promise, $previous_output, $transform, $pagination_helper, &$page ) {
				try {
					self::check_response( $response );
					$new_page_token = $response->getNextPageToken();
					$output         = $transform( $response );
					$output         = array_merge( $previous_output, $output );

					if ( null === $new_page_token || ! $pagination_helper->should_continue() ) {
						$promise->resolve( $output );

						return;
					}

					$page( $new_page_token, $promise, $output );
				} catch ( Sgdg_Exception $e ) {
					$promise->reject( $e );
				}
			};
		};
		// phpcs:enable
		$promise = new Promise();
		$page( null, $promise, array() );

		return $promise->then( null, $rejection_handler );
	}

	/**
	 * Executes all requests and resolves all promises.
	 *
	 * @param array<int|string, PromiseInterface> $promises The promises to resolve and throw exceptions if they reject.
	 *
	 * @return array<int|string, mixed> A list of results from the promises. Is in the same format as the parameter `$promises`, i.e. if an associative array of promises is passed, an associative array of results will be returned.
	 */
	public static function execute( $promises = array() ) {
		if ( is_null( self::$current_batch ) ) {
			Utils::queue()->run();

			return Utils::all( $promises )->wait();
		}

		$batch               = self::$current_batch;
		self::$current_batch = self::get_drive_client()->createBatch();
		/**
		 * The closure executes the batch and throws the exception if it is a rate limit exceeded exception (this is needed by the task runner).
		 *
		 * @throws Google_Service_Exception Rate limit excepted.
		 */
		$task      = new Runner(
			array(
				'retries' => 100,
			),
			'Batch Drive call',
			static function() use ( $batch ) {
				// @phan-suppress-next-line PhanPossiblyNonClassMethodCall
				$ret = $batch->execute();

				foreach ( $ret as $response ) {
					$exception = self::wrap_response_exception( $response );

					if (
						$response instanceof Google_Service_Exception && $exception instanceof API_Rate_Limit_Exception
					) {
						throw $response;
					}
				}

				return $ret;
			}
		);
		$responses = $task->run();

		foreach ( $responses as $key => $response ) {
			call_user_func( self::$pending_requests[ $key ], $response );
			unset( self::$pending_requests[ $key ] );
		}

		Utils::queue()->run();

		if ( count( self::$pending_requests ) > 0 ) {
			self::execute();
		}

		self::$current_batch = null;
		self::get_drive_client()->getClient()->setUseBatch( false );

		return Utils::all( $promises )->wait();
	}

	/**
	 * Checks the API response and throws an exception if there was a problem.
	 *
	 * @param ArrayAccess<mixed, mixed>|Countable|Iterator|Collection|Model|FileList|Traversable|iterable<mixed> $response The API response.
	 *
	 * @return void
	 *
	 * @throws API_Rate_Limit_Exception Rate limit exceeded.
	 * @throws Not_Found_Exception The requested resource couldn't be found.
	 * @throws API_Exception A wrapped API exception.
	 */
	private static function check_response( $response ) {
		$exception = self::wrap_response_exception( $response );

		if ( null !== $exception ) {
			throw $exception;
		}
	}

	/**
	 * Checks the API response and throws an exception if there was a problem.
	 *
	 * @param ArrayAccess<mixed, mixed>|Countable|Iterator|Collection|Model|FileList|Traversable|iterable<mixed> $response The API response.
	 *
	 * @return API_Rate_Limit_Exception|Not_Found_Exception|API_Exception|null The wrapped exception or null if the response is not an exception.
	 */
	private static function wrap_response_exception( $response ) {
		if ( ! ( $response instanceof Google_Service_Exception ) ) {
			return null;
		}

		$errors = $response->getErrors();

		if ( null === $errors ) {
			return new API_Exception( $response );
		}

		$error_reasons = array_column( $errors, 'reason' );

		if (
			in_array( 'rateLimitExceeded', $error_reasons, true ) ||
			in_array( 'userRateLimitExceeded', $error_reasons, true )
		) {
			return new API_Rate_Limit_Exception( $response );
		}

		if ( in_array( 'notFound', $error_reasons, true ) ) {
			return new Not_Found_Exception();
		}

		return new API_Exception( $response );
	}
}
