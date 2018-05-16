<?php declare(strict_types=1);

defined('WP_UNINSTALL_PLUGIN') or die('Die, die, die!');

delete_option('sgdg_client_id');
delete_option('sgdg_client_secret');
delete_option('sgdg_access_token');
delete_option('sgdg_root_path');
delete_option('sgdg_thumbnail_size');
delete_option('sgdg_thumbnail_spacing');
delete_option('sgdg_preview_size');
delete_option('sgdg_preview_speed');
delete_option('sgdg_preview_arrows');
delete_option('sgdg_preview_closebutton');
delete_option('sgdg_preview_loop');
delete_option('sgdg_preview_activity');
