<?php
namespace Sgdg\Admin\OptionsPage\OAuthGrant;

if(!is_admin())
{
	return;
}

function register()
{
	add_action('admin_init', '\\Sgdg\\Admin\\OptionsPage\\OAuthGrant\\add');
}

function add()
{
	add_settings_section('sgdg_auth', esc_html__('Step 1: Authorization', 'skaut-google-drive-gallery'), '\\Sgdg\\Admin\\OptionsPage\\OAuthGrant\\html', 'sgdg');
	\Sgdg\Options::$authorized_origin->add_field();
	\Sgdg\Options::$redirect_uri->add_field();
	\Sgdg\Options::$client_id->add_field();
	\Sgdg\Options::$client_secret->add_field();
}

function html()
{
	echo('<p>' . esc_html__('Create a Google app and provide the following details:', 'skaut-google-drive-gallery') . '</p>');
	echo('<a class="button button-primary" href="' . esc_url_raw(admin_url('options-general.php?page=sgdg&action=oauth_grant')) . '">' . esc_html__('Grant Permission', 'skaut-google-drive-gallery') . '</a>');
}
