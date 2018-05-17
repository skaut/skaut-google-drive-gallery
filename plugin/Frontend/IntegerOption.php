<?php
namespace Sgdg\Frontend;

require_once('Option.php');

class IntegerOption extends Option
{
	public function __construct($name, $defaultValue, $section, $title)
	{
		parent::__construct($name, $defaultValue, $section, $title);
	}

	public function register()
	{
		register_setting('sgdg', $this->name, ['type' => 'integer', 'sanitize_callback' => [$this, 'sanitize']]);
	}

	public function sanitize($value)
	{
		if(is_numeric($value))
		{
			return intval($value);
		}
		return $this->defaultValue;
	}

	public function html()
	{
		echo('<input type="text" name="' . $this->name . '" value="' . esc_attr(get_option($this->name, $this->defaultValue)) . '" class="regular-text">');
	}
}
