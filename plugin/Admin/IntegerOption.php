<?php declare(strict_types=1);
namespace Sgdg\Admin;

include('Option.php');

if(!is_admin())
{
	return;
}

class IntegerOption extends Option
{
	public function __construct(string $name, int $defaultValue, string $section, string $title)
	{
		parent::__construct($name, $defaultValue, $section, $title);
	}

	public function register() : void
	{
		register_setting('sgdg', $this->name, ['type' => 'integer', 'sanitize_callback' => [$this, 'sanitize']]);
	}

	public function sanitize($value) : int
	{
		if(is_numeric($value))
		{
			return intval($value);
		}
		return $this->defaultValue;
	}

	public function html() : void
	{
		echo('<input type="text" name="' . $this->name . '" value="' . esc_attr(get_option($this->name, $this->defaultValue)) . '" class="regular-text">');
	}
}
