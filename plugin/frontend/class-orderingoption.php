<?php
namespace Sgdg\Frontend;

require_once 'class-option.php';

class OrderingOption extends Option {
	public function __construct( $name, $defaultBy, $defaultOrder, $section, $title ) {
		parent::__construct( $name, [
			'by'    => ($defaultBy === 'name' ? 'name' : 'time'),
			'order' => ($defaultOrder === 'ascending' ? 'ascending' : 'descending'),
		], $section, $title );
	}

	public function register() {
		register_setting( 'sgdg', $this->name . '_order', [
			'type'              => 'string',
			'sanitize_callback' => [ $this, 'sanitize_order' ],
		]);
		register_setting( 'sgdg', $this->name . '_by', [
			'type'              => 'string',
			'sanitize_callback' => [ $this, 'sanitize' ],
		]);
	}

	public function sanitize_order( $value ) {
		if ( $value === 'ascending' ) {
			return 'ascending';
		}
		if( $value === 'descending' ) {
			return 'descending';
		}
		return $this->default_value['order'];
	}

	public function sanitize( $value ) {
		if ( $value === 'time' ) {
			return 'time';
		}
		if( $value === 'name' ) {
			return 'name';
		}
		return $this->default_value['by'];
	}

	public function add_field() {
		$this->register();
		add_settings_field( $this->name . '_order', $this->title, [ $this, 'html_order' ], 'sgdg', $this->section );
		add_settings_field( $this->name . '_by', '', [ $this, 'html' ], 'sgdg', $this->section );
	}

	public function html_order() {
		echo( '<select name="' . esc_attr( $this->name ) . '_order">' );
		echo( '<option value="ascending"' . ($this->getOrder() === 'ascending' ? ' selected' : '') . '>' . esc_html__( 'Ascending', 'skaut-google-drive-gallery' ) . '</option>' );
		echo( '<option value="descending"' . ($this->getOrder() === 'descending' ? ' selected' : '') . '>' . esc_html__( 'Descending', 'skaut-google-drive-gallery' ) . '</option>' );
		echo( '</select>' );
	}

	public function html() {
		echo( '<label for="sgdg-by-time"><input type="radio" id="sgdg-by-time" name="' . esc_attr( $this->name ) . '_by" value="time"' . ($this->getBy() === 'time' ? ' checked' : '') . '>' . esc_html__( 'By time', 'skaut-google-drive-gallery' ) . '</label><br>' );
		echo( '<label for="sgdg-by-name"><input type="radio" id="sgdg-by-name" name="' . esc_attr( $this->name ) . '_by" value="name"' . ($this->getBy() === 'name' ? ' checked' : '') . '>' . esc_html__( 'By name', 'skaut-google-drive-gallery' ) . '</label>' );
	}

	public function getOrder( $default_value = null ) {
		return get_option( $this->name . '_order', ( isset( $default_value ) ? $default_value : $this->default_value['order'] ) );
	}

	public function getBy( $default_value = null ) {
		return get_option( $this->name . '_by', ( isset( $default_value ) ? $default_value : $this->default_value['by'] ) );
	}

	public function get( $default_value = null ) {
		return ($this->getBy($default_value['by']) === 'name' ? 'name_natural' : 'modifiedTime') . ($this->getOrder($default_value['order']) === 'ascending' ? '' : ' desc');
	}
}
