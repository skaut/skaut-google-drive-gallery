<?php
/**
 * Contains the GET_Helpers class.
 *
 * @package skaut-google-drive-gallery
 */

namespace Sgdg;

/**
 * Contains helper functions for working with GET variables.
 */
class GET_Helpers {

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
	public static function get_string_variable( $name, $default = '' ) {
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
	public static function get_int_variable( $name, $default ) {
		$string_value = self::get_string_variable( $name );
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
	public static function get_array_variable( $name, $default = array() ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return isset( $_GET[ $name ] ) ? array_map( 'sanitize_text_field', wp_unslash( (array) $_GET[ $name ] ) ) : $default; // @phpstan-ignore-line
	}
}
