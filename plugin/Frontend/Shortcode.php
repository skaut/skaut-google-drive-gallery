<?php declare(strict_types=1);
namespace Sgdg\Frontend\Shortcode;

function register() : void
{
	add_shortcode('sgdg', '\\Sgdg\\Frontend\\Shortcode\\render');
}
function render(array $atts = []) : string
{
	wp_enqueue_script('sgdg_masonry');
	wp_enqueue_script('sgdg_imagesloaded');
	wp_enqueue_script('sgdg_imagelightbox_script');
	wp_enqueue_script('sgdg_gallery_init');
	wp_localize_script('sgdg_gallery_init', 'sgdg_jquery_localize', [
		'thumbnail_size' => \Sgdg_plugin::$thumbnailSize->get(),
		'thumbnail_spacing' => \Sgdg_plugin::$thumbnailSpacing->get(),
		'preview_speed' => \Sgdg_plugin::$previewSpeed->get(),
		'preview_arrows' => \Sgdg_plugin::$previewArrows->get(),
		'preview_closebutton' => \Sgdg_plugin::$previewCloseButton->get(),
		'preview_quitOnEnd' => (get_option('sgdg_preview_loop', \Sgdg_plugin::DEFAULT_PREVIEW_LOOP) === '1' ? 'false' : 'true'),
		'preview_activity' => (get_option('sgdg_preview_activity', \Sgdg_plugin::DEFAULT_PREVIEW_ACTIVITY) === '1' ? 'true' : 'false')
	]);
	wp_enqueue_style('sgdg_imagelightbox_style');
	wp_enqueue_style('sgdg_gallery_css');
	wp_add_inline_style('sgdg_gallery_css', '.grid-item { margin-bottom: ' . intval(\Sgdg_plugin::$thumbnailSpacing->get() - 7) . 'px; width: ' . \Sgdg_plugin::$thumbnailSize->get() . 'px; }');
	if(isset($atts['name']))
	{
		$client = \Sgdg\Frontend\GoogleAPILib\getDriveClient();
		$path = get_option('sgdg_root_dir', ['root']);
		$root = end($path);
		$pageToken = null;
		do
		{
			$optParams = [
				'q' => '"' . $root . '" in parents and trashed = false',
				'supportsTeamDrives' => true,
				'includeTeamDriveItems' => true,
				'pageToken' => $pageToken,
				'pageSize' => 1000,
				'fields' => 'nextPageToken, files(id, name)'
			];
			$response = $client->files->listFiles($optParams);
			foreach($response->getFiles() as $file)
			{
				if($file->getName() == $atts['name'])
				{
					return render_gallery($file->getId());
				}
			}
			$pageToken = $response->pageToken;
		}
		while($pageToken != null);
	}
	return esc_html__('No such gallery found.', 'skaut-google-drive-gallery');
}

function render_gallery($id) : string
{
	$client = \Sgdg\Frontend\GoogleAPILib\getDriveClient();
	$ret = '<div class="grid">';
	$pageToken = null;
	do
	{
		$optParams = [
			'q' => '"' . $id . '" in parents and mimeType contains "image/" and trashed = false',
			'supportsTeamDrives' => true,
			'includeTeamDriveItems' => true,
			'pageToken' => $pageToken,
			'pageSize' => 1000,
			'fields' => 'nextPageToken, files(thumbnailLink)'
		];
		$response = $client->files->listFiles($optParams);
		foreach($response->getFiles() as $file)
		{
			$ret .= '<div class="grid-item"><a class="sgdg-grid-a" data-imagelightbox="a" href="' . substr($file->getThumbnailLink(), 0, -3) . \Sgdg_plugin::$previewSize->get() . '"><img class="sgdg-grid-img" src="' . substr($file->getThumbnailLink(), 0, -4) . 'w' . \Sgdg_plugin::$thumbnailSize->get() . '"></a></div>';
		}
		$pageToken = $response->pageToken;
	}
	while($pageToken != null);
	$ret .= '</div>';
	return $ret;
}
