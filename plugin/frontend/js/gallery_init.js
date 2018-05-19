"use strict";
jQuery( document ).ready( function($) {
	var grid = $( '#sgdg-gallery' ).masonry({
		itemSelector: '.sgdg-grid-item',
		columnWidth: parseInt( sgdg_jquery_localize.thumbnail_size, 10 ),
		gutter: parseInt( sgdg_jquery_localize.thumbnail_spacing, 10 ),
		fitWidth: true
	});
	grid.imagesLoaded().progress(function()
		{
			grid.masonry( 'layout' );
	});
	$( 'a[data-imagelightbox]' ).imageLightbox({
		allowedTypes: '',
		animationSpeed: parseInt( sgdg_jquery_localize.preview_speed, 10 ),
		activity: (sgdg_jquery_localize.preview_activity === "true"),
		arrows: (sgdg_jquery_localize.preview_arrows === "true"),
		button: (sgdg_jquery_localize.preview_closebutton === "true"),
		fullscreen: true,
		overlay: true,
		quitOnEnd: (sgdg_jquery_localize.preview_quitOnEnd === "true")
	});

});
