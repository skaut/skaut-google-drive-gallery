"use strict";
jQuery( document ).ready( function($) {
	var grid = $( '#sgdg-gallery' ).masonry({
		itemSelector: '.sgdg-grid-item',
		columnWidth: '.sgdg-grid-item',
		gutter: parseInt( sgdg_shortcode_localize.grid_spacing, 10 ),
		fitWidth: (sgdg_shortcode_localize.dynamic_width === "false")
	});
	grid.imagesLoaded().progress(function()
		{
			grid.masonry( 'layout' );
	});
	$( 'a[data-imagelightbox]' ).imageLightbox({
		allowedTypes: '',
		animationSpeed: parseInt( sgdg_shortcode_localize.preview_speed, 10 ),
		activity: (sgdg_shortcode_localize.preview_activity === "true"),
		arrows: (sgdg_shortcode_localize.preview_arrows === "true"),
		button: (sgdg_shortcode_localize.preview_closebutton === "true"),
		fullscreen: true,
		overlay: true,
		quitOnEnd: (sgdg_shortcode_localize.preview_quitOnEnd === "true")
	});

});
