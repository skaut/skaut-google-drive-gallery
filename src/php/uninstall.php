<?php
/**
 * Plugin uninstallation file.
 *
 * Deletes all the plugin options so that the database is clean after uninstall.
 *
 * @package skaut-google-drive-gallery
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die( 'Die, die, die!' );
}

/**
 * Deletes all the plugin options for the current site.
 *
 * @return void
 */
function sgdg_uninstall_delete_options() {
	foreach (
		array(
			'sgdg_client_id',
			'sgdg_client_secret',
			'sgdg_access_token',
			'sgdg_root_path',
			'sgdg_grid_height',
			'sgdg_grid_spacing',
			'sgdg_dir_title_size',
			'sgdg_dir_counts',
			'sgdg_page_size',
			'sgdg_page_autoload',
			'sgdg_page_navigation',
			'sgdg_image_ordering_order',
			'sgdg_image_ordering_by',
			'sgdg_dir_ordering_order',
			'sgdg_dir_ordering_by',
			'sgdg_dir_prefix',
			'sgdg_video_names',
			'sgdg_preview_size',
			'sgdg_preview_speed',
			'sgdg_preview_arrows',
			'sgdg_preview_closebutton',
			'sgdg_preview_loop',
			'sgdg_preview_activity',
			'sgdg_preview_captions',
			'sgdg_allow_download',
			// Deprecated.
			'sgdg_image_ordering',
			'sgdg_thumbnail_size',
			'sgdg_thumbnail_size_value',
			'sgdg_thumbnail_size_unit',
			'sgdg_thumbnail_spacing',
			'sgdg_date_ordering_order',
			'sgdg_date_ordering_by',
			'sgdg_grid_mode',
			'sgdg_grid_width',
			'sgdg_grid_columns',
			'sgdg_grid_min_width',
		) as $option
	) {
		delete_option( $option );
	}

	// The configurations of the galleries.
	global $wpdb;
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
			$wpdb->esc_like( 'sgdg_gallery_' ) . '%'
		)
	);
}

if ( is_multisite() ) {
	foreach ( get_sites(
		array(
			'fields' => 'ids',
			'number' => 0,
		)
	) as $sgdg_site_id ) {
		switch_to_blog( intval( $sgdg_site_id ) );
		sgdg_uninstall_delete_options();
		restore_current_blog();
	}
} else {
	sgdg_uninstall_delete_options();
}
