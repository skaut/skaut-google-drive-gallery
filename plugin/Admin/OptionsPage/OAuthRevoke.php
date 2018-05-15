<?php declare(strict_types=1);
namespace Sgdg\Admin\OptionsPage\OAuthRevoke;

if(!is_admin())
{
	return;
}

function register() : void
{
	add_action('admin_init', '\\Sgdg\\Admin\\OptionsPage\\OAuthRevoke\\add');
}

function add() : void
{
	add_settings_section('sgdg_auth', esc_html__('Step 1: Authorization', 'skaut-google-drive-gallery'), '\\Sgdg\\Admin\\OptionsPage\\OAuthRevoke\\html', 'sgdg');
	add_settings_field('sgdg_redirect_uri', esc_html__('Authorized redirect URL', 'skaut-google-drive-gallery'), ['Sgdg_plugin', 'redirect_uri_html'], 'sgdg', 'sgdg_auth');
	\Sgdg_plugin::$clientID->add_field(true);
	\Sgdg_plugin::$clientSecret->add_field(true);
}

function html() : void
{
	echo('<a class="button button-primary" href="' . esc_url_raw(admin_url('options-general.php?page=sgdg&action=oauth_revoke')) . '">' . esc_html__('Revoke Permission', 'skaut-google-drive-gallery') . '</a>');
}
