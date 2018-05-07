<?php declare(strict_types=1);
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
		public static function getRawGoogleClient() : Google_Client
		{
			include_once('vendor/autoload.php');
			$client = new Google_Client();
			$client->setAuthConfig(['client_id' => get_option('sgdg_client_id'), 'client_secret' => get_option('sgdg_client_secret'), 'redirect_uris' => [esc_url_raw(admin_url('options-general.php?page=sgdg&action=oauth_redirect'))]]);
			$client->setAccessType('offline');
			$client->setApprovalPrompt('force');
			$client->addScope(Google_Service_Drive::DRIVE_READONLY);
			return $client;
		}

		public static function getDriveClient() : Google_Service_Drive
		{
			$client = self::getRawGoogleClient();
			$accessToken = get_option('sgdg_access_token');
			$client->setAccessToken($accessToken);

			if($client->isAccessTokenExpired())
			{
				$client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
				$newAccessToken = $client->getAccessToken();
				$mergedAccessToken = array_merge($accessToken, $newAccessToken);
				update_option('sgdg_access_token', $mergedAccessToken);
			}

			return new Google_Service_Drive($client);
		}

		public static function init() : void
		{
			add_action('init', ['Sgdg_plugin', 'register_shortcodes']);
			add_action('wp_enqueue_scripts', ['Sgdg_plugin', 'register_scripts_styles']);
			add_action('admin_init', ['Sgdg_plugin', 'action_handler']);
			add_action('admin_init', ['Sgdg_plugin', 'register_settings']);
			add_action('admin_menu', ['Sgdg_plugin', 'options_page']);
			if(!get_option('sgdg_access_token'))
			{
				add_action('admin_init', ['Sgdg_plugin', 'settings_oauth_grant']);
			}
			else
			{
				add_action('admin_init', ['Sgdg_plugin', 'settings_oauth_revoke']);
				add_action('admin_init', ['Sgdg_plugin', 'settings_root_selection']);
				add_action('admin_enqueue_scripts', ['Sgdg_plugin', 'enqueue_ajax']);
				add_action('wp_ajax_list_gdrive_dir', ['Sgdg_plugin', 'handle_ajax_list_gdrive_dir']);
			}
		}

		public static function register_shortcodes() : void
		{
			add_shortcode('sgdg', ['Sgdg_plugin', 'shortcode_gallery']);
		}

		public static function register_scripts_styles() : void
		{
			wp_register_script('sgdg_masonry', plugins_url('/node_modules/masonry-layout/dist/masonry.pkgd.min.js', __FILE__), ['jquery']);
			wp_register_script('sgdg_imagesloaded', plugins_url('/node_modules/imagesloaded/imagesloaded.pkgd.min.js', __FILE__), ['jquery']);
			//wp_register_script('sgdg_imagelightbox_script', plugins_url('/node_modules/imagelightbox/dist/imagelightbox.min.js', __FILE__), ['jquery']);
			wp_register_script('sgdg_imagelightbox_script', plugins_url('/node_modules/imagelightbox/src/imagelightbox.js', __FILE__), ['jquery']);
			wp_register_script('sgdg_gallery_init', plugins_url('/js/gallery_init.js', __FILE__), ['jquery']);
			wp_register_style('sgdg_imagelightbox_style', plugins_url('/node_modules/imagelightbox/dist/imagelightbox.min.css', __FILE__));
			wp_register_style('sgdg_gallery_css', plugins_url('/css/gallery.css', __FILE__));
		}

		public static function shortcode_gallery(array $atts = []) : string
		{
			wp_enqueue_script('sgdg_masonry');
			wp_enqueue_script('sgdg_imagesloaded');
			wp_enqueue_script('sgdg_imagelightbox_script');
			wp_enqueue_script('sgdg_gallery_init');
			wp_enqueue_style('sgdg_imagelightbox_style');
			wp_enqueue_style('sgdg_gallery_css');
			if(isset($atts['name']))
			{
				$client = self::getDriveClient();
				$path = get_option('sgdg_root_dir', ['root']);
				$root = end($path);
				$pageToken = null;
				do
				{
					$optParams = [
						'q' => '"' . $root . '" in parents and trashed = false',
						'pageToken' => $pageToken,
						'pageSize' => 1000,
						'fields' => 'nextPageToken, files(id, name)'
					];
					$response = $client->files->listFiles($optParams);
					foreach($response->getFiles() as $file)
					{
						if($file->getName() == $atts['name'])
						{
							return self::render_gallery($file->getId());
						}
					}
					$pageToken = $response->pageToken;
				}
				while($pageToken != null);
			}
			return 'No such gallery found.';
		}

		private static function render_gallery($id) : string
		{
			$client = self::getDriveClient();
			$ret = '<div class="grid">';
			$pageToken = null;
			do
			{
				$optParams = [
					'q' => '"' . $id . '" in parents and mimeType contains "image/" and trashed = false',
					'pageToken' => $pageToken,
					'pageSize' => 1000,
					'fields' => 'nextPageToken, files(thumbnailLink)'
				];
				$response = $client->files->listFiles($optParams);
				foreach($response->getFiles() as $file)
				{
					$ret .= '<div class="grid-item"><a data-imagelightbox="a" href="' . substr($file->getThumbnailLink(), 0, -3) . '1920"><img src="' . substr($file->getThumbnailLink(), 0, -3) . '500"></a></div>';
				}
				$pageToken = $response->pageToken;
			}
			while($pageToken != null);
			$ret .= '</div>';
			return $ret;
		}

		public static function action_handler() : void
		{
			if(isset($_GET['page']) && $_GET['page'] === 'sgdg' && isset($_GET['action']))
			{

				if($_GET['action'] === 'oauth_grant')
				{
					$client = self::getRawGoogleClient();
					$auth_url = $client->createAuthUrl();
					header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
				}
				elseif($_GET['action'] === 'oauth_redirect' && isset($_GET['code']) && !get_option('sgdg_access_token'))
				{
					$client = self::getRawGoogleClient();
					$client->authenticate($_GET['code']);
					$access_token = $client->getAccessToken();
					update_option('sgdg_access_token', $access_token);
					header('Location: ' . esc_url_raw(admin_url('options-general.php?page=sgdg')));
				}
				elseif($_GET['action'] === 'oauth_revoke' && get_option('sgdg_access_token'))
				{
					$client = self::getRawGoogleClient();
					$client->revokeToken();
					delete_option('sgdg_access_token');
					header('Location: ' . esc_url_raw(admin_url('options-general.php?page=sgdg')));
				}
			}
		}

		public static function register_settings() : void
		{
			register_setting('sgdg', 'sgdg_client_id', ['type' => 'string']);
			register_setting('sgdg', 'sgdg_client_secret', ['type' => 'string']);
			register_setting('sgdg', 'sgdg_root_dir', ['type' => 'string', 'sanitize_callback' => ['Sgdg_plugin', 'decode_root_dir']]);
		}

		public static function settings_oauth_grant() : void
		{
			add_settings_section('sgdg_auth', 'Step 1: Authentication', ['Sgdg_plugin', 'auth_html'], 'sgdg');
			add_settings_field('sgdg_redirect_uri', 'Authorized redirect URL', ['Sgdg_plugin', 'redirect_uri_html'], 'sgdg', 'sgdg_auth');
			add_settings_field('sgdg_client_id', 'Client ID', ['Sgdg_plugin', 'client_id_html'], 'sgdg', 'sgdg_auth');
			add_settings_field('sgdg_client_secret', 'Client Secret', ['Sgdg_plugin', 'client_secret_html'], 'sgdg', 'sgdg_auth');
		}

		public static function settings_oauth_revoke() : void
		{
			add_settings_section('sgdg_auth', 'Step 1: Authentication', ['Sgdg_plugin', 'revoke_html'], 'sgdg');
			add_settings_field('sgdg_redirect_uri', 'Authorized redirect URL', ['Sgdg_plugin', 'redirect_uri_html'], 'sgdg', 'sgdg_auth');
			add_settings_field('sgdg_client_id', 'Client ID', ['Sgdg_plugin', 'client_id_html_readonly'], 'sgdg', 'sgdg_auth');
			add_settings_field('sgdg_client_secret', 'Client Secret', ['Sgdg_plugin', 'client_secret_html_readonly'], 'sgdg', 'sgdg_auth');
		}

		public static function settings_root_selection() : void
		{
			if(get_option('sgdg_access_token'))
			{
				add_settings_section('sgdg_dir_select', 'Step 2: Root directory selection', ['Sgdg_plugin', 'dir_select_html'], 'sgdg');
			}
		}

		public static function enqueue_ajax($hook) : void
		{
			if($hook === 'settings_page_sgdg')
			{
				wp_enqueue_script('sgdg_root_selector_ajax', plugins_url('/js/root_selector.js', __FILE__), ['jquery']);
				wp_localize_script('sgdg_root_selector_ajax', 'sgdg_jquery_localize', [
					'ajax_url' => admin_url('admin-ajax.php'),
					'nonce' => wp_create_nonce('sgdg_root_selector'),
					'root_dir' => get_option('sgdg_root_dir', [])
				]);
			}
		}
		public static function handle_ajax_list_gdrive_dir() : void
		{
			check_ajax_referer('sgdg_root_selector');

			$client = self::getDriveClient();
			$path = isset($_GET['path']) ? $_GET['path'] : [];
			$root = 'root';
			if(isset($_GET['path']))
			{
				$root = end($path);
			}
			$ret = ['path' => [], 'contents' => []];

			foreach($path as $pathElement)
			{
				$response = $client->files->get($pathElement, ['fields' => 'name']);
				$ret['path'][] = $response->getName();
			}

			$pageToken = null;
			do
			{
				$optParams = [
					'q' => '"' . $root . '" in parents and mimeType = "application/vnd.google-apps.folder" and trashed = false',
					'pageToken' => $pageToken,
					'pageSize' => 1000,
					'fields' => 'nextPageToken, files(id, name)'
				];
				$response = $client->files->listFiles($optParams);
				foreach($response->getFiles() as $file)
				{
					$ret['contents'][] = ['name' => $file->getName(), 'id' => $file->getId()];
				}
				$pageToken = $response->pageToken;
			}
			while($pageToken != null);

			wp_send_json($ret);
		}

		public static function options_page() : void
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
			echo('<div class="wrap">');
			echo('<h1>' . esc_html(get_admin_page_title()) . '</h1>');
			echo('<form action="options.php" method="post">');
			settings_fields('sgdg');
			do_settings_sections('sgdg');
			submit_button('Save Settings');
			echo('</form>');
			echo('</div>');
		}

		public static function auth_html() : void
		{
			echo('<p>Create a Google app and provide the following details:</p>');
			echo('<a class="button button-primary" href="' . esc_url_raw(admin_url('options-general.php?page=sgdg&action=oauth_grant')) . '">Grant Permission</a>');
		}

		public static function revoke_html() : void
		{
			echo('<a class="button button-primary" href="' . esc_url_raw(admin_url('options-general.php?page=sgdg&action=oauth_revoke')) . '">Revoke Permission</a>');
		}

		public static function dir_select_html() : void
		{
			echo('<input id="sgdg_root_dir" type="hidden" name="sgdg_root_dir" value="' . htmlentities(json_encode(get_option('sgdg_root_dir', []), JSON_UNESCAPED_UNICODE)) . '">');
			echo('<table class="widefat">');
			echo('<thead>');
			echo('<tr>');
			echo('<th class="sgdg_root_selector_path"></th>');
			echo('</tr>');
			echo('</thead>');
			echo('<tbody id="sgdg_root_selector_body"></tbody>');
			echo('<tfoot>');
			echo('<tr>');
			echo('<td class="sgdg_root_selector_path"></td>');
			echo('</tr>');
			echo('</tfoot>');
			echo('</table>');
		}

		public static function client_id_html() : void
		{
			self::field_html('sgdg_client_id');
		}

		public static function client_secret_html() : void
		{
			self::field_html('sgdg_client_secret');
		}

		public static function client_id_html_readonly() : void
		{
			self::field_html('sgdg_client_id', true);
		}

		public static function client_secret_html_readonly() : void
		{
			self::field_html('sgdg_client_secret', true);
		}

		private static function field_html(string $setting_name, bool $readonly = false) : void
		{
			$setting = get_option($setting_name);
			echo('<input type="text" name="' . $setting_name . '" value="' . (isset($setting) ? esc_attr($setting) : '') . '" ' . ($readonly ? 'readonly ' : '') . 'class="regular-text code">');
		}

		public static function redirect_uri_html() : void
		{
			echo('<input type="text" value="' . esc_url_raw(admin_url('options-general.php?page=sgdg&action=oauth_redirect')) . '" readonly class="regular-text code">');
		}

		public static function decode_root_dir($path) : array
		{
			return json_decode($path, true);
		}
	}

	Sgdg_plugin::init();
}
