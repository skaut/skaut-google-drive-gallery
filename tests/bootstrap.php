<?php
/**
 * PHPUnit bootstrap file
 *
 * @package skaut-google-drive-gallery
 */

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals

require_once dirname( __DIR__ ) . '/vendor/yoast/phpunit-polyfills/phpunitpolyfills-autoload.php';

$_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( '' === $_tests_dir || false === $_tests_dir ) {
	$_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

if ( ! file_exists( $_tests_dir . '/includes/functions.php' ) ) {
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo "Could not find $_tests_dir/includes/functions.php, have you run bin/install-wp-tests.sh ?";
	exit( 1 );
}

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

/**
 * Manually load the plugin being tested.
 *
 * @return void
 */
$_manually_load_plugin = static function () {
	require dirname( __DIR__ ) . '/dist/skaut-google-drive-gallery.php';
};
tests_add_filter( 'muplugins_loaded', $_manually_load_plugin );

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';
