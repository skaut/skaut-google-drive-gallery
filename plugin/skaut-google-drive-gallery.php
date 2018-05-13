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
require_once('Frontend/IntegerOption.php');
require_once('Frontend/BooleanOption.php');
require_once('Frontend/StringCodeOption.php');
require_once('Frontend/Shortcode.php');

require_once('Admin/GoogleAPILib.php');

if(!class_exists('Sgdg_plugin'))
{
	class Sgdg_plugin
	{
		public static $clientID;
		public static $clientSecret;
		public static $thumbnailSize;
		public static $thumbnailSpacing;
		public static $previewSize;
		public static $previewSpeed;
		public static $previewArrows;
		public static $previewCloseButton;
		public static $previewLoop;
		public static $previewActivity;

		public static function init() : void
		{
			self::$clientID = new \Sgdg\Frontend\StringCodeOption('client_id', '', 'auth', 'Client ID');
			self::$clientSecret = new \Sgdg\Frontend\StringCodeOption('client_secret', '', 'auth', 'Client secret');
			self::$thumbnailSize = new \Sgdg\Frontend\IntegerOption('thumbnail_size', 250, 'options', 'Thumbnail size');
			self::$thumbnailSpacing = new \Sgdg\Frontend\IntegerOption('thumbnail_spacing', 10, 'options', 'Thumbnail spacing');
			self::$previewSize = new \Sgdg\Frontend\IntegerOption('preview_size', 1920, 'options', 'Preview size');
			self::$previewSpeed = new \Sgdg\Frontend\IntegerOption('preview_speed', 250, 'options', 'Preview animation speed');
			self::$previewArrows = new \Sgdg\Frontend\BooleanOption('preview_arrows', true, 'options', 'Preview arrows');
			self::$previewCloseButton = new \Sgdg\Frontend\BooleanOption('preview_closebutton', true, 'options', 'Preview close button');
			self::$previewLoop = new \Sgdg\Frontend\BooleanOption('preview_loop', false, 'options', 'Loop preview');
			self::$previewActivity = new \Sgdg\Frontend\BooleanOption('preview_activity', true, 'options', 'Preview activity indicator');
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
			register_setting('sgdg', 'sgdg_root_dir', ['type' => 'string', 'sanitize_callback' => ['Sgdg_plugin', 'decode_root_dir']]);
		}

		public static function settings_oauth_grant() : void
		{
			add_settings_section('sgdg_auth', esc_html__('Step 1: Authorization', 'skaut-google-drive-gallery'), ['Sgdg_plugin', 'auth_html'], 'sgdg');
			add_settings_field('sgdg_redirect_uri', esc_html__('Authorized redirect URL', 'skaut-google-drive-gallery'), ['Sgdg_plugin', 'redirect_uri_html'], 'sgdg', 'sgdg_auth');
			self::$clientID->add_field();
			self::$clientSecret->add_field();
		}

		public static function settings_oauth_revoke() : void
		{
			add_settings_section('sgdg_auth', esc_html__('Step 1: Authorization', 'skaut-google-drive-gallery'), ['Sgdg_plugin', 'revoke_html'], 'sgdg');
			add_settings_field('sgdg_redirect_uri', esc_html__('Authorized redirect URL', 'skaut-google-drive-gallery'), ['Sgdg_plugin', 'redirect_uri_html'], 'sgdg', 'sgdg_auth');
			self::$clientID->add_field(true);
			self::$clientSecret->add_field(true);
		}

		public static function settings_root_selection() : void
		{
			add_settings_section('sgdg_dir_select', esc_html__('Step 2: Root directory selection', 'skaut-google-drive-gallery'), ['Sgdg_plugin', 'dir_select_html'], 'sgdg');
		}

		public static function settings_other_options() : void
		{
			add_settings_section('sgdg_options', esc_html__('Step 3: Other options', 'skaut-google-drive-gallery'), ['Sgdg_plugin', 'other_options_html'], 'sgdg');
			self::$thumbnailSize->add_field();
			self::$thumbnailSpacing->add_field();
			self::$previewSize->add_field();
			self::$previewSpeed->add_field();
			self::$previewArrows->add_field();
			self::$previewCloseButton->add_field();
			self::$previewLoop->add_field();
			self::$previewActivity->add_field();
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

		public static function redirect_uri_html() : void
		{
			echo('<input type="text" value="' . esc_url_raw(admin_url('options-general.php?page=sgdg&action=oauth_redirect')) . '" readonly class="regular-text code">');
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
	}

	Sgdg_plugin::init();
}
