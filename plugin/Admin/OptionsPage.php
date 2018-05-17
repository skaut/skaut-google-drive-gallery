<?php
namespace Sgdg\Admin\OptionsPage;

require_once('OptionsPage/OAuthGrant.php');
require_once('OptionsPage/OAuthRevoke.php');
require_once('OptionsPage/RootSelection.php');
require_once('OptionsPage/Other.php');

if(!is_admin())
{
	return;
}

function register()
{
	add_action('admin_menu', '\\Sgdg\\Admin\\OptionsPage\\add');
	add_action('admin_init', '\\Sgdg\\Admin\\OptionsPage\\action_handler');
	if(!get_option('sgdg_access_token'))
	{
		OAuthGrant\register();
	}
	else
	{
		OAuthRevoke\register();
		RootSelection\register();
		Other\register();
	}
}

function add()
{
	add_options_page(esc_html__('Google drive gallery', 'skaut-google-drive-gallery'), esc_html__('Google drive gallery', 'skaut-google-drive-gallery'), 'manage_options', 'sgdg', '\\Sgdg\\Admin\\OptionsPage\\html');
}

function html()
{
	if(!current_user_can('manage_options'))
	{
		return;
	}

	settings_errors('sgdg_messages');
	echo('<div class="wrap">');
	echo('<h1>' . get_admin_page_title() . '</h1>');
	echo('<form action="options.php" method="post">');
	settings_fields('sgdg');
	do_settings_sections('sgdg');
	submit_button(esc_html__('Save Changes', 'skaut-google-drive-gallery'));
	echo('</form>');
	echo('</div>');
}

function action_handler()
{
	if(isset($_GET['page']) && $_GET['page'] === 'sgdg' && isset($_GET['action']))
	{

		if($_GET['action'] === 'oauth_grant')
		{
			\Sgdg\Admin\GoogleAPILib\OAuth_grant();
		}
		elseif($_GET['action'] === 'oauth_redirect')
		{
			\Sgdg\Admin\GoogleAPILib\OAuth_redirect();
		}
		elseif($_GET['action'] === 'oauth_revoke' && get_option('sgdg_access_token'))
		{
			\Sgdg\Admin\GoogleAPILib\OAuth_revoke();
		}
	}
}
