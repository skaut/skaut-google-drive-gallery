<?php
/**
 * Contains the API_Fields class.
 *
 * @package skaut-google-drive-gallery
 */

namespace Sgdg\Frontend;

use Sgdg\Vendor\Google\Service\Drive\DriveFile;

/**
 * API file fields
 */
final class API_Fields {

	/**
	 * The fields
	 *
	 * @var array<int|string, string|array<string>> $fields
	 */
	private $fields;

	/**
	 * API_Fields class constructor.
	 *
	 * @param array<int|string, string|array<string>> $fields The fields as an array. Use associative keys to express nested parameters. Example: `array( 'id', 'name', 'imageMediaMetadata' => array( 'width', 'height' ) )`.
	 */
	public function __construct( $fields ) {
		$this->fields = $fields;
	}

	/**
	 * Check that the fields match the prototype.
	 *
	 * @param array<int|string, string|array<string>> $prototype The prototype in the same format as the fields.
	 *
	 * @return bool True if the fields match.
	 */
	public function check( $prototype ) {
		foreach ( $this->fields as $key => $value ) {
			if ( is_string( $key ) ) {
				if ( ! self::check_composite_field( $prototype, $key, $value ) ) {
					return false;
				}
			} elseif ( ! self::check_simple_field( $prototype, $value ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Returns the fields in the correct format for Google API
	 *
	 * @return string The formatted fields.
	 */
	public function format() {
		$fields = $this->fix_shortcuts();

		$ret = '';

		foreach ( $fields as $key => $value ) {
			if ( is_string( $key ) && is_array( $value ) ) {
				$ret .= ', ' . $key . '(' . implode( ', ', $value ) . ')';
			} elseif ( is_string( $value ) ) {
				$ret .= ', ' . $value;
			}
		}

		return substr( $ret, 2 );
	}

	/**
	 * Parses a Google API response according to the fields.
	 *
	 * @param DriveFile<mixed> $response The API response.
	 *
	 * @return array<int|string, string|array<string>> The parsed response
	 */
	public function parse_response( $response ) {
		$ret = array();

		foreach ( $this->fields as $key => $value ) {
			$parsed = is_array( $value )
				? self::parse_response_composite_field( $response, $key, $value )
				: self::parse_response_simple_field( $response, $value );

			if ( null !== $parsed ) {
				$ret = array_merge( $ret, $parsed );
			}
		}

		return $ret;
	}

	/**
	 * Returns the fields with added provisions for IDs of shortcuts.
	 *
	 * @return array<int|string, string|array<string>> The enriched fields.
	 */
	private function fix_shortcuts() {
		$fields = $this->fields;

		if ( ! in_array( 'id', $fields, true ) ) {
			return $fields;
		}

		if ( ! in_array( 'mimeType', $fields, true ) ) {
			$fields[] = 'mimeType';
		}

		if ( ! array_key_exists( 'shortcutDetails', $fields ) ) {
			$fields['shortcutDetails'] = array( 'targetId', 'targetMimeType' );
		} elseif ( is_array( $fields['shortcutDetails'] ) ) {
			if ( ! in_array( 'targetId', $fields['shortcutDetails'], true ) ) {
				$fields['shortcutDetails'][] = 'targetId';
			}

			if ( ! in_array( 'targetMimeType', $fields['shortcutDetails'], true ) ) {
				$fields['shortcutDetails'][] = 'targetMimeType';
			}
		}

		return $fields;
	}

	/**
	 * Check that a field matches the prototype.
	 *
	 * @param array<int|string, string|array<string>> $prototype The prototype in the same format as the fields.
	 * @param array<string>|string                    $value The field value.
	 *
	 * @return bool True if the field matches.
	 */
	private static function check_simple_field( $prototype, $value ) {
		return in_array( $value, $prototype, true );
	}

	/**
	 * Check that a field matches the prototype.
	 *
	 * @param array<int|string, string|array<string>> $prototype The prototype in the same format as the fields.
	 * @param string                                  $key The field key.
	 * @param array<string>|string                    $value The field value.
	 *
	 * @return bool True if the field matches.
	 */
	private static function check_composite_field( $prototype, $key, $value ) {
		if ( ! array_key_exists( $key, $prototype ) ) {
			return false;
		}

		return ! is_array( $value ) ||
			! is_array( $prototype[ $key ] ) ||
			0 === count( array_diff( $value, $prototype[ $key ] ) );
	}

	/**
	 * Parses a field from the Google API response.
	 *
	 * @param DriveFile<mixed> $response The API response.
	 * @param string           $value The field value.
	 *
	 * @return array<string, string>|null The parsed response or null if the field isn't present/couldn't be parsed
	 */
	private static function parse_response_simple_field( $response, $value ) {
		if ( 'id' === $value ) {
			return array(
				'id' => 'application/vnd.google-apps.shortcut' === $response->getMimeType()
					? $response->getShortcutDetails()->getTargetId()
					: $response->getId(),
			);
		}

		if ( property_exists( $response, $value ) ) {
			return array( strval( $value ) => $response->$value );
		}

		return null;
	}

	/**
	 * Parses a field from the Google API response.
	 *
	 * @param DriveFile<mixed> $response The API response.
	 * @param int|string       $key The field key.
	 * @param array<string>    $value The field value.
	 *
	 * @return array<int|string, array<string>>|null The parsed response or null if the field isn't present/couldn't be parsed
	 */
	private static function parse_response_composite_field( $response, $key, $value ) {
		if ( ! property_exists( $response, strval( $key ) ) || ! is_object( $response->$key ) ) {
			return null;
		}

		$ret = array();

		foreach ( $value as $subvalue ) {
			if ( property_exists( $response->$key, $subvalue ) ) {
				$ret[ $subvalue ] = $response->$key->$subvalue;
			}
		}

		return array( $key => $ret );
	}
}
