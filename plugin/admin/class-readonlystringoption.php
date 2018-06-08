<?php
namespace Sgdg\Admin;

if ( ! is_admin() ) {
	return;
}

class ReadonlyStringOption {
	private $name;
	private $value;
	private $title;
	private $page;
	private $section;

	public function __construct( $name, $value, $page, $section, $title ) {
		$this->name    = 'sgdg_' . $name;
		$this->value   = $value;
		$this->page = 'sgdg_' . $page;
		$this->section = 'sgdg_' . $section;
		$this->title   = $title;
	}

	public function add_field() {
		add_settings_field( $this->name, $this->title, [ $this, 'html' ], $this->page, $this->section );
	}

	public function html() {
		echo( '<input type="text" value="' . esc_attr( $this->value ) . '" readonly class="regular-text code">' );
	}
}
