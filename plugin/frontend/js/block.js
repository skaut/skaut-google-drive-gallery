"use strict";
jQuery( document ).ready(function($) {
	var el = wp.element.createElement;

	wp.blocks.registerBlockType( "skaut-google-drive-gallery/gallery", {
		title: sgdg_block_localize.block_name,
		description: sgdg_block_localize.block_description,
		category: "common",
		icon: "format-gallery",
		attributes: {
			path: {
				type: "string",
				default: "[]"
			}
		},
		edit: render_editor,
		save: render_frontend,
		useOnce: true // TODO: Remove
	});

	function render_editor(props)
	{
		if ($( "#sgdg-block-editor-list" ).children().length === 0) {
			ajax_query( props, JSON.parse( props.attributes.path ) );
		}
		return el( "table", { class: "widefat" }, [
			el("thead", {},
				el("tr", {},
					el( "th", {class: "sgdg-block-editor-path"}, sgdg_block_localize.root_name )
				)
			),
			el( "tbody", {id: "sgdg-block-editor-list"} ),
			el("tfoot", {},
				el("tr", {},
					el( "th", {class: "sgdg-block-editor-path"}, sgdg_block_localize.root_name )
				)
			)
		]);
	}

	function render_frontend(props)
	{
		return null;
	}

	function ajax_query(props, path)
	{
		$( "#sgdg-block-editor-list" ).html( "" );
		$.get(sgdg_block_localize.ajax_url, {
			_ajax_nonce: sgdg_block_localize.nonce,
			action: "list_gallery_dir",
			path: path
			}, function(data)
			{
				var html = "";
				if (path.length > 0) {
					html += "<tr><td class=\"row-title\"><label>..</label></td></tr>";
				}
				var len = data.length;
				for (var i = 0; i < len; i++) {
					html += "<tr class=\"";
					if ((path.length === 0 && i % 2 === 1) || (path.length > 0 && i % 2 === 0)) {
						html += "alternate";
					}
					html += "\"><td class=\"row-title\"><label>" + data[i] + "</label></td></tr>";
				}
				$( "#sgdg-block-editor-list" ).html( html );
				html = sgdg_block_localize.root_name;
				len  = path.length;
				for (i = 0; i < len; i++) {
					html += " > ";
					html += path[i];
				}
				$( ".sgdg-block-editor-path" ).html( html );
				$( "#sgdg-block-editor-list label" ).click(function() {
					var newDir = $( this ).html();
					if (newDir === "..") {
						path.pop();
					} else {
						path.push( newDir );
					}
					props.setAttributes( {'path': JSON.stringify( path )} );
					ajax_query( props, path );
				});
			}
		);
	}
});
