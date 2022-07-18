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
class Script_And_Style_Helpers {

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
		$path = plugin_dir_path( dirname( __FILE__ ) ) . $src;
		$url  = plugin_dir_url( dirname( __FILE__ ) ) . $src;
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
		$path = plugin_dir_path( dirname( __FILE__ ) ) . $src;
		$url  = plugin_dir_url( dirname( __FILE__ ) ) . $src;
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

}
