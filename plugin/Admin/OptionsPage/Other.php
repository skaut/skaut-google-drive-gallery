<?php
namespace Sgdg\Admin\OptionsPage\Other;

if(!is_admin())
{
	return;
}

function register()
{
	add_action('admin_init', '\\Sgdg\\Admin\\OptionsPage\\Other\\add');
}

function add()
{
	add_settings_section('sgdg_options', esc_html__('Step 3: Other options', 'skaut-google-drive-gallery'), '\\Sgdg\\Admin\\OptionsPage\\Other\\html', 'sgdg');
	\Sgdg\Options::$thumbnailSize->add_field();
	\Sgdg\Options::$thumbnailSpacing->add_field();
	\Sgdg\Options::$previewSize->add_field();
	\Sgdg\Options::$previewSpeed->add_field();
	\Sgdg\Options::$previewArrows->add_field();
	\Sgdg\Options::$previewCloseButton->add_field();
	\Sgdg\Options::$previewLoop->add_field();
	\Sgdg\Options::$previewActivity->add_field();
}

function html() {}
