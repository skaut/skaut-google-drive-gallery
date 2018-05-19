<?php
namespace Sgdg\Frontend;

require_once('class-arrayoption.php');

class RootPathOption extends ArrayOption
{
	public function sanitize($value)
	{
		$value = parent::sanitize($value);
		if(count($value) == 0)
		{
			$value = $this->defaultValue;
		}
		return $value;
	}
}
