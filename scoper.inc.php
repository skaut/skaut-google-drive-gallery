<?php
/**
 * PHP-Scoper configuration
 *
 * @package skaut-google-drive-gallery
 */

use Isolated\Symfony\Component\Finder\Finder;

/**
 * Safely replaces pattern with replacement in string.
 *
 * @param string $pattern The pattern to be replaced.
 * @param string $replacement The replacement.
 * @param string $string The string to replace in.
 *
 * @return string The string with replacement, if it can be replaced.
 *
 * phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
 */
function safe_replace( $pattern, $replacement, $string ) {
	$replacement = mb_ereg_replace( $pattern, $replacement, $string );

	if ( false === $replacement || null === $replacement ) {
		return $string;
	}

	return $replacement;
}

/**
 * Constructs a finder for composer dependencies.
 *
 * @return Finder The initialized Finder.
 */
function dependency_finder() {
	// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.system_calls_exec
	exec( 'composer show --no-dev --name-only', $dependencies );
	$finder = Finder::create()->files()->name( array( '*.php', '/LICENSE(.txt)?/' ) )->in( 'vendor' );

	foreach ( $dependencies as $dependency ) {
		$finder->path( '#^' . $dependency . '/#' );
	}

	return $finder;
}
// phpcs:enable

return array(
	'prefix'   => 'Sgdg\\Vendor',
	'finders'  => array(
		dependency_finder()->notPath( '#^google/apiclient-services/#' ),
		Finder::create()->files()
			->name( 'autoload.php' )
			->depth( 0 )
			->in( 'vendor/google/apiclient-services' ),
		Finder::create()->files()
			->name( 'Drive.php' )
			->depth( 0 )
			->in( 'vendor/google/apiclient-services/src' ),
		Finder::create()->files()
			->name( array( '*.php', '/LICENSE(.txt)?/' ) )
			->path( '#^google/apiclient-services/src/Drive/#' )
			->in( 'vendor' ),
		Finder::create()->files()
			->name( array( '*.php', '/LICENSE(.txt)?/' ) )
			->depth( 0 )
			->in( 'vendor/composer' ),
		Finder::create()->files()
			->name( 'autoload.php' )
			->depth( 0 )
			->in( 'vendor' ),
	),
	'patchers' => array(
		static function ( $file_path, $prefix, $contents ) {
			$regex_prefix   = mb_ereg_replace( '\\\\', '\\\\\\\\', $prefix );
			$replace_prefix = mb_ereg_replace( '\\\\', '\\\\', $prefix );

			if ( __DIR__ . '/vendor/composer/autoload_real.php' === $file_path ) {
				$var_name_prefix = mb_ereg_replace( '\\\\', '_', $prefix );
				$contents = safe_replace(
					"if \\('Composer\\\\\\\\Autoload\\\\\\\\ClassLoader' === \\\$class\\)",
					"if ('{$replace_prefix}\\\\Composer\\\\Autoload\\\\ClassLoader' === \$class)",
					$contents
				);
				$contents = safe_replace(
					"\\\\spl_autoload_unregister\\(array\\('ComposerAutoloaderInit",
					"\\spl_autoload_unregister(array('{$replace_prefix}\\\\ComposerAutoloaderInit",
					$contents
				);
				$contents = safe_replace(
					"\\\$GLOBALS\['__composer_autoload_files'\]",
					"\$GLOBALS['__composer_autoload_files_" . $var_name_prefix . "']",
					$contents
				);
			}

			if ( __DIR__ . '/vendor/guzzlehttp/guzzle/src/functions.php' === $file_path ) {
				$contents = safe_replace( "\\\\{$replace_prefix}\\\\uri_template\(", "\\uri_template(", $contents );
			}

			if ( __DIR__ . '/vendor/google/apiclient/src/aliases.php' === $file_path ) {
				$contents = safe_replace(
					"'{$regex_prefix}\\\\\\\\Google\\\\\\\\(.*?)'\\s+=> 'Google_(.*?)'",
					"'{$replace_prefix}\\\\Google\\\\\\1' => '{$replace_prefix}\\\\Google_\\2'",
					$contents
				);
			}

			if ( __DIR__ . '/vendor/google/apiclient/src/AccessToken/Verify.php' === $file_path ) {
				$contents = safe_replace(
					"return 'phpseclib3\\\\\\\\Crypt\\\\\\\\AES\:\:ENGINE_OPENSSL'",
					"return '{$replace_prefix}\\\\phpseclib3\\\\Crypt\\\\AES::ENGINE_OPENSSL'",
					$contents
				);
				$contents = safe_replace(
					"return 'phpseclib\\\\\\\\Crypt\\\\\\\\RSA\:\:MODE_OPENSSL'",
					"return '{$replace_prefix}\\\\phpseclib\\\\Crypt\\\\RSA::MODE_OPENSSL'",
					$contents
				);
			}

			if (
				mb_ereg_match(
					preg_quote( __DIR__, '/' ) . '\\/vendor\\/symfony\\/polyfill-(.*)/bootstrap.php',
					$file_path
				)
			) {
				$contents = safe_replace( "namespace {$replace_prefix};", '', $contents );
			}

			$contents = safe_replace(
				"defined\('(\\\\\\\\)?GuzzleHttp",
				"defined('\\\\{$replace_prefix}\\\\GuzzleHttp",
				$contents
			);
			$contents = safe_replace(
				"array\('Monolog\\\\\\\\Utils', 'detectAndCleanUtf8'\)",
				"array('\\\\{$replace_prefix}\\\\Monolog\\\\Utils', 'detectAndCleanUtf8')",
				$contents
			);
			$contents = safe_replace(
				'\\* @return \\\\Google\\\\Client',
				"* @return \\{$prefix}\\Google\\Client",
				$contents
			);

			return $contents;
		},
	),
);
