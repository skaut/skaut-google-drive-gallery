<?php
namespace Sgdg\Frontend;

class Options_Proxy {
	public $overriden;
	private $option_list;

	public function __construct( $overriden = [] ) {
		$this->option_list = [
			'grid_height'                => \Sgdg\Options::$grid_height,
			'grid_spacing'               => \Sgdg\Options::$grid_spacing,
			'dir_title_size'             => \Sgdg\Options::$dir_title_size,
			'dir_counts'                 => \Sgdg\Options::$dir_counts,
			'image_ordering'             => \Sgdg\Options::$image_ordering,
			'dir_ordering'               => \Sgdg\Options::$dir_ordering,

			'preview_size'               => \Sgdg\Options::$preview_size,
			'preview_speed'              => \Sgdg\Options::$preview_speed,
			'preview_arrows'             => \Sgdg\Options::$preview_arrows,
			'preview_close_button'       => \Sgdg\Options::$preview_close_button,
			'preview_loop'               => \Sgdg\Options::$preview_loop,
			'preview_activity_indicator' => \Sgdg\Options::$preview_activity_indicator,
		];
		$this->overriden   = [];
		if ( is_array( $overriden ) ) {
			foreach ( $overriden as $key => $value ) {
				if ( array_key_exists( $key, $this->option_list ) ) {
					$this->overriden[ $key ] = $value;
				}
			}
		}
	}

	public function get( $name, $default_value = null ) {
		if ( array_key_exists( $name, $this->overriden ) ) {
			return $this->overriden[ $name ];
		}
		if ( array_key_exists( $name, $this->option_list ) ) {
			return $this->option_list[ $name ]->get( $default_value );
		}
		return $default_value;
	}
}
