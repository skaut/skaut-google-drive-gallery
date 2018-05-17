<?php
namespace Sgdg\Frontend;

require_once('Option.php');

class StringCodeOption extends Option
{
	private $readonly;

	public function __construct($name, $defaultValue, $section, $title)
	{
		parent::__construct($name, $defaultValue, $section, $title);
		$this->readonly = false;
	}

	public function register()
	{
		register_setting('sgdg', $this->name, ['type' => 'string', 'sanitize_callback' => [$this, 'sanitize']]);
	}

	public function add_field($readonly = false)
	{
		$this->readonly = $readonly;
		parent::add_field();
	}

	public function html()
	{
		echo('<input type="text" name="' . $this->name . '" value="' . get_option($this->name, $this->defaultValue) . '" ' . ($this->readonly ? 'readonly ' : '') . 'class="regular-text code">');
	}
}
