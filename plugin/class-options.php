<?php
namespace Sgdg;

require_once 'frontend/class-integeroption.php';
require_once 'frontend/class-booleanoption.php';
require_once 'frontend/class-stringcodeoption.php';
require_once 'frontend/class-arrayoption.php';
require_once 'frontend/class-rootpathoption.php';
require_once 'frontend/class-orderingoption.php';
require_once 'frontend/class-multioption.php';

class Options {
	public static $authorized_origin;
	public static $redirect_uri;
	public static $client_id;
	public static $client_secret;
	public static $root_path;
	public static $grid_mode;
	public static $grid_width;
	public static $grid_columns;
	public static $grid_min_width;
	public static $grid_spacing;
	public static $preview_size;
	public static $preview_speed;
	public static $dir_counts;
	public static $preview_arrows;
	public static $preview_close_button;
	public static $preview_loop;
	public static $preview_activity_indicator;
	public static $image_ordering;
	public static $dir_ordering;

	public static function init() {
		self::$authorized_origin          = new \Sgdg\Admin\ReadonlyStringOption( 'origin', get_site_url(), 'auth', esc_html__( 'Authorised JavaScript origin', 'skaut-google-drive-gallery' ) );
		self::$redirect_uri               = new \Sgdg\Admin\ReadonlyStringOption( 'redirect_uri', esc_url_raw( admin_url( 'options-general.php?page=sgdg&action=oauth_redirect' ) ), 'auth', esc_html__( 'Authorised redirect URI', 'skaut-google-drive-gallery' ) );
		self::$client_id                  = new \Sgdg\Frontend\StringCodeOption( 'client_id', '', 'auth', esc_html__( 'Client ID', 'skaut-google-drive-gallery' ) );
		self::$client_secret              = new \Sgdg\Frontend\StringCodeOption( 'client_secret', '', 'auth', esc_html__( 'Client secret', 'skaut-google-drive-gallery' ) );
		self::$root_path                  = new \Sgdg\Frontend\RootPathOption( 'root_path', [ 'root' ], 'root_selection', '' );
		self::$grid_mode                  = new \Sgdg\Frontend\MultiOption( 'grid_mode', [
			'fixed'   => esc_html__( 'Fixed width', 'skaut-google-drive-gallery' ),
			'dynamic' => esc_html__( 'Dynamic width', 'skaut-google-drive-gallery' ),
		], 'fixed', 'options', esc_html__( 'Gallery column width', 'skaut-google-drive-gallery' ) );
		self::$grid_width                 = new \Sgdg\Frontend\IntegerOption( 'grid_width', 250, 'options', esc_html__( 'Column width', 'skaut-google-drive-gallery' ) );
		self::$grid_columns               = new \Sgdg\Frontend\IntegerOption( 'grid_columns', 3, 'options', esc_html__( 'Number of columns', 'skaut-google-drive-gallery' ) );
		self::$grid_min_width             = new \Sgdg\Frontend\IntegerOption( 'grid_min_width', 250, 'options', esc_html__( 'Minimum column width', 'skaut-google-drive-gallery' ) );
		self::$grid_spacing               = new \Sgdg\Frontend\IntegerOption( 'grid_spacing', 10, 'options', esc_html__( 'Item spacing', 'skaut-google-drive-gallery' ) );
		self::$preview_size               = new \Sgdg\Frontend\IntegerOption( 'preview_size', 1920, 'options', esc_html__( 'Preview size', 'skaut-google-drive-gallery' ) );
		self::$preview_speed              = new \Sgdg\Frontend\IntegerOption( 'preview_speed', 250, 'options', esc_html__( 'Preview animation speed (ms)', 'skaut-google-drive-gallery' ) );
		self::$dir_counts                 = new \Sgdg\Frontend\BooleanOption( 'dir_counts', true, 'options', esc_html__( 'Directory item counts', 'skaut-google-drive-gallery' ) );
		self::$preview_arrows             = new \Sgdg\Frontend\BooleanOption( 'preview_arrows', true, 'options', esc_html__( 'Preview arrows', 'skaut-google-drive-gallery' ) );
		self::$preview_close_button       = new \Sgdg\Frontend\BooleanOption( 'preview_closebutton', true, 'options', esc_html__( 'Preview close button', 'skaut-google-drive-gallery' ) );
		self::$preview_loop               = new \Sgdg\Frontend\BooleanOption( 'preview_loop', false, 'options', esc_html__( 'Loop preview', 'skaut-google-drive-gallery' ) );
		self::$preview_activity_indicator = new \Sgdg\Frontend\BooleanOption( 'preview_activity', true, 'options', esc_html__( 'Preview activity indicator', 'skaut-google-drive-gallery' ) );
		self::$image_ordering             = new \Sgdg\Frontend\OrderingOption( 'image_ordering', 'date', 'ascending', 'options', esc_html__( 'Image ordering', 'skaut-google-drive-gallery' ) );
		self::$dir_ordering               = new \Sgdg\Frontend\OrderingOption( 'date_ordering', 'date', 'descending', 'options', esc_html__( 'Directory ordering', 'skaut-google-drive-gallery' ) );

		self::$grid_mode->addSubOption( 'fixed', self::$grid_width );
		self::$grid_mode->addSubOption( 'dynamic', self::$grid_columns );
		self::$grid_mode->addSubOption( 'dynamic', self::$grid_min_width );
	}
}
