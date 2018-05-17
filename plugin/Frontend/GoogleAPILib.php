<?php
namespace Sgdg\Frontend\GoogleAPILib;

function getRawClient()
{
	$client = new \Sgdg\Vendor\Google_Client();
	$client->setAuthConfig(['client_id' => \Sgdg\Options::$clientID->get(), 'client_secret' => \Sgdg\Options::$clientSecret->get(), 'redirect_uris' => [esc_url_raw(admin_url('options-general.php?page=sgdg&action=oauth_redirect'))]]);
	$client->setAccessType('offline');
	$client->setApprovalPrompt('force');
	$client->addScope(\Sgdg\Vendor\Google_Service_Drive::DRIVE_READONLY);
	return $client;
}

function getDriveClient()
{
	$client = \Sgdg\Frontend\GoogleAPILib\getRawClient();
	$accessToken = get_option('sgdg_access_token');
	if(!$accessToken)
	{
		throw new \Exception();
	}
	$client->setAccessToken($accessToken);

	if($client->isAccessTokenExpired())
	{
		$client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
		$newAccessToken = $client->getAccessToken();
		$mergedAccessToken = array_merge($accessToken, $newAccessToken);
		update_option('sgdg_access_token', $mergedAccessToken);
	}

	return new \Sgdg\Vendor\Google_Service_Drive($client);
}
