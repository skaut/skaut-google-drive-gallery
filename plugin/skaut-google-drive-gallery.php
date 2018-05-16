<?php declare(strict_types=1);
/*
Plugin Name:	Google drive gallery
Plugin URI:     https://github.com/skaut/skaut-google-drive-gallery/
Description:	A WordPress gallery using Google drive as file storage
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
require_once('Frontend/ArrayOption.php');
require_once('Frontend/Shortcode.php');

require_once('Admin/GoogleAPILib.php');
require_once('Admin/OptionsPage.php');

if(!class_exists('Sgdg_plugin'))
{
	class Sgdg_plugin
	{
		public static $clientID;
		public static $clientSecret;
		public static $rootPath;
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
			self::$rootPath = new class('root_path', ['root'], 'root_selection', '') extends \Sgdg\Frontend\ArrayOption
			{
				public function sanitize($value) : array
				{
					$value = parent::sanitize($value);
					if(count($value) === 0)
					{
						$value = $this->defaultValue;
					}
					return $value;
				}
			};
			self::$thumbnailSize = new \Sgdg\Frontend\IntegerOption('thumbnail_size', 250, 'options', 'Thumbnail size');
			self::$thumbnailSpacing = new \Sgdg\Frontend\IntegerOption('thumbnail_spacing', 10, 'options', 'Thumbnail spacing');
			self::$previewSize = new \Sgdg\Frontend\IntegerOption('preview_size', 1920, 'options', 'Preview size');
			self::$previewSpeed = new \Sgdg\Frontend\IntegerOption('preview_speed', 250, 'options', 'Preview animation speed');
			self::$previewArrows = new \Sgdg\Frontend\BooleanOption('preview_arrows', true, 'options', 'Preview arrows');
			self::$previewCloseButton = new \Sgdg\Frontend\BooleanOption('preview_closebutton', true, 'options', 'Preview close button');
			self::$previewLoop = new \Sgdg\Frontend\BooleanOption('preview_loop', false, 'options', 'Loop preview');
			self::$previewActivity = new \Sgdg\Frontend\BooleanOption('preview_activity', true, 'options', 'Preview activity indicator');
			add_action('plugins_loaded', ['Sgdg_plugin', 'load_textdomain']);
			\Sgdg\Frontend\Shortcode\register();
			\Sgdg\Admin\OptionsPage\register();
			add_action('wp_enqueue_scripts', ['Sgdg_plugin', 'register_scripts_styles']);
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

		public static function redirect_uri_html() : void
		{
			echo('<input type="text" value="' . esc_url_raw(admin_url('options-general.php?page=sgdg&action=oauth_redirect')) . '" readonly class="regular-text code">');
		}
	}

	Sgdg_plugin::init();
}
