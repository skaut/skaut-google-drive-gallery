"use strict";
jQuery(document).ready(function($) {
	function listDir(path)
	{
		$.get(sgdg_jquery_localize.ajax_url, {
			_ajax_nonce: sgdg_jquery_localize.nonce,
			action: "list_gallery_dir",
			path: path
		}, renderDir);
	}

	function renderDir(contents)
	{
		var html = "";
		if(contents.length === 0)
		{
			html = sgdg_jquery_localize.no_gallery;
		}
		for(var i = 0; i < contents.length; i++)
		{
			html += "<div class=\"grid-item\"><a class=\"sgdg-grid-a\" data-imagelightbox=\"a\" href=\"" + contents[i].previewLink + "\"><img class=\"sgdg-grid-img\" src=\"" + contents[i].thumbnailLink + "\"></a></div>";
		}
		$("#sgdg_gallery").html(html);

		var grid = $('#sgdg_gallery').masonry({
			itemSelector: '.grid-item',
			columnWidth: parseInt(sgdg_jquery_localize.thumbnail_size, 10),
			gutter: parseInt(sgdg_jquery_localize.thumbnail_spacing, 10),
			fitWidth: true
		});
		grid.imagesLoaded().progress(function()
			{
				grid.masonry('layout');
			});
		$('a[data-imagelightbox]').imageLightbox({
			allowedTypes: '',
			animationSpeed: parseInt(sgdg_jquery_localize.preview_speed, 10),
			activity: (sgdg_jquery_localize.preview_activity === "true"),
			arrows: (sgdg_jquery_localize.preview_arrows === "true"),
			button: (sgdg_jquery_localize.preview_closebutton === "true"),
			fullscreen: true,
			overlay: true,
			quitOnEnd: (sgdg_jquery_localize.preview_quitOnEnd === "true")
		});
	}

	listDir(sgdg_jquery_localize.path);
});
