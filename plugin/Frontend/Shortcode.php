<?php
namespace Sgdg\Frontend\Shortcode;

function register()
{
	add_action('init', '\\Sgdg\\Frontend\\Shortcode\\add');
	add_action('wp_enqueue_scripts', '\\Sgdg\\Frontend\\Shortcode\\register_scripts_styles');
	add_action('wp_ajax_list_gallery_dir', '\\Sgdg\\Frontend\\Shortcode\\handle_ajax');
	add_action('wp_ajax_nopriv_list_gallery_dir', '\\Sgdg\\Frontend\\Shortcode\\handle_ajax');
}

function add()
{
	add_shortcode('sgdg', '\\Sgdg\\Frontend\\Shortcode\\render');
}

function register_scripts_styles()
{
	wp_register_script('sgdg_gallery_init', plugins_url('/skaut-google-drive-gallery/Frontend/js/gallery_init.js'), ['jquery']);
	wp_register_style('sgdg_gallery_css', plugins_url('/skaut-google-drive-gallery/Frontend/css/gallery.css'));

	wp_register_script('sgdg_masonry', plugins_url('/skaut-google-drive-gallery/bundled/masonry.pkgd.min.js'), ['jquery']);
	wp_register_script('sgdg_imagesloaded', plugins_url('/skaut-google-drive-gallery/bundled/imagesloaded.pkgd.min.js'), ['jquery']);
	wp_register_script('sgdg_imagelightbox_script', plugins_url('/skaut-google-drive-gallery/bundled/imagelightbox.min.js'), ['jquery']);
	wp_register_style('sgdg_imagelightbox_style', plugins_url('/skaut-google-drive-gallery/bundled/imagelightbox.min.css'));
}

function render($atts = [])
{
	wp_enqueue_script('sgdg_masonry');
	wp_enqueue_script('sgdg_imagesloaded');
	wp_enqueue_script('sgdg_imagelightbox_script');
	wp_enqueue_style('sgdg_imagelightbox_style');

	wp_enqueue_script('sgdg_gallery_init');
	$path = isset($atts['path']) ? $atts['path'] : '';
	wp_localize_script('sgdg_gallery_init', 'sgdg_jquery_localize', [
		'thumbnail_size' => \Sgdg\Options::$thumbnailSize->get(),
		'thumbnail_spacing' => \Sgdg\Options::$thumbnailSpacing->get(),
		'preview_speed' => \Sgdg\Options::$previewSpeed->get(),
		'preview_arrows' => \Sgdg\Options::$previewArrows->get(),
		'preview_closebutton' => \Sgdg\Options::$previewCloseButton->get(),
		'preview_quitOnEnd' => \Sgdg\Options::$previewLoop->get_inverted(),
		'preview_activity' => \Sgdg\Options::$previewActivity->get(),
		'ajax_url' => admin_url('admin-ajax.php'),
		'nonce' => wp_create_nonce('sgdg_gallery'),
		'path' => $path,
		'not_auth' => esc_html__('Not authorized.', 'skaut-google-drive-gallery'),
		'not_found' => esc_html__('No such gallery found.', 'skaut-google-drive-gallery')
	]);
	wp_enqueue_style('sgdg_gallery_css');
	wp_add_inline_style('sgdg_gallery_css', '.grid-item { margin-bottom: ' . intval(\Sgdg\Options::$thumbnailSpacing->get() - 7) . 'px; width: ' . \Sgdg\Options::$thumbnailSize->get() . 'px; }');
	return '<div id="sgdg_gallery"></div>';
}

function handle_ajax()
{
	check_ajax_referer('sgdg_gallery');
	try
	{
		$client = \Sgdg\Frontend\GoogleAPILib\getDriveClient();
	}
	catch(\Exception $e)
	{
		wp_send_json('not-auth');
	}
	$rootPath = \Sgdg\Options::$rootPath->get();
	$dir = end($rootPath);
	$ret = 'not-found';

	if(isset($_GET['path']))
	{
		$path = explode('/', trim($_GET['path'], " /\t\n\r\0\x0B"));
		$dir = findDir($client, $dir, $path);
	}
	if($dir)
	{
		$ret = render_gallery($client, $dir);
	}
	wp_send_json($ret);
}

function findDir($client, $root, array $path)
{
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
			if($file->getName() == $path[0])
			{
				if(count($path) === 1)
				{
					return $file->getId();
				}
				array_shift($path);
				return findDir($client, $file->getId(), $path);
			}
		}
		$pageToken = $response->pageToken;
	}
	while($pageToken != null);
	return null;
}

function render_gallery($client, $id)
{
	$ret = [];
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
			$ret[] = ["previewLink" => substr($file->getThumbnailLink(), 0, -3) . \Sgdg\Options::$previewSize->get(), "thumbnailLink" => substr($file->getThumbnailLink(), 0, -4) . 'w' . \Sgdg\Options::$thumbnailSize->get()];
		}
		$pageToken = $response->pageToken;
	}
	while($pageToken != null);
	return $ret;
}
