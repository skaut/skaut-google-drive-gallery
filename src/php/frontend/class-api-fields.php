<?php
/**
 * Contains the API_Fields class.
 *
 * @package skaut-google-drive-gallery
 */

namespace Sgdg\Frontend;

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
				if ( ! array_key_exists( $key, $prototype ) ) {
					return false;
				}

				if ( is_array( $value ) && is_array( $prototype[ $key ] ) && count( array_diff( $value, $prototype[ $key ] ) ) > 0 ) {
					return false;
				}
			} else {
				if ( ! in_array( $value, $prototype, true ) ) {
					return false;
				}
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
			} else {
				$ret .= ', ' . strval( $value );
			}
		}

		return substr( $ret, 2 );
	}

	/**
	 * Parses a Google API response according to the fields.
	 *
	 * @param \Sgdg\Vendor\Google\Service\Drive\DriveFile<mixed> $response The API response.
	 *
	 * @return array<int|string, string|array<string>> The parsed response
	 */
	public function parse_response( $response ) {
		$ret = array();

		foreach ( $this->fields as $key => $value ) {
			if ( is_array( $value ) ) {
				foreach ( $value as $subvalue ) {
					if ( property_exists( $response, strval( $key ) ) && property_exists( $response->$key, $subvalue ) ) {
						$ret[ $key ][ $subvalue ] = $response->$key->$subvalue;
					}
				}
			} else {
				if ( 'id' === $value ) {
					$ret['id'] = $response->getMimeType() === 'application/vnd.google-apps.shortcut' ? $response->getShortcutDetails()->getTargetId() : $response->getId();
				} else {
					if ( property_exists( $response, $value ) ) {
						$ret[ strval( $value ) ] = $response->$value;
					}
				}
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

		if ( in_array( 'id', $fields, true ) ) {
			if ( ! in_array( 'mimeType', $fields, true ) ) {
				$fields[] = 'mimeType';
			}

			if ( array_key_exists( 'shortcutDetails', $fields ) ) {
				if ( is_array( $fields['shortcutDetails'] ) && ! in_array( 'targetId', $fields['shortcutDetails'], true ) ) {
					$fields['shortcutDetails'][] = 'mimeType';
				}
			} else {
				$fields['shortcutDetails'] = array( 'targetId' );
			}
		}

		return $fields;
	}

}
