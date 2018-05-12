"use strict";
jQuery(document).ready(function($) {
	var grid = $('.grid').masonry({
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
		activity: true,
		arrows: (sgdg_jquery_localize.preview_arrows === "true"),
		button: (sgdg_jquery_localize.preview_closebutton === "true"),
		overlay: true,
		quitOnEnd: (sgdg_jquery_localize.preview_quitOnEnd === "true")
	});
});
