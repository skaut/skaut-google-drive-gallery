<?php
/*
Plugin Name:	Google drive gallery
Plugin URI:		
Description:	A Wordpress gallery using Google drive as file storage
Version:		0.1
Author:			Marek Dědič
Author URI:		
License:		MIT
License URI:	

MIT License

Copyright (c) 2018 Marek Dědič

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
*/

defined('ABSPATH') or die('Die, die, die!');

if(!class_exists('Sgdg_plugin'))
{
	class Sgdg_plugin
	{
		public static function init() : void
		{
			add_action('admin_init', ['Sgdg_plugin', 'plugin_register_settings']);
			add_action('admin_menu', ['Sgdg_plugin', 'plugin_options_page']);
		}

		public static function plugin_options_page() : void
		{
			add_options_page('Google drive gallery', 'Google drive gallery', 'manage_options', 'sgdg', ['Sgdg_plugin', 'options_page_html']);
		}

		public static function plugin_register_settings() : void
		{
			register_setting('sgdg', 'sgdg_setting_name', ['type' => 'string']);
			add_settings_section('sgdg_main_section', 'Hlavní nastavení', ['Sgdg_plugin', 'main_section_top'], 'sgdg');
			add_settings_field('sgdg_setting1', '1. vlastnost', ['Sgdg_plugin', 'setting1_top'], 'sgdg', 'sgdg_main_section');
		}

		function options_page_html() : void
		{
			if (!current_user_can('manage_options'))
			{
				return;
			}
 
			if(isset($_GET['settings-updated']))
			{
			add_settings_error('sgdg_messages', 'sgdg_message', 'Settings Saved', 'updated');
			}
 
			settings_errors('sgdg_messages');
			?>
			<div class="wrap">
				<h1><?php echo(esc_html(get_admin_page_title())); ?></h1>
				<form action="options.php" method="post">
					<?php
					settings_fields('sgdg');
					do_settings_sections('sgdg');
					submit_button('Save Settings');
					?>
				</form>
			</div>
			<?php
		}

		function main_section_top() : void
		{
			echo('<p>Hlavní nastavení</p>');
		}

		function setting1_top() : void
		{
			$setting = get_option('sgdg_setting_name');
			?>
			<input type="text" name="sgdg_setting_name" value="<?php echo(isset($setting) ? esc_attr($setting) : ''); ?>">
			<?php
		}
	}

	Sgdg_plugin::init();
}
