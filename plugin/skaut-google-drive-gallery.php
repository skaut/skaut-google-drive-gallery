<?php
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

defined( 'ABSPATH' ) || die( 'Die, die, die!' );

require_once 'bundled/vendor_includes.php';

require_once 'frontend/google-api-lib.php';
require_once 'frontend/class-integeroption.php';
require_once 'frontend/class-booleanoption.php';
require_once 'frontend/class-stringcodeoption.php';
require_once 'frontend/class-arrayoption.php';
require_once 'frontend/class-rootpathoption.php';
require_once 'frontend/shortcode.php';

require_once 'admin/google-api-lib.php';
require_once 'admin/options-page.php';
require_once 'admin/class-readonlystringoption.php';

class Options {
	public static $authorized_origin;
	public static $redirect_uri;
	public static $client_id;
	public static $client_secret;
	public static $root_path;
	public static $thumbnail_size;
	public static $thumbnail_spacing;
	public static $preview_size;
	public static $preview_speed;
	public static $dir_counts;
	public static $preview_arrows;
	public static $preview_close_button;
	public static $preview_loop;
	public static $preview_activity_indicator;

	public static function init() {
		self::$authorized_origin          = new \Sgdg\Admin\ReadonlyStringOption( 'origin', get_site_url(), 'auth', esc_html__( 'Authorised JavaScript origin', 'skaut-google-drive-gallery' ) );
		self::$redirect_uri               = new \Sgdg\Admin\ReadonlyStringOption( 'redirect_uri', esc_url_raw( admin_url( 'options-general.php?page=sgdg&action=oauth_redirect' ) ), 'auth', esc_html__( 'Authorised redirect URI', 'skaut-google-drive-gallery' ) );
		self::$client_id                  = new \Sgdg\Frontend\StringCodeOption( 'client_id', '', 'auth', esc_html__( 'Client ID', 'skaut-google-drive-gallery' ) );
		self::$client_secret              = new \Sgdg\Frontend\StringCodeOption( 'client_secret', '', 'auth', esc_html__( 'Client secret', 'skaut-google-drive-gallery' ) );
		self::$root_path                  = new \Sgdg\Frontend\RootPathOption( 'root_path', [ 'root' ], 'root_selection', '' );
		self::$thumbnail_size             = new \Sgdg\Frontend\IntegerOption( 'thumbnail_size', 250, 'options', esc_html__( 'Thumbnail size', 'skaut-google-drive-gallery' ) );
		self::$thumbnail_spacing          = new \Sgdg\Frontend\IntegerOption( 'thumbnail_spacing', 10, 'options', esc_html__( 'Thumbnail spacing', 'skaut-google-drive-gallery' ) );
		self::$preview_size               = new \Sgdg\Frontend\IntegerOption( 'preview_size', 1920, 'options', esc_html__( 'Preview size', 'skaut-google-drive-gallery' ) );
		self::$preview_speed              = new \Sgdg\Frontend\IntegerOption( 'preview_speed', 250, 'options', esc_html__( 'Preview animation speed (ms)', 'skaut-google-drive-gallery' ) );
		self::$dir_counts                 = new \Sgdg\Frontend\BooleanOption( 'dir_counts', true, 'options', esc_html__( 'Directory item counts', 'skaut-google-drive-gallery' ) );
		self::$preview_arrows             = new \Sgdg\Frontend\BooleanOption( 'preview_arrows', true, 'options', esc_html__( 'Preview arrows', 'skaut-google-drive-gallery' ) );
		self::$preview_close_button       = new \Sgdg\Frontend\BooleanOption( 'preview_closebutton', true, 'options', esc_html__( 'Preview close button', 'skaut-google-drive-gallery' ) );
		self::$preview_loop               = new \Sgdg\Frontend\BooleanOption( 'preview_loop', false, 'options', esc_html__( 'Loop preview', 'skaut-google-drive-gallery' ) );
		self::$preview_activity_indicator = new \Sgdg\Frontend\BooleanOption( 'preview_activity', true, 'options', esc_html__( 'Preview activity indicator', 'skaut-google-drive-gallery' ) );
	}
}

function init() {
	register_activation_hook( __FILE__, '\\Sgdg\\activate' );
	add_action( 'plugins_loaded', [ '\\Sgdg\\Options', 'init' ] );
	\Sgdg\Frontend\Shortcode\register();
	\Sgdg\Admin\OptionsPage\register();
}

function activate() {
	if ( ! isset( $GLOBALS['wp_version'] ) || version_compare( $GLOBALS['wp_version'], '4.9.6', '<' ) ) {
		deactivate_plugins( plugin_basename( __FILE__ ) );
		wp_die( esc_html__( 'Google drive gallery requires at least WordPress 4.9.6', 'skaut-google-drive-gallery' ) );
	}
	if ( version_compare( phpversion(), '5.6', '<' ) ) {
		deactivate_plugins( plugin_basename( __FILE__ ) );
		wp_die( esc_html__( 'Google drive gallery requires at least PHP 5.6', 'skaut-google-drive-gallery' ) );
	}
}

init();
