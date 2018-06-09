<?php
namespace Sgdg\Frontend;

require_once 'class-option.php';

class StringCodeOption extends Option {
	private $readonly;

	public function __construct( $name, $default_value, $page, $section, $title ) {
		parent::__construct( $name, $default_value, $page, $section, $title );
		$this->readonly = false;
	}

	public function register() {
		register_setting( $this->page, $this->name, [
			'type'              => 'string',
			'sanitize_callback' => [ $this, 'sanitize' ],
		]);
	}

	public function add_field( $readonly = false ) {
		$this->readonly = $readonly;
		parent::add_field();
	}

	public function html() {
		echo( '<input type="text" name="' . $this->name . '" value="' . get_option( $this->name, $this->default_value ) . '" ' . ( $this->readonly ? 'readonly ' : '' ) . 'class="regular-text code">' );
	}
}
