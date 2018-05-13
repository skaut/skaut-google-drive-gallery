<?php declare(strict_types=1);
namespace Sgdg\Admin\GoogleAPILib;

if(!is_admin())
{
	return;
}

function OAuth_grant() : void
{
	$client = \Sgdg\Frontend\GoogleAPILib\getRawClient();
	$auth_url = $client->createAuthUrl();
	header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
}

function OAuth_redirect() : void
{
	if(isset($_GET['code']) && !get_option('sgdg_access_token'))
	{
		$client = \Sgdg\Frontend\GoogleAPILib\getRawClient();
		$client->authenticate($_GET['code']);
		$access_token = $client->getAccessToken();
		update_option('sgdg_access_token', $access_token);
	}
	header('Location: ' . esc_url_raw(admin_url('options-general.php?page=sgdg')));
}

function OAuth_revoke() : void
{
	$client = \Sgdg\Frontend\GoogleAPILib\getRawClient();
	$client->revokeToken();
	delete_option('sgdg_access_token');
	header('Location: ' . esc_url_raw(admin_url('options-general.php?page=sgdg')));
}
