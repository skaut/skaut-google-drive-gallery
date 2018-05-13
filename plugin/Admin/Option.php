<?php declare(strict_types=1);
namespace Sgdg\Admin;

if(!is_admin() || class_exists('\\Sgdg\\Admin\\Option'))
{
	return;
}


abstract class Option
{
	protected $name;
	protected $defaultValue;
	protected $section;
	protected $title;

	public function __construct(string $name, $defaultValue, string $section, string $title)
	{
		$this->name =  'sgdg_' . $name;
		$this->defaultValue = $defaultValue;
		$this->section = 'sgdg_' . $section;
		$this->title = $title;
		add_action('admin_init', [$this, 'register']);
	}

	abstract public function register() : void;

	public function sanitize($value)
	{
		return $value;
	}

	public function add_field() : void
	{
		add_settings_field($this->name, esc_html__($this->title, 'skaut-google-drive-gallery'), [$this, 'html'], 'sgdg', $this->section);
	}

	abstract public function html() : void;

	public function get()
	{
		return get_option($this->name, $this->defaultValue);
	}
}
