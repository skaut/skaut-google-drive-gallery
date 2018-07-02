"use strict";
jQuery( document ).ready( function($) {
	$( "#sgdg-gallery" ).justifiedGallery({
		border: 0,
		margins: parseInt( sgdg_shortcode_localize.grid_spacing ),
		rowHeight: parseInt( sgdg_shortcode_localize.grid_height )
	});
	$( "a[data-imagelightbox]" ).imageLightbox({
		allowedTypes: "",
		animationSpeed: parseInt( sgdg_shortcode_localize.preview_speed, 10 ),
		activity: (sgdg_shortcode_localize.preview_activity === "true"),
		arrows: (sgdg_shortcode_localize.preview_arrows === "true"),
		button: (sgdg_shortcode_localize.preview_closebutton === "true"),
		fullscreen: true,
		history: true,
		overlay: true,
		quitOnEnd: (sgdg_shortcode_localize.preview_quitOnEnd === "true")
	});

});
