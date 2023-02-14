<?php
/**
 * Contains the Options_Proxy class
 *
 * @package skaut-google-drive-gallery
 */

namespace Sgdg\Frontend;

use Sgdg\Frontend\Boolean_Option;
use Sgdg\Frontend\Bounded_Integer_Option;
use Sgdg\Frontend\Integer_Option;
use Sgdg\Frontend\Ordering_Option;
use Sgdg\Frontend\String_Option;
use Sgdg\Options;

/**
 * A proxy for overridable options
 *
 * Servers as a proxy for all options overridable on a case-by-case basis (in a shortcode or a block). Returns the overriden values or the global values if the option isn't overriden.
 */
final class Options_Proxy {

	/**
	 * A list of all currently overriden options.
	 *
	 * @var array<int|string> $overriden {
	 *     All the fields are optional.
	 *
	 *     @type int    $grid_height The height of a row in the image grid.
	 *     @type int    $grid_spacing Item spacing in the image grid.
	 *     @type string $dir_title_size Directory title size, including CSS units.
	 *     @type string $dir_counts Whether to show directory item counts. Accepts `true`, `false`.
	 *     @type int    $page_size Number of items per 1 page.
	 *     @type string $page_autoload Whether to autoload new images. Accepts `true`, `false`.
	 *     @type string $dir_prefix A prefix separator to cut a prefix from the start of all directory names.
	 *     @type int    $preview_size Maximum size of an image in the lightbox.
	 *     @type int    $preview_speed Lightbox animation speed.
	 *     @type string $preview_arrows Whether to show lightbox navigation arrows. Accepts `true`, `false`.
	 *     @type string $preview_close_button Whether to show lightbox close button. Accepts `true`, `false`.
	 *     @type string $preview_loop Whether to loop the images in the lightbox. Accepts `true`, `false`.
	 *     @type string $preview_activity_indicator Whether to show an activity indicator while the lightbox is loading. Accepts `true`, `false`.
	 *     @type string $preview_captions Whether to show image captions in the lightbox. Accepts `true`, `false`.
	 *     @type string $image_ordering_by What to order images by. Accepts `name`, `time`.
	 *     @type string $image_ordering_order Whether to order images in ascending or descending order. Accepts `ascending`, `descending`.
	 *     @type string $dir_ordering_by What to order images by. Accepts `name`, `time`.
	 *     @type string $dir_ordering_order Whether to order images in ascending or descending order. Accepts `ascending`, `descending`.
	 * }
	 */
	private $overriden;

	/**
	 * All the options that can be overriden, except for ordering options.
	 *
	 * @see $ordering_option_list
	 *
	 * @var array{grid_height: Bounded_Integer_Option, grid_spacing: Integer_Option, dir_title_size: String_Option, dir_counts: Boolean_Option, page_size: Bounded_Integer_Option, page_autoload: Boolean_Option, dir_prefix: String_Option, preview_size: Bounded_Integer_Option, preview_speed: Bounded_Integer_Option, preview_arrows: Boolean_Option, preview_close_button: Boolean_Option, preview_loop: Boolean_Option, preview_activity_indicator: Boolean_Option, preview_captions: Boolean_Option} $option_list {
	 *     All the fields are mandatory.
	 *
	 *     @type Bounded_Integer_Option $grid_height The height of a row in the image grid.
	 *     @type Integer_Option         $grid_spacing Item spacing in the image grid.
	 *     @type String_Option          $dir_title_size Directory title size.
	 *     @type Boolean_Option         $dir_counts Whether to show directory item counts.
	 *     @type Bounded_Integer_Option $page_size Number of items per 1 page.
	 *     @type Boolean_Option         $page_autoload Whether to autoload new images.
	 *     @type String_Option          $dir_prefix A prefix separator to cut a prefix from the start of all directory names.
	 *     @type Bounded_Integer_Option $preview_size Maximum size of an image in the lightbox.
	 *     @type Bounded_Integer_Option $preview_speed Lightbox animation speed.
	 *     @type Boolean_Option         $preview_arrows Whether to show lightbox navigation arrows.
	 *     @type Boolean_Option         $preview_close_button Whether to show lightbox close button.
	 *     @type Boolean_Option         $preview_loop Whether to loop the images in the lightbox.
	 *     @type Boolean_Option         $preview_activity_indicator Whether to show an activity indicator while the lightbox is loading.
	 *     @type Boolean_Option         $preview_activity_captions Whether to show image captions in the lightbox.
	 * }
	 */
	private $option_list;

	/**
	 * All the ordering options that can be overriden.
	 *
	 * @see $option_list
	 *
	 * @var array{image_ordering: Ordering_Option, dir_ordering: Ordering_Option} $ordering_option_list {
	 *     All the fields are mandatory.
	 *
	 *     @type Ordering_Option $image_ordering How to order images in the gallery.
	 *     @type Ordering_Option $dir_ordering How to order directories in the gallery.
	 * }
	 */
	private $ordering_option_list;

	/**
	 * Option class constructor.
	 *
	 * @param array<int|string> $overriden {
	 *     A list of options to override.
	 *
	 *     @see $overriden
	 * }
	 */
	public function __construct( $overriden = array() ) {
		$this->option_list          = array(
			'grid_height'                => Options::$grid_height,
			'grid_spacing'               => Options::$grid_spacing,
			'dir_title_size'             => Options::$dir_title_size,
			'dir_counts'                 => Options::$dir_counts,
			'page_size'                  => Options::$page_size,
			'page_autoload'              => Options::$page_autoload,
			'dir_prefix'                 => Options::$dir_prefix,

			'preview_size'               => Options::$preview_size,
			'preview_speed'              => Options::$preview_speed,
			'preview_arrows'             => Options::$preview_arrows,
			'preview_close_button'       => Options::$preview_close_button,
			'preview_loop'               => Options::$preview_loop,
			'preview_activity_indicator' => Options::$preview_activity_indicator,
			'preview_captions'           => Options::$preview_captions,
		);
		$this->ordering_option_list = array(
			'image_ordering' => Options::$image_ordering,
			'dir_ordering'   => Options::$dir_ordering,
		);
		$this->overriden            = array();

		if ( ! is_array( $overriden ) ) {
			return;
		}

		foreach ( $overriden as $key => $value ) {
			if ( array_key_exists( $key, $this->option_list ) ) {
				$this->overriden[ $key ] = $value;
			}

			if (
				'_order' === substr( $key, -6 ) &&
				array_key_exists( substr( $key, 0, -6 ), $this->ordering_option_list )
			) {
				$this->overriden[ $key ] = $value;
			}

			if (
				'_by' === substr( $key, -3 ) &&
				array_key_exists( substr( $key, 0, -3 ), $this->ordering_option_list )
			) {
				$this->overriden[ $key ] = $value;
			}
		}
	}

	/**
	 * Gets a value of an option.
	 *
	 * Returns the overriden value if it exists, otherwise returns the global value.
	 *
	 * @see \Sgdg\Frontend\Option::get()
	 *
	 * @param string $name The name of the requested option.
	 * @param mixed  $default_value A default value to return if the option isn't overriden and has no value. If null, the default value from the option will be used. Default null.
	 *
	 * @return mixed The value of the option.
	 */
	public function get( $name, $default_value = null ) {
		if ( array_key_exists( $name, $this->overriden ) ) {
			return $this->overriden[ $name ];
		}

		if (
			array_key_exists( $name . '_order', $this->overriden ) &&
			array_key_exists( $name . '_by', $this->overriden )
		) {
			return ( 'name' === $this->overriden[ $name . '_by' ] ? 'name_natural' : 'modifiedTime' ) .
				( 'ascending' === $this->overriden[ $name . '_order' ] ? '' : ' desc' );
		}

		if ( array_key_exists( $name, $this->option_list ) ) {
			return $this->option_list[ $name ]->get( $default_value );
		}

		if ( array_key_exists( $name, $this->ordering_option_list ) ) {
			return $this->ordering_option_list[ $name ]->get( $default_value );
		}

		return $default_value;
	}

	/**
	 * Gets the title of an option.
	 *
	 * Returns a human-readable name of the option.
	 *
	 * @see \Sgdg\Frontend\Option::get_title()
	 *
	 * @param string $name The name of the requested option.
	 *
	 * @return string|null The name of the option or `null` if no such option is present.
	 */
	public function get_title( $name ) {
		if ( array_key_exists( $name, $this->option_list ) ) {
			return $this->option_list[ $name ]->get_title();
		}

		if ( array_key_exists( $name, $this->ordering_option_list ) ) {
			return $this->ordering_option_list[ $name ]->get_title();
		}

		return null;
	}

	/**
	 * Gets the "order" part of an Ordering_Option.
	 *
	 * Returns the overriden value if it exists, otherwise returns the global value.
	 *
	 * @see Ordering_Option::get_order()
	 *
	 * @param string      $name The name of the requested option.
	 * @param string|null $default_value A default value to return if the option isn't overriden and has no value. If null, the default value from the option will be used. Accepts `ascending`, `descending`, null. Default null.
	 *
	 * @return string|null The "order" part of the option.
	 */
	public function get_order( $name, $default_value = null ) {
		if ( array_key_exists( $name . '_order', $this->overriden ) ) {
			return strval( $this->overriden[ $name . '_order' ] );
		}

		if ( array_key_exists( $name, $this->ordering_option_list ) ) {
			return $this->ordering_option_list[ $name ]->get_order( $default_value );
		}

		return $default_value;
	}

	/**
	 * Gets the "by" part of an Ordering_Option.
	 *
	 * Returns the overriden value if it exists, otherwise returns the global value.
	 *
	 * @see Ordering_Option::get_by()
	 *
	 * @param string      $name The name of the requested option.
	 * @param string|null $default_value A default value to return if the option isn't overriden and has no value. If null, the default value from the option will be used. Accepts `name`, `time`, null. Default null.
	 *
	 * @return string|null The "by" part of the option.
	 */
	public function get_by( $name, $default_value = null ) {
		if ( array_key_exists( $name . '_by', $this->overriden ) ) {
			return strval( $this->overriden[ $name . '_by' ] );
		}

		if ( array_key_exists( $name, $this->ordering_option_list ) ) {
			return $this->ordering_option_list[ $name ]->get_by( $default_value );
		}

		return $default_value;
	}

	/**
	 * Exports all overriden options.
	 *
	 * @return array<int|string> The overriden options.
	 */
	public function export_overriden() {
		return $this->overriden;
	}

}
