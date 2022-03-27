<?php
/**
 * Main plugin file
 *
 * Contains plugin init function, activation logic and some helpers for script/style enqueueing.
 *
 * @package skaut-google-drive-gallery
 */

/*
Plugin Name:	Image and video gallery from Google Drive
Plugin URI:		https://github.com/skaut/skaut-google-drive-gallery/
Description:	A WordPress gallery using Google Drive as file storage
Version:		2.12.0
Author:			Junák - český skaut
Author URI:		https://github.com/skaut
License:		MIT
License URI:	https://github.com/skaut/skaut-google-drive-gallery/blob/master/LICENSE
Text Domain:	skaut-google-drive-gallery

MIT License

Copyright (c) Marek Dědič

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

namespace Sgdg;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Die, die, die!' );
}

require_once __DIR__ . '/vendor/scoper-autoload.php';

require_once __DIR__ . '/class-options.php';
require_once __DIR__ . '/class-api-client.php';
require_once __DIR__ . '/class-api-facade.php';

require_once __DIR__ . '/exceptions/class-exception.php';
require_once __DIR__ . '/exceptions/class-api-exception.php';
require_once __DIR__ . '/exceptions/class-api-rate-limit-exception.php';
require_once __DIR__ . '/exceptions/class-cant-edit-exception.php';
require_once __DIR__ . '/exceptions/class-cant-manage-exception.php';
require_once __DIR__ . '/exceptions/class-directory-not-found-exception.php';
require_once __DIR__ . '/exceptions/class-drive-not-found-exception.php';
require_once __DIR__ . '/exceptions/class-file-not-found-exception.php';
require_once __DIR__ . '/exceptions/class-gallery-expired-exception.php';
require_once __DIR__ . '/exceptions/class-internal-exception.php';
require_once __DIR__ . '/exceptions/class-not-found-exception.php';
require_once __DIR__ . '/exceptions/class-path-not-found-exception.php';
require_once __DIR__ . '/exceptions/class-plugin-not-authorized-exception.php';
require_once __DIR__ . '/exceptions/class-root-not-found-exception.php';
require_once __DIR__ . '/exceptions/class-unsupported-value-exception.php';

require_once __DIR__ . '/frontend/interface-pagination-helper-interface.php';
require_once __DIR__ . '/frontend/class-api-fields.php';
require_once __DIR__ . '/frontend/class-block.php';
require_once __DIR__ . '/frontend/class-gallery.php';
require_once __DIR__ . '/frontend/class-infinite-pagination-helper.php';
require_once __DIR__ . '/frontend/class-pagination-helper.php';
require_once __DIR__ . '/frontend/class-options-proxy.php';
require_once __DIR__ . '/frontend/class-page.php';
require_once __DIR__ . '/frontend/class-shortcode.php';
require_once __DIR__ . '/frontend/class-single-page-pagination-helper.php';

require_once __DIR__ . '/admin/class-oauth-helpers.php';
require_once __DIR__ . '/admin/class-settings-pages.php';
require_once __DIR__ . '/admin/class-tinymce-plugin.php';

/**
 * Initializes the plugin
 *
 * @return void
 */
function init() {
	register_activation_hook( __FILE__, '\\Sgdg\\activate' );
	add_action( 'plugins_loaded', array( '\\Sgdg\\Options', 'init' ) );
	add_action( 'admin_notices', '\\Sgdg\\activation_notice' );
	new \Sgdg\Frontend\Shortcode();
	new \Sgdg\Frontend\Block();
	new \Sgdg\Frontend\Page();
	new \Sgdg\Frontend\Gallery();
	new \Sgdg\Admin\Settings_Pages();
	new \Sgdg\Admin\TinyMCE_Plugin();
}

/**
 * Plugin activation function
 *
 * This function is called on plugin activation (i.e. usually once right after the user has installed the plugin). It checks whether the version of PHP and WP is sufficient and deactivates the plugin if they aren't.
 *
 * @return void
 */
function activate() {
	if ( ! isset( $GLOBALS['wp_version'] ) || version_compare( $GLOBALS['wp_version'], '4.9.6', '<' ) ) {
		deactivate_plugins( plugin_basename( __FILE__ ) );
		wp_die( esc_html__( 'Google Drive gallery requires at least WordPress 4.9.6', 'skaut-google-drive-gallery' ) );
	}
	if ( version_compare( phpversion(), '5.6', '<' ) ) {
		deactivate_plugins( plugin_basename( __FILE__ ) );
		wp_die( esc_html__( 'Google Drive gallery requires at least PHP 5.6', 'skaut-google-drive-gallery' ) );
	}
	set_transient( 'sgdg_activation_notice', true, 30 );
}

/**
 * Renders the post-activation notice
 *
 * This function is called after the plugin has been successfully activated and points the user to the docs.
 *
 * @return void
 */
function activation_notice() {
	if ( false !== get_transient( 'sgdg_activation_notice' ) ) {
		echo( '<div class="notice notice-info is-dismissible"><p>' );
		$help_link = 'https://napoveda.skaut.cz/dobryweb/' . substr( get_locale(), 0, 2 ) . '-skaut-google-drive-gallery';
		/* translators: 1: Start of a link to the settings 2: End of the link to the settings 3: Start of a help link 4: End of the help link */
		printf( esc_html__( 'Google Drive gallery needs to be %1$sconfigured%2$s before it can be used. See the %3$sdocumentation%4$s for more information.', 'skaut-google-drive-gallery' ), '<a href="' . esc_url( admin_url( 'admin.php?page=sgdg_basic' ) ) . '">', '</a>', '<a href="' . esc_url( $help_link ) . '" target="_blank">', '</a>' );
		echo( '</p></div>' );
		delete_transient( 'sgdg_activation_notice' );
	}
}

/**
 * Safely loads a string GET variable
 *
 * This function loads a GET variable, runs it through all the required WordPress sanitization and returns it.
 *
 * @param string $name The name of the GET variable.
 * @param string $default The default value to use if the GET variable doesn't exist. Default empty string.
 *
 * @return string The GET variable value
 */
function safe_get_string_variable( $name, $default = '' ) {
	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.NonceVerification.Recommended
	return isset( $_GET[ $name ] ) ? sanitize_text_field( wp_unslash( strval( $_GET[ $name ] ) ) ) : $default;
}

/**
 * Safely loads an integer GET variable
 *
 * This function loads a GET variable, runs it through all the required WordPress sanitization and returns it.
 *
 * @param string $name The name of the GET variable.
 * @param int    $default The default value to use if the GET variable doesn't exist.
 *
 * @return int The GET variable value
 */
function safe_get_int_variable( $name, $default ) {
	$string_value = safe_get_string_variable( $name );
	return '' !== $string_value ? intval( $string_value ) : $default;
}

/**
 * Safely loads an array GET variable
 *
 * This function loads a GET variable, runs it through all the required WordPress sanitization and returns it.
 *
 * @param string        $name The name of the GET variable.
 * @param array<string> $default The default value to use if the GET variable doesn't exist. Default empty array.
 *
 * @return array<string> The GET variable value
 */
function safe_get_array_variable( $name, $default = array() ) {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	return isset( $_GET[ $name ] ) ? array_map( 'sanitize_text_field', wp_unslash( (array) $_GET[ $name ] ) ) : $default; // @phpstan-ignore-line
}

/**
 * Checks whether debug info should be displayed
 *
 * @return bool True to display debug info.
 */
function is_debug_display() {
	if ( defined( 'WP_DEBUG' ) && defined( 'WP_DEBUG_DISPLAY' ) ) {
		return \WP_DEBUG === true && \WP_DEBUG_DISPLAY === true;
	}
	return false;
}

init();
