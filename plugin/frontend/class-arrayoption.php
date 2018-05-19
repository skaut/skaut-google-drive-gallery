<?php
namespace Sgdg\Frontend;

require_once 'class-option.php';

class ArrayOption extends Option {
	public function register() {
		register_setting( 'sgdg', $this->name, [
			'type'              => 'string',
			'sanitize_callback' => [ $this, 'sanitize' ],
		]);
	}

	public function sanitize( $value ) {
		if ( is_string( $value ) ) {
			$value = json_decode( $value, true );
		}
		if ( null === $value ) {
			$value = $this->default_value;
		}
		return $value;
	}

	public function html() {
		echo( '<input id="' . esc_attr( $this->name ) . '" type="hidden" name="' . esc_attr( $this->name ) . '" value="' . esc_attr( wp_json_encode( $this->get(), JSON_UNESCAPED_UNICODE ) ) . '">' );
	}
}
