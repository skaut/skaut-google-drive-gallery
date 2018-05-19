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
	if ( isset( $_GET['code'] ) && ! get_option( 'sgdg_access_token' ) ) {
		$client = \Sgdg\Frontend\GoogleAPILib\get_raw_client();
		$client->authenticate( $_GET['code'] );
		$access_token = $client->getAccessToken();
		update_option( 'sgdg_access_token', $access_token );
	}
	header( 'Location: ' . esc_url_raw( admin_url( 'options-general.php?page=sgdg' ) ) );
}

function oauth_revoke() {
	$client = \Sgdg\Frontend\GoogleAPILib\get_raw_client();
	$client->revokeToken();
	delete_option( 'sgdg_access_token' );
	header( 'Location: ' . esc_url_raw( admin_url( 'options-general.php?page=sgdg' ) ) );
}
