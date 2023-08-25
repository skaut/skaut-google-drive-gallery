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
final class GET_Helpers {

	/**
	 * Safely loads a string GET variable
	 *
	 * This function loads a GET variable, runs it through all the required WordPress sanitization and returns it.
	 *
	 * @param string $name The name of the GET variable.
	 * @param string $default_value The default value to use if the GET variable doesn't exist. Default empty string.
	 *
	 * @return string The GET variable value
	 */
	public static function get_string_variable( $name, $default_value = '' ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return isset( $_GET[ $name ] )
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.NonceVerification.Recommended
			? self::sanitize_get_variable( wp_unslash( strval( $_GET[ $name ] ) ) )
			: $default_value;
	}

	/**
	 * Safely loads an integer GET variable
	 *
	 * This function loads a GET variable, runs it through all the required WordPress sanitization and returns it.
	 *
	 * @param string $name The name of the GET variable.
	 * @param int    $default_value The default value to use if the GET variable doesn't exist.
	 *
	 * @return int The GET variable value
	 */
	public static function get_int_variable( $name, $default_value ) {
		$string_value = self::get_string_variable( $name );

		return '' !== $string_value ? intval( $string_value ) : $default_value;
	}

	/**
	 * Safely loads an array GET variable
	 *
	 * This function loads a GET variable, runs it through all the required WordPress sanitization and returns it.
	 *
	 * @param string        $name The name of the GET variable.
	 * @param array<string> $default_value The default value to use if the GET variable doesn't exist. Default empty array.
	 *
	 * @return array<string> The GET variable value
	 */
	public static function get_array_variable( $name, $default_value = array() ) {
		// phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Recommended
		return isset( $_GET[ $name ] )
			// @phpstan-ignore-next-line
			? array_map( array( self::class, 'sanitize_get_variable' ), wp_unslash( (array) $_GET[ $name ] ) )
			: $default_value;
		// phpcs:enable
	}

	/**
	 * Sanitizes a GET variable
	 *
	 * This function is a modified version of _sanitize_text_fields() from WordPress core. Unlike the original, this version doesn't strip leading and trailing spaces.
	 *
	 * @param mixed $str The value to sanitize (the actual value, not the variable name).
	 *
	 * @return string The sanitized value.
	 */
	private static function sanitize_get_variable( $str ) {
		if ( is_object( $str ) || is_array( $str ) ) {
			return '';
		}

		$str      = (string) $str;
		$filtered = wp_check_invalid_utf8( $str );

		if ( false !== strpos( $filtered, '<' ) ) {
			$filtered = wp_pre_kses_less_than( $filtered );
			$filtered = wp_strip_all_tags( $filtered, false );
			$filtered = str_replace( "<\n", "&lt;\n", $filtered );
		}

		$filtered = preg_replace( '/[\r\n\t]+/', '', $filtered );

		if ( is_null( $filtered ) ) {
			return '';
		}

		$found = false;

		while ( preg_match( '/%[a-f0-9]{2}/i', $filtered, $match ) ) {
			$filtered = str_replace( $match[0], '', $filtered );
			$found    = true;
		}

		if ( $found ) {
			$filtered = preg_replace( '/ +/', ' ', $filtered );

			if ( is_null( $filtered ) ) {
				return '';
			}
		}

		return $filtered;
	}
}
