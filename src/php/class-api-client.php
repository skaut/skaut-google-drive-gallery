<?php
/**
 * Contains the API_Client class.
 *
 * @package skaut-google-drive-gallery
 */

namespace Sgdg;

/**
 * API client
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
final class API_Client {

	/**
	 * Google API client
	 *
	 * @var \Sgdg\Vendor\Google\Client|null $raw_client
	 */
	private static $raw_client = null;

	/**
	 * Google Drive API client
	 *
	 * @var \Sgdg\Vendor\Google\Service\Drive|null $drive_client
	 */
	private static $drive_client = null;

	/**
	 * The current Google API batch
	 *
	 * @var \Sgdg\Vendor\Google\Http\Batch|null $current_batch
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
	 * @return \Sgdg\Vendor\Google\Client
	 */
	public static function get_unauthorized_raw_client() {
		$raw_client = self::$raw_client;

		if ( null === $raw_client ) {
			$raw_client = new \Sgdg\Vendor\Google\Client();
			$raw_client->setAuthConfig(
				array(
					'client_id'     => \Sgdg\Options::$client_id->get(),
					'client_secret' => \Sgdg\Options::$client_secret->get(),
					'redirect_uris' => array( esc_url_raw( admin_url( 'admin.php?page=sgdg_basic&action=oauth_redirect' ) ) ),
				)
			);
			$raw_client->setAccessType( 'offline' );
			$raw_client->setApprovalPrompt( 'force' );
			$raw_client->addScope( \Sgdg\Vendor\Google\Service\Drive::DRIVE_READONLY );
			self::$raw_client = $raw_client;
		}

		return $raw_client;
	}

	/**
	 * Returns a fully configured and authorized Google client.
	 *
	 * @return \Sgdg\Vendor\Google\Client
	 *
	 * @throws \Sgdg\Exceptions\Plugin_Not_Authorized_Exception Not authorized.
	 */
	public static function get_authorized_raw_client() {
		$raw_client   = self::get_unauthorized_raw_client();
		$access_token = get_option( 'sgdg_access_token', false );

		if ( false === $access_token ) {
			throw new \Sgdg\Exceptions\Plugin_Not_Authorized_Exception();
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
	 * @return \Sgdg\Vendor\Google\Service\Drive
	 */
	public static function get_drive_client() {
		$drive_client = self::$drive_client;

		if ( null === $drive_client ) {
			$raw_client         = self::get_authorized_raw_client();
			$drive_client       = new \Sgdg\Vendor\Google\Service\Drive( $raw_client );
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
	 * @param \Sgdg\Vendor\GuzzleHttp\Psr7\Request $request The Google API request.
	 * @param callable                             $transform A function to be executed when the request completes, in the format `function( $response ): $output` where `$response` is the Google API response. The function should do any transformations on the output data necessary.
	 * @param callable|null                        $rejection_handler A function to be executed when the request fails, in the format `function( $exception ): $output` where `$exception` is the exception in question and `$output` should be a RejectedPromise.
	 *
	 * @return \Sgdg\Vendor\GuzzleHttp\Promise\PromiseInterface A promise that will be resolved in `$callback`.
	 *
	 * @throws \Sgdg\Exceptions\Internal_Exception The method was called without the preamble.
	 */
	public static function async_request( $request, $transform, $rejection_handler = null ) {
		if ( null === self::$current_batch ) {
			throw new \Sgdg\Exceptions\Internal_Exception();
		}

		$key = wp_rand();
		// @phan-suppress-next-line PhanPossiblyNonClassMethodCall
		self::$current_batch->add( $request, $key );
		$promise                                      = new \Sgdg\Vendor\GuzzleHttp\Promise\Promise();
		self::$pending_requests[ 'response-' . $key ] = static function( $response ) use ( $transform, $promise ) {
			try {
				self::check_response( $response );
				$promise->resolve( $transform( $response ) );
			} catch ( \Sgdg\Exceptions\Exception $e ) {
				$promise->reject( $e );
			}
		};

		return $promise->then( null, $rejection_handler );
	}

	/**
	 * Registers a paginated request to be executed later.
	 *
	 * @param callable                         $request A function which makes the Google API request. In the format `function( $page_token )` where `$page_token` is the pagination token to use.
	 * @param callable                         $transform A function to be executed when the request completes, in the format `function( $response ): $output` where `$response` is the Google API response. The function should do any transformations on the output data necessary.
	 * @param \Sgdg\Frontend\Pagination_Helper $pagination_helper An initialized pagination helper.
	 * @param callable|null                    $rejection_handler A function to be executed when the request fails, in the format `function( $exception ): $output` where `$exception` is the exception in question and `$output` should be a RejectedPromise.
	 *
	 * @return \Sgdg\Vendor\GuzzleHttp\Promise\PromiseInterface A promise that will be resolved in `$callback`.
	 */
	public static function async_paginated_request( $request, $transform, $pagination_helper, $rejection_handler = null ) {
		/**
		 * Gets one page.
		 *
		 * @throws \Sgdg\Exceptions\Internal_Exception The method was called without the preamble.
		 */
		$page    = static function( $page_token, $promise, $previous_output ) use ( $request, $transform, $pagination_helper, &$page ) {
			if ( null === self::$current_batch ) {
				throw new \Sgdg\Exceptions\Internal_Exception();
			}

			$key = wp_rand();
			// @phan-suppress-next-line PhanPossiblyNonClassMethodCall
			self::$current_batch->add( $request( $page_token ), $key );
			self::$pending_requests[ 'response-' . $key ] = static function( $response ) use ( $promise, $previous_output, $transform, $pagination_helper, &$page ) {
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
				} catch ( \Sgdg\Exceptions\Exception $e ) {
					$promise->reject( $e );
				}
			};
		};
		$promise = new \Sgdg\Vendor\GuzzleHttp\Promise\Promise();
		$page( null, $promise, array() );

		return $promise->then( null, $rejection_handler );
	}

	/**
	 * Executes all requests and resolves all promises.
	 *
	 * @param array<int|string, \Sgdg\Vendor\GuzzleHttp\Promise\PromiseInterface> $promises The promises to resolve and throw exceptions if they reject.
	 *
	 * @return array<int|string, mixed> A list of results from the promises. Is in the same format as the parameter `$promises`, i.e. if an associative array of promises is passed, an associative array of results will be returned.
	 */
	public static function execute( $promises = array() ) {
		if ( is_null( self::$current_batch ) ) {
			\Sgdg\Vendor\GuzzleHttp\Promise\Utils::queue()->run();

			return \Sgdg\Vendor\GuzzleHttp\Promise\Utils::all( $promises )->wait();
		}

		$batch               = self::$current_batch;
		self::$current_batch = self::get_drive_client()->createBatch();
		/**
		 * The closure executes the batch and throws the exception if it is a rate limit exceeded exception (this is needed by the task runner).
		 *
		 * @throws \Sgdg\Vendor\Google\Service\Exception Rate limit excepted.
		 */
		$task      = new \Sgdg\Vendor\Google\Task\Runner(
			array(
				'retries' => 100,
			),
			'Batch Drive call',
			static function() use ( $batch ) {
				// @phan-suppress-next-line PhanPossiblyNonClassMethodCall
				$ret = $batch->execute();

				foreach ( $ret as $response ) {
					if ( $response instanceof \Sgdg\Vendor\Google\Service\Exception ) {
						$errors = array_column( $response->getErrors(), 'reason' );

						if ( in_array( 'rateLimitExceeded', $errors, true ) || in_array( 'userRateLimitExceeded', $errors, true ) ) {
							throw $response;
						}
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

		\Sgdg\Vendor\GuzzleHttp\Promise\Utils::queue()->run();

		if ( count( self::$pending_requests ) > 0 ) {
			self::execute();
		}

		self::$current_batch = null;
		self::get_drive_client()->getClient()->setUseBatch( false );

		return \Sgdg\Vendor\GuzzleHttp\Promise\Utils::all( $promises )->wait();
	}

	/**
	 * Checks the API response and throws an exception if there was a problem.
	 *
	 * @param \ArrayAccess<mixed, mixed>|\Countable|\Iterator|\Sgdg\Vendor\Google\Collection|\Sgdg\Vendor\Google\Model|\Sgdg\Vendor\Google\Service\Drive\FileList|\Traversable|iterable<mixed> $response The API response.
	 *
	 * @return void
	 *
	 * @throws \Sgdg\Exceptions\API_Rate_Limit_Exception Rate limit exceeded.
	 * @throws \Sgdg\Exceptions\Not_Found_Exception The requested resource couldn't be found.
	 * @throws \Sgdg\Exceptions\API_Exception A wrapped API exception.
	 */
	private static function check_response( $response ) {
		if ( ! ( $response instanceof \Sgdg\Vendor\Google\Service\Exception ) ) {
			return;
		}

		if ( in_array( 'userRateLimitExceeded', array_column( $response->getErrors(), 'reason' ), true ) ) {
			throw new \Sgdg\Exceptions\API_Rate_Limit_Exception( $response );
		}

		if ( in_array( 'notFound', array_column( $response->getErrors(), 'reason' ), true ) ) {
			throw new \Sgdg\Exceptions\Not_Found_Exception();
		}

		throw new \Sgdg\Exceptions\API_Exception( $response );
	}

}
