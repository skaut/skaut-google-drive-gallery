<?php
namespace Sgdg\Frontend\Shortcode;

function register()
{
	add_action('init', '\\Sgdg\\Frontend\\Shortcode\\add');
	add_action('wp_enqueue_scripts', '\\Sgdg\\Frontend\\Shortcode\\register_scripts_styles');
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
	wp_localize_script('sgdg_gallery_init', 'sgdg_jquery_localize', [
		'thumbnail_size' => \Sgdg\Options::$thumbnailSize->get(),
		'thumbnail_spacing' => \Sgdg\Options::$thumbnailSpacing->get(),
		'preview_speed' => \Sgdg\Options::$previewSpeed->get(),
		'preview_arrows' => \Sgdg\Options::$previewArrows->get(),
		'preview_closebutton' => \Sgdg\Options::$previewCloseButton->get(),
		'preview_quitOnEnd' => \Sgdg\Options::$previewLoop->get_inverted(),
		'preview_activity' => \Sgdg\Options::$previewActivity->get()
	]);
	wp_enqueue_style('sgdg_gallery_css');
	wp_add_inline_style('sgdg_gallery_css', '.sgdg-grid-item { margin-bottom: ' . intval(\Sgdg\Options::$thumbnailSpacing->get() - 7) . 'px; width: ' . \Sgdg\Options::$thumbnailSize->get() . 'px; }');

	try
	{
		$client = \Sgdg\Frontend\GoogleAPILib\getDriveClient();
	}
	catch(\Exception $e)
	{
		return '<div id="sgdg-gallery">' . esc_html__('Not authorized.', 'skaut-google-drive-gallery') . '</div>';
	}
	$rootPath = \Sgdg\Options::$rootPath->get();
	$dir = end($rootPath);

	if(isset($atts['path']) and $atts['path'] !== '')
	{
		$path = explode('/', trim($atts['path'], " /\t\n\r\0\x0B"));
		$dir = findDir($client, $dir, $path);
	}
	if(!$dir)
	{
		return '<div id="sgdg-gallery">' . esc_html__('No such gallery found.', 'skaut-google-drive-gallery') . '</div>';
	}
	$ret = '<div id="sgdg-gallery">';
	if(isset($_GET['sgdg-path']))
	{

		$path = explode('/', $_GET['sgdg-path']);
		$ret .= '<div id="sgdg-breadcrumbs"><a href="' . remove_query_arg('sgdg-path') . '">' . esc_html__('Gallery', 'skaut-google-drive-gallery') . '</a>' . render_breadcrumbs($client, $path) . '</div>';
		$dir = applyPath($client, $dir, $path);
	}
	$ret .= render_directories($client, $dir);
	$ret .= render_images($client, $dir);
	return $ret . '</div>';
}

function findDir($client, $root, array $path)
{
	$pageToken = null;
	do
	{
		$optParams = [
			'q' => '"' . $root . '" in parents and mimeType = "application/vnd.google-apps.folder" and trashed = false',
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

function applyPath($client, $root, array $path)
{
	$pageToken = null;
	do
	{
		$optParams = [
			'q' => '"' . $root . '" in parents and mimeType = "application/vnd.google-apps.folder" and trashed = false',
			'supportsTeamDrives' => true,
			'includeTeamDriveItems' => true,
			'pageToken' => $pageToken,
			'pageSize' => 1000,
			'fields' => 'nextPageToken, files(id)'
		];
		$response = $client->files->listFiles($optParams);
		foreach($response->getFiles() as $file)
		{
			if($file->getId() == $path[0])
			{
				if(count($path) === 1)
				{
					return $file->getId();
				}
				array_shift($path);
				return applyPath($client, $file->getId(), $path);
			}
		}
		$pageToken = $response->pageToken;
	}
	while($pageToken != null);
	return null;
}

function render_breadcrumbs($client, array $path, array $usedPath = [])
{
	$response = $client->files->get($path[0], ['supportsTeamDrives' => true, 'fields' => 'name']);
	$ret = ' > <a href="' . add_query_arg('sgdg-path', implode('/', array_merge($usedPath, [$path[0]]))) . '">' . $response->getName() . '</a>';
	if(count($path) === 1)
	{
		return $ret;
	}
	$usedPath[] = array_shift($path);
	return $ret . render_breadcrumbs($client, $path, $usedPath);
}

function render_directories($client, $dir)
{
	$ret = '';
	$pageToken = null;
	do
	{
		$optParams = [
			'q' => '"' . $dir . '" in parents and mimeType = "application/vnd.google-apps.folder" and trashed = false',
			'supportsTeamDrives' => true,
			'includeTeamDriveItems' => true,
			'pageToken' => $pageToken,
			'pageSize' => 1000,
			'fields' => 'nextPageToken, files(id, name)'
		];
		$response = $client->files->listFiles($optParams);
		foreach($response->getFiles() as $file)
		{
			$href = add_query_arg('sgdg-path', (isset($_GET['sgdg-path']) ? $_GET['sgdg-path'] . '/' : '') . $file->getId());
			$ret .= '<div class="sgdg-grid-item"><a class="sgdg-grid-a" href="' . $href . '">' . random_dir_image($client, $file->getId()) . '<div class="sgdg-dir-overlay">' . $file->getName() . '</div></a></div>';
		}
		$pageToken = $response->pageToken;
	}
	while($pageToken != null);
	return $ret;
}

function random_dir_image($client, $dir)
{
	$images = [];
	$pageToken = null;
	do
	{
		$optParams = [
			'q' => '"' . $dir . '" in parents and mimeType contains "image/" and trashed = false',
			'supportsTeamDrives' => true,
			'includeTeamDriveItems' => true,
			'pageToken' => $pageToken,
			'pageSize' => 1000,
			'fields' => 'nextPageToken, files(thumbnailLink)'
		];
		$response = $client->files->listFiles($optParams);
		$images = array_merge($images, $response->getFiles());
		$pageToken = $response->pageToken;
	}
	while($pageToken != null);
	if(count($images) === 0)
	{
		return '<svg class="sgdg-dir-icon" x="0px" y="0px" focusable="false" viewBox="0 0 24 20" fill="#8f8f8f"><path d="M10 2H4c-1.1 0-1.99.9-1.99 2L2 16c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2h-8l-2-2z"></path></svg>';
	}
	$file = $images[array_rand($images)];
	return '<img class="sgdg-grid-img" src="' . substr($file->getThumbnailLink(), 0, -4) . 'w' . \Sgdg\Options::$thumbnailSize->get() . '">';
}

function render_images($client, $dir)
{
	$ret = '';
	$pageToken = null;
	do
	{
		$optParams = [
			'q' => '"' . $dir . '" in parents and mimeType contains "image/" and trashed = false',
			'supportsTeamDrives' => true,
			'includeTeamDriveItems' => true,
			'pageToken' => $pageToken,
			'pageSize' => 1000,
			'fields' => 'nextPageToken, files(thumbnailLink)'
		];
		$response = $client->files->listFiles($optParams);
		foreach($response->getFiles() as $file)
		{
			$ret .= '<div class="sgdg-grid-item"><a class="sgdg-grid-a" data-imagelightbox="a" href="' . substr($file->getThumbnailLink(), 0, -3) . \Sgdg\Options::$previewSize->get() . '"><img class="sgdg-grid-img" src="' . substr($file->getThumbnailLink(), 0, -4) . 'w' . \Sgdg\Options::$thumbnailSize->get() . '"></a></div>';
		}
		$pageToken = $response->pageToken;
	}
	while($pageToken != null);
	return $ret;
}
