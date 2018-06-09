<?php
namespace Sgdg\Frontend;

class MultiOption extends Option {
	private $values;
	private $suboptions;

	public function __construct( $name, $values, $default_value, $page, $section, $title ) {
		if ( ! array_key_exists( $default_value, $values ) ) {
			if ( count( $values ) > 0 ) {
				reset( $values );
				$default_value = key( $values );
			} else {
				$default_value = '';
			}
		}
		parent::__construct( $name, $default_value, $page, $section, $title );
		$this->values     = $values;
		$this->suboptions = [];
		foreach ( $values as $value => $_ ) {
			$this->suboptions[ $value ] = [];
		}
	}

	public function addSubOption( $value, \Sgdg\Frontend\Option $option ) {
		$this->suboptions[ $value ][] = $option;
	}

	public function register() {
		register_setting( $this->page, $this->name, [
			'type'              => 'string',
			'sanitize_callback' => [ $this, 'sanitize' ],
		]);
		foreach ( $this->values as $value => $_ ) {
			foreach ( $this->suboptions[ $value ] as $suboption ) {
				$suboption->register();
			}
		}
	}

	public function sanitize( $value ) {
		if ( array_key_exists( $value, $this->values ) ) {
			return $value;
		}
		return $this->default_value;
	}

	public function html() {
		foreach ( $this->values as $value => $name ) {
			$id = 'sgdg-' . esc_attr( $this->name ) . '-value-' . esc_attr( $value );
			echo( '<label for="' . $id . '"><input type="radio" id="' . $id . '" name="' . esc_attr( $this->name ) . '" value="' . $value . '"' . ( $this->get() === $value ? ' checked' : '' ) . '>' . esc_html( $name ) . '</label>' );
			echo( '<br><table class="form-table sgdg-options-indented"><tbody>' );
			foreach ( $this->suboptions[ $value ] as $suboption ) {
				echo( '<tr><th>' );
				echo( esc_html( $suboption->title ) );
				echo( '</th><td>' );
				$suboption->html();
				echo( '</td></tr>' );
			}
			echo( '</tbody></table>' );
		}
	}
}
