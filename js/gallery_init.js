"use strict";
jQuery(document).ready(function($) {
	var grid = $('.grid').masonry({
		itemSelector: '.grid-item',
		columnWidth: 250,
		gutter: 10,
		fitWidth: true
	});
	grid.imagesLoaded().progress(function()
		{
			grid.masonry('layout');
		});
	$('a[data-imagelightbox]').imageLightbox({
		allowedTypes: '',
		activity: true,
		arrows: true,
		button: true,
		overlay: true,
		quitOnEnd: true
	});
});
