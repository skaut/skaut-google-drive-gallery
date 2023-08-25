<?php
/**
 * Contains the OAuth_Revoke class.
 *
 * @package skaut-google-drive-gallery
 */

namespace Sgdg\Admin\Settings_Pages\Basic;

use Sgdg\Options;

/**
 * Registers and renders the OAuth revocation settings section.
 *
 * @phan-constructor-used-for-side-effects
 */
final class OAuth_Revoke {

	/**
	 * Register all the hooks for this section.
	 */
	public function __construct() {
		if ( ! is_admin() ) {
			return;
		}

		add_action( 'admin_init', array( self::class, 'add_section' ) );
	}

	/**
	 * Adds the settings section and all the fields in it.
	 *
	 * @return void
	 */
	public static function add_section() {
		add_settings_section(
			'sgdg_auth',
			esc_html__( 'Step 1: Authorization', 'skaut-google-drive-gallery' ),
			array( self::class, 'html' ),
			'sgdg_basic'
		);
		Options::$authorized_domain->add_field();
		Options::$authorized_origin->add_field();
		Options::$redirect_uri->add_field();
		Options::$client_id->add_field( true );
		Options::$client_secret->add_field( true );
	}

	/**
	 * Renders the header for the section.
	 *
	 * @return void
	 */
	public static function html() {
		echo '<a class="button button-primary" href="' .
			esc_url_raw(
				wp_nonce_url( admin_url( 'admin.php?page=sgdg_basic&action=oauth_revoke' ), 'oauth_revoke' )
			) .
			'">' .
			esc_html__( 'Revoke Permission', 'skaut-google-drive-gallery' ) .
			'</a>';
	}
}
