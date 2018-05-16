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
	\Sgdg\Options::$authorizedOrigin->add_field();
	\Sgdg\Options::$redirectURI->add_field();
	\Sgdg\Options::$clientID->add_field(true);
	\Sgdg\Options::$clientSecret->add_field(true);
}

function html() : void
{
	echo('<a class="button button-primary" href="' . esc_url_raw(admin_url('options-general.php?page=sgdg&action=oauth_revoke')) . '">' . esc_html__('Revoke Permission', 'skaut-google-drive-gallery') . '</a>');
}
