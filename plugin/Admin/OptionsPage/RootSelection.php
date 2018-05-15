<?php declare(strict_types=1);
namespace Sgdg\Admin\OptionsPage\RootSelection;

if(!is_admin())
{
	return;
}

function register() : void
{
	add_action('admin_init', '\\Sgdg\\Admin\\OptionsPage\\RootSelection\\add');
}

function add() : void
{
	add_settings_section('sgdg_root_selector', esc_html__('Step 2: Root directory selection', 'skaut-google-drive-gallery'), '\\Sgdg\\Admin\\OptionsPage\\RootSelection\\html', 'sgdg');
	\Sgdg_plugin::$rootPath->register();
}

function html() : void
{
	\Sgdg_plugin::$rootPath->html();
	echo('<table class="widefat">');
	echo('<thead>');
	echo('<tr>');
	echo('<th class="sgdg_root_selector_path"></th>');
	echo('</tr>');
	echo('</thead>');
	echo('<tbody id="sgdg_root_selector_body"></tbody>');
	echo('<tfoot>');
	echo('<tr>');
	echo('<td class="sgdg_root_selector_path"></td>');
	echo('</tr>');
	echo('</tfoot>');
	echo('</table>');
}
