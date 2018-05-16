<?php declare(strict_types=1);
namespace Sgdg\Admin;

if(!is_admin())
{
	return;
}

class ReadonlyStringOption
{
	private $name;
	private $value;
	private $title;
	private $section;

	public function __construct(string $name, string $value, string $section, string $title)
	{
		$this->name =  'sgdg_' . $name;
		$this->value = $value;
		$this->section = 'sgdg_' . $section;
		$this->title = $title;
	}

	public function add_field() : void
	{
		add_settings_field($this->name, esc_html__($this->title, 'skaut-google-drive-gallery'), [$this, 'html'], 'sgdg', $this->section);
	}

	public function html() : void
	{
		echo('<input type="text" value="' . $this->value . '" readonly class="regular-text code">');
	}
}
