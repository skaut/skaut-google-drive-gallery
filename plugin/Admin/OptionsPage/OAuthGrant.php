<?php declare(strict_types=1);
namespace Sgdg\Admin\OptionsPage\OAuthGrant;

if(!is_admin())
{
	return;
}

function register() : void
{
	add_action('admin_init', '\\Sgdg\\Admin\\OptionsPage\\OAuthGrant\\add');
}

function add() : void
{
	add_settings_section('sgdg_auth', esc_html__('Step 1: Authorization', 'skaut-google-drive-gallery'), '\\Sgdg\\Admin\\OptionsPage\\OAuthGrant\\html', 'sgdg');
	add_settings_field('sgdg_redirect_uri', esc_html__('Authorized redirect URL', 'skaut-google-drive-gallery'), ['Sgdg_plugin', 'redirect_uri_html'], 'sgdg', 'sgdg_auth');
	\Sgdg_plugin::$clientID->add_field();
	\Sgdg_plugin::$clientSecret->add_field();
}

function html() : void
{
	echo('<p>' . esc_html__('Create a Google app and provide the following details:', 'skaut-google-drive-gallery') . '</p>');
	echo('<a class="button button-primary" href="' . esc_url_raw(admin_url('options-general.php?page=sgdg&action=oauth_grant')) . '">' . esc_html__('Grant Permission', 'skaut-google-drive-gallery') . '</a>');
}
