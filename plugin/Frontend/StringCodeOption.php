<?php declare(strict_types=1);
namespace Sgdg\Frontend;

require_once('Option.php');

class StringCodeOption extends Option
{
	private $readonly;

	public function __construct(string $name, string $defaultValue, string $section, string $title)
	{
		parent::__construct($name, $defaultValue, $section, $title);
		$this->readonly = false;
	}

	public function register() : void
	{
		register_setting('sgdg', $this->name, ['type' => 'string', 'sanitize_callback' => [$this, 'sanitize']]);
	}

	public function add_field(bool $readonly = false) : void
	{
		$this->readonly = $readonly;
		parent::add_field();
	}

	public function html() : void
	{
		echo('<input type="text" name="' . $this->name . '" value="' . get_option($this->name, $this->defaultValue) . '" ' . ($this->readonly ? 'readonly ' : '') . 'class="regular-text code">');
	}
}
