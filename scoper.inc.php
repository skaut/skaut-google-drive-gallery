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
			->path( '#^paragonie/random_compat/#' )
			->path( '#^phpseclib/phpseclib/#' )
			->path( '#^psr/cache/#' )
			->path( '#^psr/http-message/#' )
			->path( '#^psr/log/#' )
			->path( '#^ralouphie/getallheaders/#' )
			->path( '#^symfony/polyfill-intl-idn/#' )
			->path( '#^symfony/polyfill-intl-normalizer/#' )
			->path( '#^symfony/polyfill-php70/#' )
			->path( '#^symfony/polyfill-php72/#' )
			->path( '#^composer/#' )
			->in( 'vendor' ),
		Finder::create()->files()
			->path( '#^google/apiclient-services/src/Drive.php#' )
			->in( 'vendor' ),
		Finder::create()->files()
			->name( array( '*.php', '/LICENSE(.txt)?/' ) )
			->path( '#^google/apiclient-services/src/Drive/#' )
			->in( 'vendor' ),
		Finder::create()->files()
			->name( 'autoload.php' )
			->in( 'vendor' ),
	),
	'patchers'                   => array(
		static function ( $file_path, $prefix, $contents ) {
			$regex_prefix = mb_ereg_replace( '\\\\', '\\\\\\\\', $prefix );
			$replace_prefix = mb_ereg_replace( '\\\\', '\\\\', $prefix );
			if ( __DIR__ . '/vendor/composer/autoload_real.php' === $file_path ) {
				$var_name_prefix = mb_ereg_replace( '\\\\', '_', $prefix );
				$contents = mb_ereg_replace( "if \\('Composer\\\\\\\\Autoload\\\\\\\\ClassLoader' === \\\$class\\)", "if ('{$replace_prefix}\\\\Composer\\\\Autoload\\\\ClassLoader' === \$class)", $contents );
				$contents = mb_ereg_replace( "\\\\spl_autoload_unregister\\(array\\('ComposerAutoloaderInit", "\\spl_autoload_unregister(array('{$replace_prefix}\\\\ComposerAutoloaderInit", $contents );
				$contents = mb_ereg_replace( "\\\$GLOBALS\['__composer_autoload_files'\]", "\$GLOBALS['__composer_autoload_files_" . $var_name_prefix . "']", $contents );
			}
			if ( __DIR__ . '/vendor/guzzlehttp/guzzle/src/functions.php' === $file_path ) {
				$contents = mb_ereg_replace( "\\\\{$replace_prefix}\\\\uri_template\(", "\\uri_template(", $contents );
			}
			if ( __DIR__ . '/vendor/google/apiclient/src/aliases.php' === $file_path ) {
				$contents = mb_ereg_replace( "'{$regex_prefix}\\\\\\\\Google\\\\\\\\(.*?)'\\s+=> 'Google_(.*?)'", "'{$replace_prefix}\\\\Google\\\\\\1' => '{$replace_prefix}\\\\Google_\\2'", $contents );
			}
			if ( __DIR__ . '/vendor/google/apiclient/src/AccessToken/Verify.php' === $file_path ) {
				$contents = mb_ereg_replace( "return 'phpseclib3\\\\\\\\Crypt\\\\\\\\AES\:\:ENGINE_OPENSSL'", "return '{$replace_prefix}\\\\phpseclib3\\\\Crypt\\\\AES::ENGINE_OPENSSL'", $contents );
				$contents = mb_ereg_replace( "return 'phpseclib\\\\\\\\Crypt\\\\\\\\RSA\:\:MODE_OPENSSL'", "return '{$replace_prefix}\\\\phpseclib\\\\Crypt\\\\RSA::MODE_OPENSSL'", $contents );
			}
			if ( mb_ereg_match( preg_quote( __DIR__, '/' ) . '\\/vendor\\/symfony\\/polyfill-(.*)/bootstrap.php', $file_path ) ) {
				$contents = mb_ereg_replace( "namespace {$replace_prefix};", '', $contents );
			}

			$contents = mb_ereg_replace( "defined\('(\\\\\\\\)?GuzzleHttp", "defined('\\\\{$replace_prefix}\\\\GuzzleHttp", $contents );
			$contents = mb_ereg_replace( "array\('Monolog\\\\\\\\Utils', 'detectAndCleanUtf8'\)", "array('\\\\{$replace_prefix}\\\\Monolog\\\\Utils', 'detectAndCleanUtf8')", $contents );
			$contents = mb_ereg_replace( '\\* @return \\\\Google\\\\Client', "* @return \\{$prefix}\\Google\\Client", $contents );

			return $contents;
		},
	),
	'whitelist-global-classes'   => false,
	'whitelist-global-constants' => false,
	'whitelist-global-functions' => false,
);
