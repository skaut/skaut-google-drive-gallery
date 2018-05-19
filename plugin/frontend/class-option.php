<?php
namespace Sgdg\Frontend;

abstract class Option
{
	protected $name;
	protected $defaultValue;
	protected $section;
	protected $title;

	public function __construct($name, $defaultValue, $section, $title)
	{
		$this->name =  'sgdg_' . $name;
		$this->defaultValue = $defaultValue;
		$this->section = 'sgdg_' . $section;
		$this->title = $title;
	}

	abstract public function register();

	public function sanitize($value)
	{
		return $value;
	}

	public function add_field()
	{
		$this->register();
		add_settings_field($this->name, $this->title, [$this, 'html'], 'sgdg', $this->section);
	}

	abstract public function html();

	public function get($defaultValue = null)
	{
		return get_option($this->name, (isset($defaultValue) ? $defaultValue : $this->defaultValue));
	}
}
