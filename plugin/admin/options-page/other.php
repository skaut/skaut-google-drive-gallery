<?php
namespace Sgdg\Admin\OptionsPage\Other;

if ( ! is_admin() ) {
	return;
}

function register() {
	add_action( 'admin_init', '\\Sgdg\\Admin\\OptionsPage\\Other\\add' );
	add_action( 'admin_enqueue_scripts', '\\Sgdg\\Admin\\OptionsPage\\Other\\register_scripts_styles' );
}

function add() {
	add_settings_section( 'sgdg_options', esc_html__( 'Step 3: Other options', 'skaut-google-drive-gallery' ), '\\Sgdg\\Admin\\OptionsPage\\Other\\html', 'sgdg_advanced' );
	\Sgdg\Options::$grid_mode->add_field();
	\Sgdg\Options::$grid_spacing->add_field();
	\Sgdg\Options::$preview_size->add_field();
	\Sgdg\Options::$preview_speed->add_field();
	\Sgdg\Options::$dir_counts->add_field();
	\Sgdg\Options::$preview_arrows->add_field();
	\Sgdg\Options::$preview_close_button->add_field();
	\Sgdg\Options::$preview_loop->add_field();
	\Sgdg\Options::$preview_activity_indicator->add_field();
	\Sgdg\Options::$image_ordering->add_field();
	\Sgdg\Options::$dir_ordering->add_field();
}

function register_scripts_styles() {
	wp_enqueue_style( 'sgdg_options_other', plugins_url( '/skaut-google-drive-gallery/admin/css/options-other.css' ) );
}

function html() {}
