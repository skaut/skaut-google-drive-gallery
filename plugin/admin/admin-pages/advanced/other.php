<?php
namespace Sgdg\Admin\AdminPages\Advanced\Other;

if ( ! is_admin() ) {
	return;
}

function register() {
	add_action( 'admin_init', '\\Sgdg\\Admin\\AdminPages\\Advanced\\Other\\add' );
}

function add() {
	add_settings_section( 'sgdg_options', esc_html__( 'Step 3: Other options', 'skaut-google-drive-gallery' ), '\\Sgdg\\Admin\\AdminPages\\Advanced\\Other\\html', 'sgdg_advanced' );
	\Sgdg\Options::$preview_size->add_field();
	\Sgdg\Options::$preview_speed->add_field();
	\Sgdg\Options::$preview_arrows->add_field();
	\Sgdg\Options::$preview_close_button->add_field();
	\Sgdg\Options::$preview_loop->add_field();
	\Sgdg\Options::$preview_activity_indicator->add_field();
}

function html() {}
