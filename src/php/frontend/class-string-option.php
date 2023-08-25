<?php
/**
 * Contains the String_Option class
 *
 * @package skaut-google-drive-gallery
 */

namespace Sgdg\Frontend;

require_once __DIR__ . '/class-option.php';

/**
 * An option representing a string value
 *
 * @see Option
 *
 * phpcs:disable SlevomatCodingStandard.Classes.RequireAbstractOrFinal.ClassNeitherAbstractNorFinal
 */
class String_Option extends Option {

	/**
	 * Registers the option with WordPress.
	 *
	 * @return void
	 */
	public function register() {
		register_setting(
			$this->page,
			$this->name,
			array(
				'sanitize_callback' => array( $this, 'sanitize' ),
				'type'              => 'string',
			)
		);
	}

	/**
	 * Renders the UI for updating the option.
	 *
	 * This function renders (by calling `echo()`) the UI for updating the option, including the current value.
	 *
	 * @return void
	 */
	public function html() {
		echo '<input type="text" name="' .
			esc_attr( $this->name ) .
			'" value="' .
			esc_attr( get_option( $this->name, $this->default_value ) ) .
			'" class="regular-text">';
	}
}
