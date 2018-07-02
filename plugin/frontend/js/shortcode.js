"use strict";
jQuery( document ).ready( function($) {
	var reflow = function() {
		var ratios = jQuery.map($(".sgdg-grid-a"), function(i) {
			$(i).css("position", "initial");
			var img = $(i).children().first();
			var ret = img.width() / img.height();
			$(i).css("position", "absolute");
			return ret;
		});
		var config = {
			containerWidth: $("#sgdg-gallery").width(),
			boxSpacing: parseInt(sgdg_shortcode_localize.grid_spacing),
			targetRowHeight: parseInt(sgdg_shortcode_localize.grid_height)
		};
		var positions = require("justified-layout")(ratios, config);
		$(".sgdg-grid-a").each(function(i) {
			var sizes = positions.boxes[i];
			var containerPosition = $("#sgdg-gallery").position();
			$(this).css("top", sizes.top + containerPosition.top);
			$(this).css("left", sizes.left + containerPosition.left);
			$(this).width(sizes.width);
			$(this).height(sizes.height);
			$(this).children().first().width(sizes.width);
			$(this).children().first().height(sizes.height);
		});
		$("#sgdg-gallery").height(positions.containerHeight)
	}
	$(window).resize(reflow);
	reflow();
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
