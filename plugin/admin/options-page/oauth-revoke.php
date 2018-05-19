<?php
namespace Sgdg\Admin\OptionsPage\OAuthRevoke;

if ( ! is_admin() ) {
	return;
}

function register() {
	add_action( 'admin_init', '\\Sgdg\\Admin\\OptionsPage\\OAuthRevoke\\add' );
}

function add() {
	add_settings_section( 'sgdg_auth', esc_html__( 'Step 1: Authorization', 'skaut-google-drive-gallery' ), '\\Sgdg\\Admin\\OptionsPage\\OAuthRevoke\\html', 'sgdg' );
	\Sgdg\Options::$authorized_origin->add_field();
	\Sgdg\Options::$redirect_uri->add_field();
	\Sgdg\Options::$client_id->add_field( true );
	\Sgdg\Options::$client_secret->add_field( true );
}

function html() {
	echo( '<a class="button button-primary" href="' . esc_url_raw( admin_url( 'options-general.php?page=sgdg&action=oauth_revoke' ) ) . '">' . esc_html__( 'Revoke Permission', 'skaut-google-drive-gallery' ) . '</a>' );
}
