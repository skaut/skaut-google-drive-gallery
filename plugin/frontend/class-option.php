<?php
namespace Sgdg\Frontend;

abstract class Option {
	protected $name;
	protected $default_value;
	protected $section;
	protected $title;

	public function __construct( $name, $default_value, $section, $title ) {
		$this->name          = 'sgdg_' . $name;
		$this->default_value = $default_value;
		$this->section       = 'sgdg_' . $section;
		$this->title         = $title;
	}

	abstract public function register();

	public function sanitize( $value ) {
		return $value;
	}

	public function add_field() {
		$this->register();
		add_settings_field( $this->name, $this->title, [ $this, 'html' ], 'sgdg', $this->section );
	}

	abstract public function html();

	public function get( $default_value = null ) {
		return get_option( $this->name, ( isset( $default_value ) ? $default_value : $this->default_value ) );
	}
}
