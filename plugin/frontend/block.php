<?php
namespace Sgdg\Frontend\Block;

function register() {
	if ( function_exists( 'register_block_type' ) ) {
		add_action( 'init', '\\Sgdg\\Frontend\\Block\\add' );
	}
}

function add() {
	\Sgdg\enqueue_script( 'sgdg_block_icon', '/frontend/js/iconSvg.js' );
	\Sgdg\enqueue_script( 'sgdg_block_integer_settings_components', '/frontend/js/SgdgIntegerSettingsComponent.js' );
	\Sgdg\enqueue_script( 'sgdg_block_settings_override_component', '/frontend/js/SgdgSettingsOverrideComponent.js' );
	\Sgdg\enqueue_script( 'sgdg_block_editor_component', '/frontend/js/SgdgEditorComponent.js' );
	\Sgdg\register_script( 'sgdg_block', '/frontend/js/block.js', [ 'wp-blocks', 'wp-components', 'wp-editor', 'wp-element', 'sgdg_block_icon', 'sgdg_block_integer_settings_components', 'sgdg_block_settings_override_component', 'sgdg_block_editor_component' ] );
	$options = new \Sgdg\Frontend\Options_Proxy();
	wp_localize_script(
		'sgdg_block',
		'sgdgBlockLocalize',
		[
			'ajax_url'          => admin_url( 'admin-ajax.php' ),
			'nonce'             => wp_create_nonce( 'sgdg_editor_plugin' ),
			'block_name'        => esc_html__( 'Google Drive gallery', 'skaut-google-drive-gallery' ),
			'block_description' => esc_html__( 'A WordPress gallery using Google Drive as file storage', 'skaut-google-drive-gallery' ),
			'root_name'         => esc_html__( 'Google Drive gallery', 'skaut-google-drive-gallery' ),
			'settings_override' => esc_html__( 'Settings override', 'skaut-google-drive-gallery' ),
			'grid_height'       => [
				'default' => $options->get( 'grid_height' ),
				'name'    => esc_html__( 'Row height', 'skaut-google-drive-gallery' ),
			],
		]
	);
	\Sgdg\enqueue_style( 'sgdg_block', '/frontend/css/block.css' );
	register_block_type(
		'skaut-google-drive-gallery/gallery',
		[
			'editor_script'   => 'sgdg_block',
			'render_callback' => '\\Sgdg\\Frontend\\Block\\html',
		]
	);
}

function html( $attributes ) {
	return \Sgdg\Frontend\Shortcode\html( $attributes );
}
