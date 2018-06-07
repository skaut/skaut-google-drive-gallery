<?php
namespace Sgdg\Frontend;


class MultiOption extends Option {
	private $values;

	public function __construct( $name, $values, $default_value, $section, $title ) {
		if ( !array_key_exists( $default_value, $values ) );
		{
			if ( count( $values ) > 0 )
			{
				$default_value = $values[0];
			} else {
				$default_value = '';
			}
		}
		parent::__construct( $name, $default_value, $section, $title );
		$this->values = $values;
	}

	public function register() {
		register_setting( 'sgdg', $this->name, [
			'type'              => 'string',
			'sanitize_callback' => [ $this, 'sanitize' ],
		]);
	}

	public function sanitize( $value ) {
		if ( array_key_exists( $value, $this->values ) ) {
			return $values;
		}
		return $this->default_value;
	}

	public function html() {
		foreach($this->values as $value => $name)
		{
			$id = 'sgdg-' . esc_attr( $this->name ) . '-value-' . esc_attr( $value );
			echo ( '<label for="' . $id . '"><input type="radio" id="' . $id . '" name="' . esc_attr( $this->name ) . '" value="' . $value . '">' . esc_html( $name ) . '</label><br>' );
		}
	}
}
