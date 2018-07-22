<?php
namespace Sgdg\Frontend\Shortcode;

function register() {
	add_action( 'init', '\\Sgdg\\Frontend\\Shortcode\\add' );
	add_action( 'wp_enqueue_scripts', '\\Sgdg\\Frontend\\Shortcode\\register_scripts_styles' );
}

function add() {
	add_shortcode( 'sgdg', '\\Sgdg\\Frontend\\Shortcode\\render' );
}

function register_scripts_styles() {
	wp_register_script( 'sgdg_gallery_init', plugins_url( '/skaut-google-drive-gallery/frontend/js/shortcode.js' ), [ 'jquery' ] );
	wp_register_style( 'sgdg_gallery_css', plugins_url( '/skaut-google-drive-gallery/frontend/css/shortcode.css' ) );

	wp_register_script( 'sgdg_imagelightbox_script', plugins_url( '/skaut-google-drive-gallery/bundled/imagelightbox.min.js' ), [ 'jquery' ] );
	wp_register_style( 'sgdg_imagelightbox_style', plugins_url( '/skaut-google-drive-gallery/bundled/imagelightbox.min.css' ) );
	wp_register_script( 'sgdg_imagesloaded', plugins_url( '/skaut-google-drive-gallery/bundled/imagesloaded.pkgd.min.js' ), [ 'jquery' ] );
	wp_register_script( 'sgdg_justified-layout', plugins_url( '/skaut-google-drive-gallery/bundled/justified-layout.min.js' ) );
}

function render( $atts = [] ) {
	define( 'DONOTCACHEPAGE', true );
	wp_enqueue_script( 'sgdg_imagelightbox_script' );
	wp_enqueue_style( 'sgdg_imagelightbox_style' );
	wp_enqueue_script( 'sgdg_imagesloaded' );
	wp_enqueue_script( 'sgdg_justified-layout' );

	wp_enqueue_script( 'sgdg_gallery_init' );
	wp_localize_script( 'sgdg_gallery_init', 'sgdgShortcodeLocalize', [
		'grid_height'         => \Sgdg\Options::$grid_height->get(),
		'grid_spacing'        => \Sgdg\Options::$grid_spacing->get(),
		'preview_speed'       => \Sgdg\Options::$preview_speed->get(),
		'preview_arrows'      => \Sgdg\Options::$preview_arrows->get(),
		'preview_closebutton' => \Sgdg\Options::$preview_close_button->get(),
		'preview_quitOnEnd'   => \Sgdg\Options::$preview_loop->get_inverted(),
		'preview_activity'    => \Sgdg\Options::$preview_activity_indicator->get(),
	]);
	wp_enqueue_style( 'sgdg_gallery_css' );
	wp_add_inline_style( 'sgdg_gallery_css', '.sgdg-dir-name {font-size: ' . \Sgdg\Options::$dir_title_size->get() . ';}' );

	$keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$nonce    = '';
	for ( $i = 0; $i < 128; $i++ ) {
		$nonce .= $keyspace[ wp_rand( 0, strlen( $keyspace ) - 1 ) ];
	}
	$value = isset( $atts['path'] ) ? $atts['path'] : [];

	set_transient( 'sgdg_nonce_' . $nonce, $value, 2 * HOUR_IN_SECONDS );
	return '<div id="sgdg-gallery", data-sgdg-nonce="' . $nonce . '"></div>';
}
