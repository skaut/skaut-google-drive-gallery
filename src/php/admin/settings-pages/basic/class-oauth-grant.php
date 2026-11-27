<?php
/**
 * Contains the OAuth_Grant class.
 *
 * @package skaut-google-drive-gallery
 */

namespace Sgdg\Admin\Settings_Pages\Basic;

use Sgdg\Options;

/**
 * Registers and renders the OAuth granting settings section.
 *
 * @phan-constructor-used-for-side-effects
 */
final class OAuth_Grant {

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
		Options::$client_id->add_field();
		Options::$client_secret->add_field();
	}

	/**
	 * Renders the header for the section.
	 *
	 * @return void
	 */
	public static function html() {
		if ( ! self::is_redirect_uri_accepted() ) {
			echo '<div class="notice notice-warning inline"><p>' .
				esc_html__( 'This website doesn’t use HTTPS.', 'skaut-google-drive-gallery' ) .
				' ' .
				esc_html__(
					'Google only accepts HTTPS redirect URIs (except for localhost), so granting permission will fail.',
					'skaut-google-drive-gallery'
				) .
				' ' .
				esc_html__(
					'Switch the website to HTTPS first and then use the redirect URI shown below.',
					'skaut-google-drive-gallery'
				) .
				'</p></div>';
		}

		echo '<p>' .
			esc_html__( 'Create a Google app and provide the following details:', 'skaut-google-drive-gallery' ) .
			'</p>';
		echo '<a class="button button-primary" href="' .
			esc_url_raw( wp_nonce_url( admin_url( 'admin.php?page=sgdg_basic&action=oauth_grant' ), 'oauth_grant' ) ) .
			'">' .
			esc_html__( 'Grant Permission', 'skaut-google-drive-gallery' ) .
			'</a>';
	}

	/**
	 * Checks whether Google accepts the redirect URI of the plugin.
	 *
	 * Google only accepts redirect URIs with HTTPS, except for localhost.
	 *
	 * @see https://developers.google.com/identity/protocols/oauth2/web-server#uri-validation
	 *
	 * @return bool Whether the redirect URI is accepted.
	 */
	private static function is_redirect_uri_accepted() {
		$url = wp_parse_url( admin_url() );

		if ( ! is_array( $url ) || ! isset( $url['scheme'], $url['host'] ) || 'https' === $url['scheme'] ) {
			return true;
		}

		return in_array( $url['host'], array( 'localhost', '127.0.0.1', '[::1]' ), true );
	}
}
