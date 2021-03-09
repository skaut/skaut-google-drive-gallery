<?php
/**
 * PHP-Scoper configuration
 *
 * @package skaut-google-drive-gallery
 */

use Isolated\Symfony\Component\Finder\Finder;

return array(
	'prefix'                     => 'Sgdg\\Vendor',
	'finders'                    => array(
		Finder::create()->files()
			->name( array( '*.php', '/LICENSE(.txt)?/' ) )

			->path( '#^firebase/php-jwt/#' )
			->path( '#^google/apiclient/#' )
			->path( '#^google/auth/#' )
			->path( '#^guzzlehttp/guzzle/#' )
			->path( '#^guzzlehttp/promises/#' )
			->path( '#^guzzlehttp/psr7/#' )
			->path( '#^monolog/monolog/#' )
			->path( '#^psr/cache/#' )
			->path( '#^psr/http-message/#' )
			->path( '#^psr/log/#' )
			->path( '#^ralouphie/getallheaders/#' )
			->path( '#^symfony/polyfill-intl-idn/#' )
			->path( '#^symfony/polyfill-intl-normalizer/#' )
			->path( '#^symfony/polyfill-php70/#' )
			->path( '#^symfony/polyfill-php72/#' )
			->in( 'vendor' ),
		Finder::create()->files()
			->path( '#^google/apiclient-services/src/Google/Service/Drive.php#' )
			->in( 'vendor' ),
		Finder::create()->files()
			->name( array( '*.php', '/LICENSE(.txt)?/' ) )
			->path( '#^google/apiclient-services/src/Google/Service/Drive/#' )
			->in( 'vendor' ),
	),
	'patchers'                   => array(
		static function ( $file_path, $prefix, $contents ) {
			$regex_prefix = mb_ereg_replace( '\\\\', '\\\\\\\\', $prefix );
			$prefix = mb_ereg_replace( '\\\\', '\\\\', $prefix );
			if ( mb_ereg_match( preg_quote( __DIR__, '/' ) . '\\/vendor\\/symfony\\/polyfill-(.*)/bootstrap.php', $file_path ) ) {
				$contents = mb_ereg_replace( "namespace {$prefix};", '', $contents );
			}
			if ( __DIR__ . '/vendor/guzzlehttp/guzzle/src/functions.php' === $file_path ) {
				$contents = mb_ereg_replace( "\\\\{$prefix}\\\\uri_template\(", "\\uri_template(", $contents );
			}
			if ( __DIR__ . '/vendor/google/apiclient/src/aliases.php' === $file_path ) {
				$contents = mb_ereg_replace( "'{$regex_prefix}\\\\\\\\Google\\\\\\\\(.*?)'\\s+=> 'Google_(.*?)'", "'{$prefix}\\\\Google\\\\\\1' => '{$prefix}\\\\Google_\\2'", $contents );
			}

			$contents = mb_ereg_replace( "defined\('(\\\\\\\\)?GuzzleHttp", "defined('\\\\{$prefix}\\\\GuzzleHttp", $contents, 'n' );
			$contents = mb_ereg_replace( "array\('Monolog\\\\\\\\Utils', 'detectAndCleanUtf8'\)", "array('\\\\{$prefix}\\\\Monolog\\\\Utils', 'detectAndCleanUtf8')", $contents );

			return $contents;
		},
	),
	'whitelist-global-classes'   => false,
	'whitelist-global-constants' => false,
	'whitelist-global-functions' => false,
);
