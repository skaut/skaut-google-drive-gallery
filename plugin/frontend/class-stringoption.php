<?php
namespace Sgdg\Frontend;

require_once 'class-option.php';

class StringOption extends Option {
	public function register() {
		register_setting( $this->page, $this->name, [
			'type'              => 'string',
			'sanitize_callback' => [ $this, 'sanitize' ],
		]);
	}
	public function html() {
		echo( '<input type="text" name="' . $this->name . '" value="' . get_option( $this->name, $this->default_value ) . '" class="regular-text">' );
	}
}
