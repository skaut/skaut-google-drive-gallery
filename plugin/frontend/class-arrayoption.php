<?php
namespace Sgdg\Frontend;

require_once('class-option.php');

class ArrayOption extends Option
{
	public function __construct($name, array $defaultValue, $section, $title)
	{
		parent::__construct($name, $defaultValue, $section, $title);
	}

	public function register()
	{
		register_setting('sgdg', $this->name, ['type' => 'string', 'sanitize_callback' => [$this, 'sanitize']]);
	}

	public function sanitize($value)
	{
		if(is_string($value))
		{
			$value = json_decode($value, true);
		}
		if($value === null)
		{
			$value = $this->defaultValue;
		}
		return $value;
	}

	public function html()
	{
		echo('<input id="' . $this->name . '" type="hidden" name="' . $this->name . '" value="' . htmlentities(json_encode($this->get(), JSON_UNESCAPED_UNICODE)) . '">');
	}
}
