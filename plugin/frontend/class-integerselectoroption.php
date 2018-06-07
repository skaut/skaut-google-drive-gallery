<?php
namespace Sgdg\Frontend;

require_once 'class-integeroption.php';

class IntegerSelectorOption extends Option {
	public function __construct( $name, $default_value, $default_unit, $section, $title ) {
		parent::__construct( $name, [
			'value' => $default_value,
			'unit' => $default_unit,
		], $section, $title );
	}

	public function register() {
		register_setting( 'sgdg', $this->name . '_value', [
			'type'              => 'integer',
			'sanitize_callback' => [ $this, 'sanitize' ],
		]);
		register_setting( 'sgdg', $this->name . '_unit', [
			'type'              => 'string',
			'sanitize_callback' => [ $this, 'sanitize_unit' ],
		]);
	}

	public function sanitize( $value ) {
		if ( is_numeric( $value ) ) {
			return intval( $value );
		}
		return $this->default_value['value'];
	}

	public function sanitize_unit( $value ) {
		if ( 'px' === $value ) {
			return 'px';
		}
		if ( 'cols' === $value ) {
			return 'cols';
		}
		return $this->default_value['unit'];
	}

	public function html() {
		echo( '<input type="text" name="' . esc_attr( $this->name ) . '_value" value="' . esc_attr( $this->getValue() ) . '" class="regular-text">' );
		echo( '<select name="' . esc_attr($this->name) . '_unit">' );
		echo( '<option value="px"' . ( $this->getUnit() === 'px' ? ' selected' : '' ) . '>px</option>' );
		echo( '<option value="cols"' . ( $this->getUnit() === 'cols' ? ' selected' : '' ) . '>' . esc_html__( 'columns', 'skaut-google-drive-gallery' ) . '</option>' );
		echo( '</select>' );
	}

	public function getValue( $default_value = null ) {
		return get_option( $this->name . '_value', ( isset( $default_value ) ? $default_value : $this->default_value['value'] ) );
	}

	public function getUnit( $default_value = null ) {
		return get_option( $this->name . '_unit', ( isset( $default_value ) ? $default_value : $this->default_value['unit'] ) );
	}

	public function getSize( $default_value = null ) {
		if ( $this->getUnit( $default_value['unit'] ) === 'cols' ) {
			return floor( 1920 / get_option( $this->name . '_value', ( isset( $default_value ) ? $default_value : $this->default_value['value'] ) ) );
		}
		return get_option( $this->name . '_value', ( isset( $default_value ) ? $default_value : $this->default_value['value'] ) );
	}

	public function getWidth( $spacing, $default_value = null ) {
		if ( $this->getUnit( $default_value['unit'] ) === 'cols' ) {
			$cols = $this->getValue( $default_value['value'] );
			return 'width: ' . floor( 95 / $cols ) . '%; width: calc(' . floor( 100 / $cols ) . '% - ' . $spacing * ( 1 - 1 / $cols ) . 'px);';
		}
		return 'width: ' . $this->getValue( $default_value['value'] ) . $this->getUnit( $default_value['unit'] ) . ';';
	}
}
