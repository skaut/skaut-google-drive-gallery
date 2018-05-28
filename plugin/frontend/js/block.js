"use strict";
jQuery( document ).ready(function($) {
	var el         = wp.element.createElement;
	var blockStyle = { backgroundColor: "#900", color: "#fff", padding: "20px" };

	wp.blocks.registerBlockType( "skaut-google-drive-gallery/gallery", {
		title: "Google Drive gallery", // TODO: i18n
		description: "", // TODO
		category: "common",
		icon: "format-gallery",
		attributes: {
			path: {
				type: "array"
			}
		},
		edit: render_editor,
		save: render_frontend
	});

	function render_editor(attributes)
	{
		return el( "p", { style: blockStyle }, "Hello editor content." );
	}

	function render_frontend(attributes)
	{
		return el( "p", { style: blockStyle }, "Hello saved content." );
	}
});
