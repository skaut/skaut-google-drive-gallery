"use strict";
jQuery( document ).ready( function($) {
	var reflow = function() {
		var loaded = [];
		var ratios = [];
		$("#sgdg-gallery").children().each(function(i) {
			$(this).css("position", "initial");
			$(this).css("display", "inline-block");
			var val = this.getBoundingClientRect().width / this.getBoundingClientRect().height;
			if($(this).find("svg").length > 0) {
				var bbox = $(this).find("svg")[0].getBBox();
				val = bbox.width / bbox.height;
			}
			if(isNaN(val)) {
				loaded[i] = false;
			} else {
				loaded[i] = true;
				ratios.push(val);
			}
			$(this).css("position", "absolute");
		});
		var config = {
			containerWidth: $("#sgdg-gallery").width(),
			boxSpacing: parseInt(sgdg_shortcode_localize.grid_spacing),
			targetRowHeight: parseInt(sgdg_shortcode_localize.grid_height)
		};
		var positions = require("justified-layout")(ratios, config);
		var j = 0;
		$("#sgdg-gallery").children().each(function(i) {
			if(!loaded[i]) {
				$(this).css("display", "none");
				return;
			}
			var sizes = positions.boxes[j];
			j++;
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
	$("#sgdg-gallery").find("img").load(reflow);
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
