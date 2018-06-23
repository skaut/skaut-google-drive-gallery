<?php
namespace Sgdg\Frontend\Shortcode;

function register() {
	add_action( 'init', '\\Sgdg\\Frontend\\Shortcode\\add' );
	add_action( 'wp_enqueue_scripts', '\\Sgdg\\Frontend\\Shortcode\\register_scripts_styles' );
}

function add() {
	add_shortcode( 'sgdg', '\\Sgdg\\Frontend\\Shortcode\\render' );
}

function register_scripts_styles() {
	wp_register_script( 'sgdg_gallery_init', plugins_url( '/skaut-google-drive-gallery/frontend/js/shortcode.js' ), [ 'jquery', 'jquery-masonry' ] );
	wp_register_style( 'sgdg_gallery_css', plugins_url( '/skaut-google-drive-gallery/frontend/css/shortcode.css' ) );

	wp_register_script( 'sgdg_imagesloaded', plugins_url( '/skaut-google-drive-gallery/bundled/imagesloaded.pkgd.min.js' ), [ 'jquery' ] );
	wp_register_script( 'sgdg_imagelightbox_script', plugins_url( '/skaut-google-drive-gallery/bundled/imagelightbox.min.js' ), [ 'jquery' ] );
	wp_register_style( 'sgdg_imagelightbox_style', plugins_url( '/skaut-google-drive-gallery/bundled/imagelightbox.min.css' ) );
}

function render( $atts = [] ) {
	define( 'DONOTCACHEPAGE', true );
	wp_enqueue_script( 'sgdg_imagesloaded' );
	wp_enqueue_script( 'sgdg_imagelightbox_script' );
	wp_enqueue_style( 'sgdg_imagelightbox_style' );

	wp_enqueue_script( 'sgdg_gallery_init' );
	wp_localize_script( 'sgdg_gallery_init', 'sgdg_shortcode_localize', [
		'grid_spacing'        => \Sgdg\Options::$grid_spacing->get(),
		'preview_speed'       => \Sgdg\Options::$preview_speed->get(),
		'preview_arrows'      => \Sgdg\Options::$preview_arrows->get(),
		'preview_closebutton' => \Sgdg\Options::$preview_close_button->get(),
		'preview_quitOnEnd'   => \Sgdg\Options::$preview_loop->get_inverted(),
		'preview_activity'    => \Sgdg\Options::$preview_activity_indicator->get(),
		'dynamic_width'       => \Sgdg\Options::$grid_mode->get() === 'dynamic' ? 'true' : 'false',
	]);
	wp_enqueue_style( 'sgdg_gallery_css' );
	$grid_item_style = '.sgdg-grid-item { margin-bottom: ' . intval( \Sgdg\Options::$grid_spacing->get() ) . 'px;';
	if ( \Sgdg\Options::$grid_mode->get() === 'dynamic' ) {
		$cols             = \Sgdg\Options::$grid_columns->get();
		$grid_item_style .= 'width: ' . floor( 95 / $cols ) . '%; ';
		$grid_item_style .= 'width: calc(' . floor( 100 / $cols ) . '% - ' . \Sgdg\Options::$grid_spacing->get() * ( 1 - 1 / $cols ) . 'px);';
		$grid_item_style .= 'min-width: ' . \Sgdg\Options::$grid_min_width->get() . 'px;';
	} else {
		$grid_item_style .= 'width: ' . \Sgdg\Options::$grid_width->get() . 'px;';
	}
	$grid_item_style .= ' }';
	wp_add_inline_style( 'sgdg_gallery_css', $grid_item_style );

	try {
		$client = \Sgdg\Frontend\GoogleAPILib\get_drive_client();
	} catch ( \Exception $e ) {
		return '<div id="sgdg-gallery">' . esc_html__( 'Not authorized.', 'skaut-google-drive-gallery' ) . '</div>';
	}
	$root_path = \Sgdg\Options::$root_path->get();
	$dir       = end( $root_path );

	if ( isset( $atts['path'] ) && '' !== $atts['path'] ) {
		$path = explode( '/', trim( $atts['path'], " /\t\n\r\0\x0B" ) );
		$dir  = find_dir( $client, $dir, $path );
	}
	if ( ! $dir ) {
		return '<div id="sgdg-gallery">' . esc_html__( 'No such gallery found.', 'skaut-google-drive-gallery' ) . '</div>';
	}
	$ret = '<div id="sgdg-gallery">';
	// phpcs:ignore WordPress.CSRF.NonceVerification.NoNonceVerification
	if ( isset( $_GET['sgdg-path'] ) ) {

		// phpcs:ignore WordPress.CSRF.NonceVerification.NoNonceVerification
		$path = explode( '/', $_GET['sgdg-path'] );
		$ret .= '<div id="sgdg-breadcrumbs"><a href="' . remove_query_arg( 'sgdg-path' ) . '">' . esc_html__( 'Gallery', 'skaut-google-drive-gallery' ) . '</a>' . render_breadcrumbs( $client, $path ) . '</div>';
		$dir  = apply_path( $client, $dir, $path );
	}
	$ret .= render_directories( $client, $dir );
	$ret .= render_images( $client, $dir );
	return $ret . '</div>';
}

function find_dir( $client, $root, array $path ) {
	$page_token = null;
	do {
		$params   = [
			'q'                     => '"' . $root . '" in parents and mimeType = "application/vnd.google-apps.folder" and trashed = false',
			'supportsTeamDrives'    => true,
			'includeTeamDriveItems' => true,
			'pageToken'             => $page_token,
			'pageSize'              => 1000,
			'fields'                => 'nextPageToken, files(id, name)',
		];
		$response = $client->files->listFiles( $params );
		foreach ( $response->getFiles() as $file ) {
			if ( $file->getName() === $path[0] ) {
				if ( count( $path ) === 1 ) {
					return $file->getId();
				}
				array_shift( $path );
				return find_dir( $client, $file->getId(), $path );
			}
		}
		$page_token = $response->getNextPageToken();
	} while ( null !== $page_token );
	return null;
}

function apply_path( $client, $root, array $path ) {
	$page_token = null;
	do {
		$params   = [
			'q'                     => '"' . $root . '" in parents and mimeType = "application/vnd.google-apps.folder" and trashed = false',
			'supportsTeamDrives'    => true,
			'includeTeamDriveItems' => true,
			'pageToken'             => $page_token,
			'pageSize'              => 1000,
			'fields'                => 'nextPageToken, files(id)',
		];
		$response = $client->files->listFiles( $params );
		foreach ( $response->getFiles() as $file ) {
			if ( $file->getId() === $path[0] ) {
				if ( count( $path ) === 1 ) {
					return $file->getId();
				}
				array_shift( $path );
				return apply_path( $client, $file->getId(), $path );
			}
		}
		$page_token = $response->getNextPageToken();
	} while ( null !== $page_token );
	return null;
}

function render_breadcrumbs( $client, array $path, array $used_path = [] ) {
	$response = $client->files->get( $path[0], [
		'supportsTeamDrives' => true,
		'fields'             => 'name',
	]);
	$ret      = ' > <a href="' . add_query_arg( 'sgdg-path', implode( '/', array_merge( $used_path, [ $path[0] ] ) ) ) . '">' . $response->getName() . '</a>';
	if ( count( $path ) === 1 ) {
		return $ret;
	}
	$used_path[] = array_shift( $path );
	return $ret . render_breadcrumbs( $client, $path, $used_path );
}

function render_directories( $client, $dir ) {
	$dir_counts_allowed = \Sgdg\Options::$dir_counts->get() === 'true';
	$ids                = [];
	$names              = [];

	$page_token = null;
	do {
		$params   = [
			'q'                     => '"' . $dir . '" in parents and mimeType = "application/vnd.google-apps.folder" and trashed = false',
			'supportsTeamDrives'    => true,
			'includeTeamDriveItems' => true,
			'orderBy'               => \Sgdg\Options::$dir_ordering->get(),
			'pageToken'             => $page_token,
			'pageSize'              => 1000,
			'fields'                => 'nextPageToken, files(id, name)',
		];
		$response = $client->files->listFiles( $params );
		foreach ( $response->getFiles() as $file ) {
			$ids[]   = $file->getId();
			$names[] = $file->getName();
		}
		$page_token = $response->getNextPageToken();
	} while ( null !== $page_token );

	$client->getClient()->setUseBatch( true );
	$batch = $client->createBatch();
	dir_images_requests( $client, $batch, $ids );
	if ( $dir_counts_allowed ) {
		dir_counts_requests( $client, $batch, $ids );
	}
	$responses = $batch->execute();
	$client->getClient()->setUseBatch( false );

	try {
		$dir_images = dir_images_responses( $responses, $ids );
		if ( $dir_counts_allowed ) {
			$dir_counts = dir_counts_responses( $responses, $ids );
		}
	} catch ( \Sgdg\Vendor\Google_Service_Exception $e ) {
		if ( 'userRateLimitExceeded' === $e->getErrors()[0]['reason'] ) {
			return esc_html__( 'The maximum number of requests has been exceeded. Please try again in a minute.', 'skaut-google-drive-gallery' );
		} else {
			return $e->getErrors()[0]['message'];
		}
	}

	$ret   = '';
	$count = count( $ids );
	for ( $i = 0; $i < $count; $i++ ) {
		// phpcs:ignore WordPress.CSRF.NonceVerification.NoNonceVerification
		$href = add_query_arg( 'sgdg-path', ( isset( $_GET['sgdg-path'] ) ? $_GET['sgdg-path'] . '/' : '' ) . $ids[ $i ] );
		$ret .= '<div class="sgdg-grid-item"><a class="sgdg-grid-a" href="' . $href . '">' . $dir_images[ $i ] . '<div class="sgdg-dir-overlay"><div class="sgdg-dir-name">' . $names[ $i ] . '</div>';
		if ( $dir_counts_allowed ) {
			$ret .= $dir_counts[ $i ];
		}
		$ret .= '</div></a></div>';
	}
	return $ret;
}

function dir_images_requests( $client, $batch, $dirs ) {
	$params = [
		'supportsTeamDrives'    => true,
		'includeTeamDriveItems' => true,
		'orderBy'               => \Sgdg\Options::$image_ordering->get(),
		'pageSize'              => 1,
		'fields'                => 'files(thumbnailLink)',
	];

	foreach ( $dirs as $dir ) {
		$params['q'] = '"' . $dir . '" in parents and mimeType contains "image/" and trashed = false';
		$request     = $client->files->listFiles( $params );
		$batch->add( $request, 'img-' . $dir );
	}
}

function dir_images_responses( $responses, $dirs ) {
	$ret = [];
	foreach ( $dirs as $dir ) {
		$response = $responses[ 'response-img-' . $dir ];
		if ( $response instanceof \Sgdg\Vendor\Google_Service_Exception ) {
			throw $response;
		}
		$images = $response->getFiles();
		if ( count( $images ) === 0 ) {
			$ret[] = '<svg class="sgdg-dir-icon" x="0px" y="0px" focusable="false" viewBox="0 0 24 20" fill="#8f8f8f"><path d="M10 2H4c-1.1 0-1.99.9-1.99 2L2 16c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2h-8l-2-2z"></path></svg>';
		} else {
			$ret[] = '<img class="sgdg-grid-img" src="' . substr( $images[0]->getThumbnailLink(), 0, -4 ) . 'w' . get_thumbnail_width() . '">';
		}
	}
	return $ret;
}

function dir_counts_requests( $client, $batch, $dirs ) {
	$params = [
		'supportsTeamDrives'    => true,
		'includeTeamDriveItems' => true,
		'pageSize'              => 1000,
		'fields'                => 'files(id)',
	];

	foreach ( $dirs as $dir ) {
		$params['q'] = '"' . $dir . '" in parents and mimeType contains "application/vnd.google-apps.folder" and trashed = false';
		$request     = $client->files->listFiles( $params );
		$batch->add( $request, 'dircount-' . $dir );
		$params['q'] = '"' . $dir . '" in parents and mimeType contains "image/" and trashed = false';
		$request     = $client->files->listFiles( $params );
		$batch->add( $request, 'imgcount-' . $dir );
	}
}

function dir_counts_responses( $responses, $dirs ) {
	$ret = [];
	foreach ( $dirs as $dir ) {
		$dir_response = $responses[ 'response-dircount-' . $dir ];
		$img_response = $responses[ 'response-imgcount-' . $dir ];
		if ( $dir_response instanceof \Sgdg\Vendor\Google_Service_Exception ) {
			throw $dir_response;
		}
		if ( $img_response instanceof \Sgdg\Vendor\Google_Service_Exception ) {
			throw $img_response;
		}
		$dircount   = count( $dir_response->getFiles() );
		$imagecount = count( $img_response->getFiles() );

		$val = '<div class="sgdg-dir-counts">';
		if ( $dircount > 0 ) {
			$val .= $dircount . ' ' . esc_html( _n( 'folder', 'folders', $dircount, 'skaut-google-drive-gallery' ) );
			if ( $imagecount > 0 ) {
				$val .= ', ';
			}
		}
		if ( $imagecount > 0 ) {
			$val .= $imagecount . ' ' . esc_html( _n( 'image', 'images', $imagecount, 'skaut-google-drive-gallery' ) );
		}
		$ret[] = $val . '</div>';
	}
	return $ret;
}

function dir_count_types( $client, $dir, $type ) {
	$count      = 0;
	$page_token = null;
	do {
		$params     = [
			'q'                     => '"' . $dir . '" in parents and mimeType contains "' . $type . '" and trashed = false',
			'supportsTeamDrives'    => true,
			'includeTeamDriveItems' => true,
			'pageToken'             => $page_token,
			'pageSize'              => 1000,
			'fields'                => 'nextPageToken, files(id)',
		];
		$response   = $client->files->listFiles( $params );
		$count     += count( $response->getFiles() );
		$page_token = $response->getNextPageToken();
	} while ( null !== $page_token );
	return $count;
}

function render_images( $client, $dir ) {
	$ret        = '';
	$page_token = null;
	do {
		$params   = [
			'q'                     => '"' . $dir . '" in parents and mimeType contains "image/" and trashed = false',
			'supportsTeamDrives'    => true,
			'includeTeamDriveItems' => true,
			'orderBy'               => \Sgdg\Options::$image_ordering->get(),
			'pageToken'             => $page_token,
			'pageSize'              => 1000,
			'fields'                => 'nextPageToken, files(id, thumbnailLink)',
		];
		$response = $client->files->listFiles( $params );
		foreach ( $response->getFiles() as $file ) {
			$ret .= '<div class="sgdg-grid-item"><a class="sgdg-grid-a" data-imagelightbox="a"';
			$ret .= 'data-ilb2-id="' . $file->getId() . '"';
			$ret .=	' href="' . substr( $file->getThumbnailLink(), 0, -3 ) . \Sgdg\Options::$preview_size->get() . '"><img class="sgdg-grid-img" src="' . substr( $file->getThumbnailLink(), 0, -4 ) . 'w' . get_thumbnail_width() . '"></a></div>';
		}
		$page_token = $response->getNextPageToken();
	} while ( null !== $page_token );
	return $ret;
}

function get_thumbnail_width() {
	if ( \Sgdg\Options::$grid_mode->get() === 'dynamic' ) {
		return max( ceil( 1920 / \Sgdg\Options::$grid_columns->get() ), \Sgdg\Options::$grid_min_width->get() );
	}
	return \Sgdg\Options::$grid_width->get();
}
