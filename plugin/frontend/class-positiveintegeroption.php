<?php
namespace Sgdg\Frontend;

require_once __DIR__ . '/class-integeroption.php';

class PositiveIntegerOption extends IntegerOption {
	public function sanitize( $value ) {
		$value = parent::sanitize( $value );
		if( 0 < $value ) {
			return $value;
		}
		return $this->default_value;
	}
}
