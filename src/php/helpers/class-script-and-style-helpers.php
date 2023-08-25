<?php
/**
 * Contains the Script_And_Style_Helpers class.
 *
 * @package skaut-google-drive-gallery
 */

namespace Sgdg;

/**
 * Contains helper functions for registering and enqueueing scripts and styles.
 */
final class Script_And_Style_Helpers {

	/**
	 * A list of already added inline configurations
	 *
	 * @var array<array{0: string, 1: string}> The acitve configurations, recorded as a script handle and the JavaScript variable name.
	 */
	private static $inline_configs = array();

	/**
	 * Registers a script file
	 *
	 * Registers a script so that it can later be enqueued by `wp_enqueue_script()`.
	 *
	 * @param string        $handle A unique handle to identify the script with. This handle should be passed to `wp_enqueue_script()`.
	 * @param string        $src Path to the file, relative to the plugin directory.
	 * @param array<string> $deps A list of dependencies of the script. These can be either system dependencies like jquery, or other registered scripts. Default [].
	 *
	 * @return void
	 */
	public static function register_script( $handle, $src, $deps = array() ) {
		$path = plugin_dir_path( __DIR__ ) . $src;
		$url  = plugin_dir_url( __DIR__ ) . $src;
		wp_register_script( $handle, $url, $deps, file_exists( $path ) ? strval( filemtime( $path ) ) : false, true );
	}

	/**
	 * Registers a style file
	 *
	 * Registers a style so that it can later be enqueued by `wp_enqueue_style()`.
	 *
	 * @param string        $handle A unique handle to identify the style with. This handle should be passed to `wp_enqueue_style()`.
	 * @param string        $src Path to the file, relative to the plugin directory.
	 * @param array<string> $deps A list of dependencies of the style. These can be either system dependencies or other registered styles. Default [].
	 *
	 * @return void
	 */
	public static function register_style( $handle, $src, $deps = array() ) {
		$path = plugin_dir_path( __DIR__ ) . $src;
		$url  = plugin_dir_url( __DIR__ ) . $src;
		wp_register_style( $handle, $url, $deps, file_exists( $path ) ? strval( filemtime( $path ) ) : false );
	}

	/**
	 * Enqueues a script file
	 *
	 * Registers and immediately enqueues a script. Note that you should **not** call this function if you've previously registered the script using `register_script()`.
	 *
	 * @param string        $handle A unique handle to identify the script with.
	 * @param string        $src Path to the file, relative to the plugin directory.
	 * @param array<string> $deps A list of dependencies of the script. These can be either system dependencies like jquery, or other registered scripts. Default [].
	 *
	 * @return void
	 */
	public static function register_and_enqueue_script( $handle, $src, $deps = array() ) {
		self::register_script( $handle, $src, $deps );
		wp_enqueue_script( $handle );
	}

	/**
	 * Enqueues a style file
	 *
	 * Registers and immediately enqueues a style. Note that you should **not** call this function if you've previously registered the style using `register_style()`.
	 *
	 * @param string        $handle A unique handle to identify the style with.
	 * @param string        $src Path to the file, relative to the plugin directory.
	 * @param array<string> $deps A list of dependencies of the style. These can be either system dependencies or other registered styles. Default [].
	 *
	 * @return void
	 */
	public static function register_and_enqueue_style( $handle, $src, $deps = array() ) {
		self::register_style( $handle, $src, $deps );
		wp_enqueue_style( $handle );
	}

	/**
	 * Adds a configuration to an already registered/enqueued script.
	 *
	 * @param string                                      $handle A unique handle to identify the script with.
	 * @param string                                      $js_var_name The name of the JavaScript variable that the configuration will be accessible in.
	 * @param array<string, string|array<string, string>> $data The actual configuration data.
	 *
	 * @return void
	 */
	public static function add_script_configuration( $handle, $js_var_name, $data ) {
		if ( in_array( array( $handle, $js_var_name ), self::$inline_configs, true ) ) {
			return;
		}

		wp_add_inline_script( $handle, 'const ' . $js_var_name . ' = ' . wp_json_encode( $data ) . ';', 'before' );
		self::$inline_configs[] = array( $handle, $js_var_name );
	}
}
