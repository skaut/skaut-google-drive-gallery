<?php declare(strict_types=1);
namespace Sgdg;
/*
Plugin Name:	Google drive gallery
Plugin URI:     https://github.com/skaut/skaut-google-drive-gallery/
Description:	A WordPress gallery using Google drive as file storage
Version:	0.1.0-beta
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
require_once('Admin/ReadonlyStringOption.php');

class Options
{
	public static $authorizedOrigin;
	public static $redirectURI;
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
		self::$authorizedOrigin = new \Sgdg\Admin\ReadonlyStringOption('origin', get_site_url(), 'auth', esc_html__('Authorised JavaScript origin', 'skaut-google-drive-gallery'));
		self::$redirectURI = new \Sgdg\Admin\ReadonlyStringOption('redirect_uri', esc_url_raw(admin_url('options-general.php?page=sgdg&action=oauth_redirect')), 'auth', esc_html__('Authorised redirect URI', 'skaut-google-drive-gallery'));
		self::$clientID = new \Sgdg\Frontend\StringCodeOption('client_id', '', 'auth', esc_html__('Client ID', 'skaut-google-drive-gallery'));
		self::$clientSecret = new \Sgdg\Frontend\StringCodeOption('client_secret', '', 'auth', esc_html__('Client secret', 'skaut-google-drive-gallery'));
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
		self::$thumbnailSize = new \Sgdg\Frontend\IntegerOption('thumbnail_size', 250, 'options', esc_html__('Thumbnail size', 'skaut-google-drive-gallery'));
		self::$thumbnailSpacing = new \Sgdg\Frontend\IntegerOption('thumbnail_spacing', 10, 'options', esc_html__('Thumbnail spacing', 'skaut-google-drive-gallery'));
		self::$previewSize = new \Sgdg\Frontend\IntegerOption('preview_size', 1920, 'options', esc_html__('Preview size', 'skaut-google-drive-gallery'));
		self::$previewSpeed = new \Sgdg\Frontend\IntegerOption('preview_speed', 250, 'options', esc_html__('Preview animation speed (ms)', 'skaut-google-drive-gallery'));
		self::$previewArrows = new \Sgdg\Frontend\BooleanOption('preview_arrows', true, 'options', esc_html__('Preview arrows', 'skaut-google-drive-gallery'));
		self::$previewCloseButton = new \Sgdg\Frontend\BooleanOption('preview_closebutton', true, 'options', esc_html__('Preview close button', 'skaut-google-drive-gallery'));
		self::$previewLoop = new \Sgdg\Frontend\BooleanOption('preview_loop', false, 'options', esc_html__('Loop preview', 'skaut-google-drive-gallery'));
		self::$previewActivity = new \Sgdg\Frontend\BooleanOption('preview_activity', true, 'options', esc_html__('Preview activity indicator', 'skaut-google-drive-gallery'));
	}
}

function init() : void
{
	Options::init();
	add_action('plugins_loaded', '\\Sgdg\\load_textdomain');
	\Sgdg\Frontend\Shortcode\register();
	\Sgdg\Admin\OptionsPage\register();
}

function load_textdomain() : void
{
	load_plugin_textdomain('skaut-google-drive-gallery', false, basename( dirname( __FILE__ ) ) . '/languages/' );
}

init();
