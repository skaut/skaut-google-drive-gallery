<?php declare(strict_types=1);
/*
Plugin Name:	Skaut Google drive gallery
Plugin URI:     https://github.com/skaut/skaut-google-drive-gallery/
Description:	A Wordpress gallery using Google drive as file storage
Version:	0.1
Author:		Marek Dědič
Author URI:
License:	MIT
License URI:	https://raw.githubusercontent.com/skaut/skaut-google-drive-gallery/master/LICENSE.md
Text Domain:	skaut-google-drive-gallery
Domain Path:	/languages

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

require_once('bundled/vendor_includes.php');
require_once('Frontend/GoogleAPILib.php');
require_once('Frontend/Shortcode.php');
require_once('Admin/GoogleAPILib.php');
require_once('Admin/Option.php');
require_once('Admin/IntegerOption.php');

if(!class_exists('Sgdg_plugin'))
{
	class Sgdg_plugin
	{
		public static $thumbnailSize;
		const DEFAULT_THUMBNAIL_SPACING = 10;
		const DEFAULT_PREVIEW_SIZE = 1920;
		const DEFAULT_PREVIEW_SPEED = 250;
		const DEFAULT_PREVIEW_ARROWS = '1';
		const DEFAULT_PREVIEW_CLOSEBUTTON = '1';
		const DEFAULT_PREVIEW_LOOP = '0';
		const DEFAULT_PREVIEW_ACTIVITY = '1';

		public static function init() : void
		{
			self::$thumbnailSize = new \Sgdg\Admin\IntegerOption('thumbnail_size', 250, 'options', 'Thumbnail size');
			add_action('plugins_loaded', ['Sgdg_plugin', 'load_textdomain']);
			add_action('init', '\\Sgdg\\Frontend\\Shortcode\\register');
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
				add_action('admin_init', ['Sgdg_plugin', 'settings_other_options']);
				add_action('admin_enqueue_scripts', ['Sgdg_plugin', 'enqueue_ajax']);
				add_action('wp_ajax_list_gdrive_dir', ['Sgdg_plugin', 'handle_ajax_list_gdrive_dir']);
			}
		}

		public static function load_textdomain() : void
		{
			load_plugin_textdomain('skaut-google-drive-gallery', false, basename( dirname( __FILE__ ) ) . '/languages/' );
		}

		public static function register_scripts_styles() : void
		{
			wp_register_script('sgdg_masonry', plugins_url('/bundled/masonry.pkgd.min.js', __FILE__), ['jquery']);
			wp_register_script('sgdg_imagesloaded', plugins_url('/bundled/imagesloaded.pkgd.min.js', __FILE__), ['jquery']);
			wp_register_script('sgdg_imagelightbox_script', plugins_url('/bundled/imagelightbox.min.js', __FILE__), ['jquery']);
			wp_register_script('sgdg_gallery_init', plugins_url('/js/gallery_init.js', __FILE__), ['jquery']);
			wp_register_style('sgdg_imagelightbox_style', plugins_url('/bundled/imagelightbox.min.css', __FILE__));
			wp_register_style('sgdg_gallery_css', plugins_url('/css/gallery.css', __FILE__));
		}

		public static function action_handler() : void
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

		public static function register_settings() : void
		{
			register_setting('sgdg', 'sgdg_client_id', ['type' => 'string']);
			register_setting('sgdg', 'sgdg_client_secret', ['type' => 'string']);
			register_setting('sgdg', 'sgdg_root_dir', ['type' => 'string', 'sanitize_callback' => ['Sgdg_plugin', 'decode_root_dir']]);
			self::$thumbnailSize->register();
			register_setting('sgdg', 'sgdg_thumbnail_spacing', ['type' => 'integer', 'sanitize_callback' => ['Sgdg_plugin', 'sanitize_thumbnail_spacing']]);
			register_setting('sgdg', 'sgdg_preview_size', ['type' => 'integer', 'sanitize_callback' => ['Sgdg_plugin', 'sanitize_preview_size']]);
			register_setting('sgdg', 'sgdg_preview_speed', ['type' => 'integer', 'sanitize_callback' => ['Sgdg_plugin', 'sanitize_preview_speed']]);
			register_setting('sgdg', 'sgdg_preview_arrows', ['type' => 'boolean', 'sanitize_callback' => ['Sgdg_plugin', 'sanitize_bool']]);
			register_setting('sgdg', 'sgdg_preview_closebutton', ['type' => 'boolean', 'sanitize_callback' => ['Sgdg_plugin', 'sanitize_bool']]);
			register_setting('sgdg', 'sgdg_preview_loop', ['type' => 'boolean', 'sanitize_callback' => ['Sgdg_plugin', 'sanitize_bool']]);
			register_setting('sgdg', 'sgdg_preview_activity', ['type' => 'boolean', 'sanitize_callback' => ['Sgdg_plugin', 'sanitize_bool']]);
		}

		public static function settings_oauth_grant() : void
		{
			add_settings_section('sgdg_auth', esc_html__('Step 1: Authorization', 'skaut-google-drive-gallery'), ['Sgdg_plugin', 'auth_html'], 'sgdg');
			add_settings_field('sgdg_redirect_uri', esc_html__('Authorized redirect URL', 'skaut-google-drive-gallery'), ['Sgdg_plugin', 'redirect_uri_html'], 'sgdg', 'sgdg_auth');
			add_settings_field('sgdg_client_id', esc_html__('Client ID', 'skaut-google-drive-gallery'), ['Sgdg_plugin', 'client_id_html'], 'sgdg', 'sgdg_auth');
			add_settings_field('sgdg_client_secret', esc_html__('Client Secret', 'skaut-google-drive-gallery'), ['Sgdg_plugin', 'client_secret_html'], 'sgdg', 'sgdg_auth');
		}

		public static function settings_oauth_revoke() : void
		{
			add_settings_section('sgdg_auth', esc_html__('Step 1: Authorization', 'skaut-google-drive-gallery'), ['Sgdg_plugin', 'revoke_html'], 'sgdg');
			add_settings_field('sgdg_redirect_uri', esc_html__('Authorized redirect URL', 'skaut-google-drive-gallery'), ['Sgdg_plugin', 'redirect_uri_html'], 'sgdg', 'sgdg_auth');
			add_settings_field('sgdg_client_id', esc_html__('Client ID', 'skaut-google-drive-gallery'), ['Sgdg_plugin', 'client_id_html_readonly'], 'sgdg', 'sgdg_auth');
			add_settings_field('sgdg_client_secret', esc_html__('Client Secret', 'skaut-google-drive-gallery'), ['Sgdg_plugin', 'client_secret_html_readonly'], 'sgdg', 'sgdg_auth');
		}

		public static function settings_root_selection() : void
		{
			add_settings_section('sgdg_dir_select', esc_html__('Step 2: Root directory selection', 'skaut-google-drive-gallery'), ['Sgdg_plugin', 'dir_select_html'], 'sgdg');
		}

		public static function settings_other_options() : void
		{
			add_settings_section('sgdg_options', esc_html__('Step 3: Other options', 'skaut-google-drive-gallery'), ['Sgdg_plugin', 'other_options_html'], 'sgdg');
			self::$thumbnailSize->add_field();
			add_settings_field('sgdg_thumbnail_spacing', esc_html__('Thumbnail spacing', 'skaut-google-drive-gallery'), ['Sgdg_plugin', 'thumbnail_spacing_html'], 'sgdg', 'sgdg_options');
			add_settings_field('sgdg_preview_size', esc_html__('Preview size', 'skaut-google-drive-gallery'), ['Sgdg_plugin', 'preview_size_html'], 'sgdg', 'sgdg_options');
			add_settings_field('sgdg_preview_speed', esc_html__('Preview animation speed (ms)', 'skaut-google-drive-gallery'), ['Sgdg_plugin', 'preview_speed_html'], 'sgdg', 'sgdg_options');
			add_settings_field('sgdg_preview_arrows', esc_html__('Preview arrows', 'skaut-google-drive-gallery'), ['Sgdg_plugin', 'preview_arrows_html'], 'sgdg', 'sgdg_options');
			add_settings_field('sgdg_preview_closebutton', esc_html__('Preview close button', 'skaut-google-drive-gallery'), ['Sgdg_plugin', 'preview_closebutton_html'], 'sgdg', 'sgdg_options');
			add_settings_field('sgdg_preview_loop', esc_html__('Loop preview', 'skaut-google-drive-gallery'), ['Sgdg_plugin', 'preview_loop_html'], 'sgdg', 'sgdg_options');
			add_settings_field('sgdg_preview_activity', esc_html__('Preview activity indicator', 'skaut-google-drive-gallery'), ['Sgdg_plugin', 'preview_activity_html'], 'sgdg', 'sgdg_options');
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

			$client = \Sgdg\Frontend\GoogleAPILib\getDriveClient();
			$path = isset($_GET['path']) ? $_GET['path'] : [];
			$ret = ['path' => [], 'contents' => []];

			if(count($path) > 0)
			{
				if($path[0] === 'root')
				{
					$ret['path'][] = esc_html__('My Drive', 'skaut-google-drive-gallery');
				}
				else
				{
					$response = $client->teamdrives->get($path[0], ['fields' => 'name']);
					$ret['path'][] = $response->getName();
				}
			}
			foreach(array_slice($path, 1) as $pathElement)
			{
				$response = $client->files->get($pathElement, ['supportsTeamDrives' => true, 'fields' => 'name']);
				$ret['path'][] = $response->getName();
			}

			if(count($path) === 0)
			{
				$ret['contents'][] = ['name' => esc_html__('My Drive', 'skaut-google-drive-gallery'), 'id' => 'root'];
				$pageToken = null;
				do
				{
					$optParams = [
						'pageToken' => $pageToken,
						'pageSize' => 100,
						'fields' => 'nextPageToken, teamDrives(id, name)'
					];
					$response = $client->teamdrives->listTeamdrives($optParams);
					foreach($response->getTeamdrives() as $teamdrive)
					{
						$ret['contents'][] = ['name' => $teamdrive->getName(), 'id' => $teamdrive->getId()];
					}
					$pageToken = $response->pageToken;
				}
				while($pageToken != null);

				wp_send_json($ret);
			}

			$root = end($path);

			$pageToken = null;
			do
			{
				$optParams = [
					'q' => '"' . $root . '" in parents and mimeType = "application/vnd.google-apps.folder" and trashed = false',
					'supportsTeamDrives' => true,
					'includeTeamDriveItems' => true,
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
			add_options_page(esc_html__('Google drive gallery', 'skaut-google-drive-gallery'), esc_html__('Google drive gallery', 'skaut-google-drive-gallery'), 'manage_options', 'sgdg', ['Sgdg_plugin', 'options_page_html']);
		}

		public static function options_page_html() : void
		{
			if (!current_user_can('manage_options'))
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

		public static function auth_html() : void
		{
			echo('<p>' . __('Create a Google app and provide the following details:', 'skaut-google-drive-gallery') . '</p>');
			echo('<a class="button button-primary" href="' . esc_url_raw(admin_url('options-general.php?page=sgdg&action=oauth_grant')) . '">' . esc_html__('Grant Permission', 'skaut-google-drive-gallery') . '</a>');
		}

		public static function revoke_html() : void
		{
			echo('<a class="button button-primary" href="' . esc_url_raw(admin_url('options-general.php?page=sgdg&action=oauth_revoke')) . '">' . esc_html__('Revoke Permission', 'skaut-google-drive-gallery') . '</a>');
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

		public static function other_options_html() : void
		{}

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

		public static function thumbnail_spacing_html() : void
		{
			self::int_html('sgdg_thumbnail_spacing', self::DEFAULT_THUMBNAIL_SPACING);
		}

		public static function preview_size_html() : void
		{
			self::int_html('sgdg_preview_size', self::DEFAULT_PREVIEW_SIZE);
		}

		public static function preview_speed_html() : void
		{
			self::int_html('sgdg_preview_speed', self::DEFAULT_PREVIEW_SPEED);
		}

		public static function preview_arrows_html() : void
		{
			self::bool_html('sgdg_preview_arrows', self::DEFAULT_PREVIEW_ARROWS);
		}

		public static function preview_closebutton_html() : void
		{
			self::bool_html('sgdg_preview_closebutton', self::DEFAULT_PREVIEW_CLOSEBUTTON);
		}

		public static function preview_loop_html() : void
		{
			self::bool_html('sgdg_preview_loop', self::DEFAULT_PREVIEW_LOOP);
		}

		public static function preview_activity_html() : void
		{
			self::bool_html('sgdg_preview_activity', self::DEFAULT_PREVIEW_ACTIVITY);
		}

		private static function int_html(string $setting_name, int $default) : void
		{
			$setting = get_option($setting_name, $default);
			echo('<input type="text" name="' . $setting_name . '" value="' . esc_attr($setting) . '" class="regular-text">');
		}

		private static function bool_html(string $setting_name, string $default) : void
		{
			$setting = get_option($setting_name, $default);
			echo('<input type="checkbox" name="' . $setting_name . '" value="1"');
			checked($setting, '1');
			echo('>');
		}

		public static function decode_root_dir($path) : array
		{
			if(!is_array($path))
			{
				$path =  json_decode($path, true);
			}
			if(count($path) === 0)
			{
				$path = ['root'];
			}
			return $path;
		}

		public static function sanitize_thumbnail_spacing($size) : int
		{
			return self::sanitize_int($size, self::DEFAULT_THUMBNAIL_SPACING);
		}

		public static function sanitize_preview_size($size) : int
		{
			return self::sanitize_int($size, self::DEFAULT_PREVIEW_SIZE);
		}

		public static function sanitize_preview_speed($speed) : int
		{
			return self::sanitize_int($speed, self::DEFAULT_PREVIEW_SPEED);
		}

		public static function sanitize_bool($value) : int
		{
			if(isset($value) && ($value === '1' || $value === 1))
			{
				return 1;
			}
			return 0;
		}

		private static function sanitize_int($size, int $default) : int
		{
			if(is_numeric($size))
			{
				return intval($size);
			}
			return $default;
		}
	}

	Sgdg_plugin::init();
}
