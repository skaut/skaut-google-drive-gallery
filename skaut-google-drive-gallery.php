<?php
/*
Plugin Name:	Google drive gallery
Plugin URI:		
Description:	A Wordpress gallery using Google drive as file storage
Version:		0.1
Author:			Marek Dědič
Author URI:		
License:		MIT
License URI:	

MIT License

Copyright (c) 2018 Marek Dědič

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
*/

defined('ABSPATH') or die('Die, die, die!');

if(!class_exists('Sgdg_plugin'))
{
	class Sgdg_plugin
	{
		public static function init() : void
		{
			add_action('admin_init', ['Sgdg_plugin', 'plugin_oauth']);
			add_action('admin_init', ['Sgdg_plugin', 'plugin_register_settings']);
			add_action('admin_menu', ['Sgdg_plugin', 'plugin_options_page']);
		}

		public static function plugin_oauth() : void
		{
			if(isset($_GET['action']))
			{
				include_once('vendor/autoload.php');
				$client = new Google_Client();
				$client->setAuthConfig(['client_id' => get_option('sgdg_client_id'), 'client_secret' => get_option('sgdg_client_secret'), 'redirect_uris' => [esc_url_raw(admin_url("options-general.php?page=sgdg&action=oauth_redirect"))]]);
				$client->setAccessType('offline');
				$client->addScope(Google_Service_Drive::DRIVE_READONLY);
				if($_GET['action'] === 'oauth_grant')
				{
					$auth_url = $client->createAuthUrl();
					header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
				}
				elseif($_GET['action'] === 'oauth_redirect' && isset($_GET['code']))
				{
					$client->authenticate($_GET['code']);
					$access_token = $client->getAccessToken();
					update_option('sgdg_access_token', $access_token);
					add_settings_error('sgdg_messages', 'sgdg_auth_success', 'Authentication granted successfully.', 'updated');
				}
			}
		}

		public static function plugin_register_settings() : void
		{
			register_setting('sgdg', 'sgdg_client_id', ['type' => 'string']);
			register_setting('sgdg', 'sgdg_client_secret', ['type' => 'string']);
			add_settings_section('sgdg_auth', 'Authentication', ['Sgdg_plugin', 'auth_html'], 'sgdg');
			add_settings_field('sgdg_redirect_uri', 'Authorized redirect URL', ['Sgdg_plugin', 'redirect_uri_html'], 'sgdg', 'sgdg_auth');
			add_settings_field('sgdg_client_id', 'Client ID', ['Sgdg_plugin', 'client_id_html'], 'sgdg', 'sgdg_auth');
			add_settings_field('sgdg_client_secret', 'Client Secret', ['Sgdg_plugin', 'client_secret_html'], 'sgdg', 'sgdg_auth');
		}

		public static function plugin_options_page() : void
		{
			add_options_page('Google drive gallery', 'Google drive gallery', 'manage_options', 'sgdg', ['Sgdg_plugin', 'options_page_html']);
		}

		public static function options_page_html() : void
		{
			if (!current_user_can('manage_options'))
			{
				return;
			}
 
			settings_errors('sgdg_messages');
			?>
			<div class="wrap">
				<h1><?php echo(esc_html(get_admin_page_title())); ?></h1>
				<form action="options.php" method="post">
					<?php
					do_settings_sections('sgdg');
					submit_button('Save Settings');
					?>
				</form>
			</div>
			<?php
		}

		public static function auth_html() : void
		{
			echo('<p>Create a Google app and provide the following details:</p><a class="button button-primary" href="' . esc_url_raw(admin_url("options-general.php?page=sgdg&action=oauth_grant")) . '">Grant Permission</a>');
		}

		public static function client_id_html() : void
		{
			self::field_html('sgdg_client_id');
		}

		public static function client_secret_html() : void
		{
			self::field_html('sgdg_client_secret');
		}

		public static function email_html() : void
		{
			self::field_html('sgdg_email');
		}

		private static function field_html(string $setting_name) : void
		{
			$setting = get_option($setting_name);
			?>
			<input type="text" name="<?php echo($setting_name); ?>" value="<?php echo(isset($setting) ? esc_attr($setting) : ''); ?>" class="regular-text code">
			<?php
		}

		public static function redirect_uri_html() : void
		{
			?>
			<input type="text" value="<?php echo esc_url_raw(admin_url("options-general.php?page=sgdg&action=oauth_redirect")); ?>" readonly class="regular-text code">
			<?php
		}
	}

	Sgdg_plugin::init();
}
