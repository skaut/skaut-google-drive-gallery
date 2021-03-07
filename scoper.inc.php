<?php

use Isolated\Symfony\Component\Finder\Finder;

return array(
	'prefix'                     => 'Sgdg\\Vendor',
	'finders'                    => array(
		Finder::create()->files()
			->in( 'dist/bundled/vendor' ),
	),
	'patchers'                   => array(
		function ( $file_path, $prefix, $contents ) {
			$regex_prefix = mb_ereg_replace( '\\\\', '\\\\\\\\', $prefix );
			$prefix = mb_ereg_replace( '\\\\', '\\\\', $prefix );
			if ( __DIR__ . '/dist/bundled/vendor/symfony/polyfill-mbstring/Mbstring.php' === $file_path ) {
				return $contents; // TODO: Remove after nikic/PHP-Parser#763 is solved.
			}
			if ( in_array( $file_path, array( __DIR__ . '/dist/bundled/vendor/symfony/polyfill-intl-idn/bootstrap.php', __DIR__ . '/dist/bundled/vendor/symfony/polyfill-mbstring/bootstrap.php' ), true ) ) {
				$contents = mb_ereg_replace( "namespace {$prefix};", '', $contents );
			}
			if ( __DIR__ . '/dist/bundled/vendor/guzzlehttp/guzzle/src/functions.php' === $file_path ) {
				$contents = mb_ereg_replace( "\\\\{$prefix}\\\\uri_template\(", "\\uri_template(", $contents );
			}
			if ( __DIR__ . '/dist/bundled/vendor/google/apiclient/src/aliases.php' === $file_path ) {
				$contents = mb_ereg_replace( "'{$regex_prefix}\\\\\\\\Google\\\\\\\\(.*?)'\\s+=> 'Google_(.*?)'", "'{$prefix}\\\\Google\\\\\\1' => '{$prefix}\\\\Google_\\2'", $contents );
			}
			$contents = mb_ereg_replace( "defined\('(\\\\\\\\)?GuzzleHttp", "defined('\\\\{$prefix}\\\\GuzzleHttp", $contents );
			$contents = mb_ereg_replace( "array\('Monolog\\\\\\\\Utils', 'detectAndCleanUtf8'\)", "array('\\\\{$prefix}\\\\Monolog\\\\Utils', 'detectAndCleanUtf8')", $contents );

			return $contents;
		},
	),
	'whitelist-global-classes'   => false,
	'whitelist-global-constants' => false, // TODO: Only difference is in Verify.php - check which one is correct. Defaults to true.
	'whitelist-global-functions' => false,
);
