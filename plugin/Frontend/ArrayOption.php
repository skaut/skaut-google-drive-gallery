<?php declare(strict_types=1);
namespace Sgdg\Frontend;

require_once('Option.php');

class ArrayOption extends Option
{
	public function __construct(string $name, array $defaultValue, string $section, string $title)
	{
		parent::__construct($name, $defaultValue, $section, $title);
	}

	public function register() : void
	{
		register_setting('sgdg', $this->name, ['type' => 'string', 'sanitize_callback' => [$this, 'sanitize']]);
	}

	public function sanitize($value) : array
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

	public function html() : void
	{
		echo('<input id="' . $this->name . '" type="hidden" name="' . $this->name . '" value="' . htmlentities(json_encode($this->get(), JSON_UNESCAPED_UNICODE)) . '">');
	}
}
