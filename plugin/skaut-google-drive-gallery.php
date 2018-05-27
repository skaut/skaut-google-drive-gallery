<?php
namespace Sgdg;

/*
Plugin Name:	Google Drive gallery
Plugin URI:     https://github.com/skaut/skaut-google-drive-gallery/
Description:	A WordPress gallery using Google Drive as file storage
Version:		1.0.1
Author:			Marek Dědič
Author URI:		https://github.com/genabitu
License:		MIT
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

require_once 'bundled/vendor-includes.php';

require_once 'class-options.php';

require_once 'frontend/google-api-lib.php';
require_once 'frontend/shortcode.php';

require_once 'admin/google-api-lib.php';
require_once 'admin/options-page.php';
require_once 'admin/class-readonlystringoption.php';
require_once 'admin/tinymce.php';

function init() {
	register_activation_hook( __FILE__, '\\Sgdg\\activate' );
	add_action( 'plugins_loaded', [ '\\Sgdg\\Options', 'init' ] );
	\Sgdg\Frontend\Shortcode\register();
	\Sgdg\Admin\OptionsPage\register();
	\Sgdg\Admin\TinyMCE\register();
}

function activate() {
	if ( ! isset( $GLOBALS['wp_version'] ) || version_compare( $GLOBALS['wp_version'], '4.9.6', '<' ) ) {
		deactivate_plugins( plugin_basename( __FILE__ ) );
		wp_die( esc_html__( 'Google Drive gallery requires at least WordPress 4.9.6', 'skaut-google-drive-gallery' ) );
	}
	if ( version_compare( phpversion(), '5.6', '<' ) ) {
		deactivate_plugins( plugin_basename( __FILE__ ) );
		wp_die( esc_html__( 'Google Drive gallery requires at least PHP 5.6', 'skaut-google-drive-gallery' ) );
	}
}

init();
