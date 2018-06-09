<?php
namespace Sgdg\Admin\GoogleAPILib;

if ( ! is_admin() ) {
	return;
}

function oauth_grant() {
	$client   = \Sgdg\Frontend\GoogleAPILib\get_raw_client();
	$auth_url = $client->createAuthUrl();
	header( 'Location: ' . filter_var( $auth_url, FILTER_SANITIZE_URL ) );
}

function oauth_redirect() {
	// phpcs:ignore WordPress.CSRF.NonceVerification.NoNonceVerification
	if ( isset( $_GET['code'] ) && ! get_option( 'sgdg_access_token' ) ) {
		$client = \Sgdg\Frontend\GoogleAPILib\get_raw_client();
		// phpcs:ignore WordPress.CSRF.NonceVerification.NoNonceVerification
		$client->authenticate( $_GET['code'] );
		$access_token = $client->getAccessToken();

		$drive_client = new \Sgdg\Vendor\Google_Service_Drive( $client );
		try {
			\Sgdg\Admin\OptionsPage\RootSelection\list_teamdrives( $drive_client );
		} catch ( \Sgdg\Vendor\Google_Service_Exception $e ) {
			if ( 'accessNotConfigured' === $e->getErrors()[0]['reason'] ) {
				header( 'Location: ' . esc_url_raw( admin_url( 'admin.php?page=sgdg&error=not-enabled' ) ) );
				die();
			}
			header( 'Location: ' . esc_url_raw( admin_url( 'admin.php?page=sgdg&error=' . $e->getErrors()[0]['message'] ) ) );
			die();
		}
		update_option( 'sgdg_access_token', $access_token );
	}
	header( 'Location: ' . esc_url_raw( admin_url( 'admin.php?page=sgdg&success=true' ) ) );
}

function oauth_revoke() {
	$client = \Sgdg\Frontend\GoogleAPILib\get_raw_client();
	$client->revokeToken();
	delete_option( 'sgdg_access_token' );
	header( 'Location: ' . esc_url_raw( admin_url( 'admin.php?page=sgdg&success=true' ) ) );
}
