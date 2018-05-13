<?php declare(strict_types=1);
namespace Sgdg\Frontend;

require_once('Option.php');

class BooleanOption extends Option
{
	public function __construct(string $name, bool $defaultValue, string $section, string $title)
	{
		parent::__construct($name, ($defaultValue ? '1' : '0'), $section, $title);
	}

	public function register() : void
	{
		register_setting('sgdg', $this->name, ['type' => 'boolean', 'sanitize_callback' => [$this, 'sanitize']]);
	}

	public function sanitize($value) : int
	{
		if(isset($value) && ($value === '1' || $value === 1))
		{
			return 1;
		}
		return 0;
	}

	public function html() : void
	{
		echo('<input type="checkbox" name="' . $this->name . '" value="1"');
		checked(get_option($this->name, $this->defaultValue), '1');
		echo('>');
	}

	public function get() : string
	{
		return (parent::get() === '1' ? 'true' : 'false');
	}

	public function get_inverted() : string
	{
		return (parent::get() === '1' ? 'false' : 'true');
	}
}
