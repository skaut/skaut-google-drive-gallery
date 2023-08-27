<?php
/**
 * Main plugin file
 *
 * Contains plugin init function, activation logic and some helpers for script/style enqueueing.
 *
 * @package skaut-google-drive-gallery
 */

/*
Plugin Name:       Image and video gallery from Google Drive
Plugin URI:        https://github.com/skaut/skaut-google-drive-gallery/
Description:       A WordPress gallery using Google Drive as file storage
Version:           2.13.4
Requires at least: 4.9.6
Requires PHP:      5.6
Author:            Junák - český skaut
Author URI:        https://github.com/skaut
License:           MIT
License URI:       https://github.com/skaut/skaut-google-drive-gallery/blob/master/LICENSE
Text Domain:       skaut-google-drive-gallery

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

require_once __DIR__ . '/helpers/class-get-helpers.php';
require_once __DIR__ . '/helpers/class-helpers.php';
require_once __DIR__ . '/helpers/class-script-and-style-helpers.php';

require_once __DIR__ . '/frontend/page/class-directories.php';
require_once __DIR__ . '/frontend/page/class-images.php';
require_once __DIR__ . '/frontend/page/class-videos.php';

require_once __DIR__ . '/frontend/interface-pagination-helper.php';
require_once __DIR__ . '/frontend/class-api-fields.php';
require_once __DIR__ . '/frontend/class-block.php';
require_once __DIR__ . '/frontend/class-gallery.php';
require_once __DIR__ . '/frontend/class-gallery-context.php';
require_once __DIR__ . '/frontend/class-paging-pagination-helper.php';
require_once __DIR__ . '/frontend/class-options-proxy.php';
require_once __DIR__ . '/frontend/class-page.php';
require_once __DIR__ . '/frontend/class-shortcode.php';
require_once __DIR__ . '/frontend/class-single-page-pagination-helper.php';
require_once __DIR__ . '/frontend/class-video-proxy.php';

require_once __DIR__ . '/admin/class-oauth-helpers.php';
require_once __DIR__ . '/admin/class-settings-pages.php';
require_once __DIR__ . '/admin/class-tinymce-plugin.php';

require_once __DIR__ . '/class-main.php';

new Main();
