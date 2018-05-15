<?php declare(strict_types=1);
namespace Sgdg\Admin\OptionsPage\Other;

if(!is_admin())
{
	return;
}

function register() : void
{
	add_action('admin_init', '\\Sgdg\\Admin\\OptionsPage\\Other\\add');
}

function add() : void
{
	add_settings_section('sgdg_options', esc_html__('Step 3: Other options', 'skaut-google-drive-gallery'), '\\Sgdg\\Admin\\OptionsPage\\Other\\html', 'sgdg');
	\Sgdg_plugin::$thumbnailSize->add_field();
	\Sgdg_plugin::$thumbnailSpacing->add_field();
	\Sgdg_plugin::$previewSize->add_field();
	\Sgdg_plugin::$previewSpeed->add_field();
	\Sgdg_plugin::$previewArrows->add_field();
	\Sgdg_plugin::$previewCloseButton->add_field();
	\Sgdg_plugin::$previewLoop->add_field();
	\Sgdg_plugin::$previewActivity->add_field();
}

function html() : void {}
