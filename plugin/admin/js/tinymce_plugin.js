"use strict";
jQuery( document ).ready(function($) {
	var path = [];
	var localize = [];

	tinymce.PluginManager.add("sgdg_tinymce_button", function(editor, url) {
		editor.addButton("sgdg_tinymce_button", {
			text: "SGDG", // TODO: i18n, icon
			icon: false,
			onclick: function() {tinymce_onclick(editor);}
		});
		var html = "<div id=\"sgdg-tinymce-modal\"></div>";
		$("body").append(html)
	});
	function tinymce_onclick(editor)
	{
		tinymce_html(editor);
		tb_show("Titleeeeeeeeeeeee", "#TB_inline?inlineId=sgdg-tinymce-modal"); // TODO: i18n
		path = [];
		localize = editor.settings.sgdg_localize;
		ajax_query();
	}
	function tinymce_html(editor)
	{
		var html = "<table id=\"sgdg-tinymce-table\" class=\"widefat\">";
		html += "<thead>";
		html += "<tr>";
		html += "<th class=\"sgdg-tinymce-path\">Root</th>"; // TODO: i18n
		html += "</tr>";
		html += "</thead>";
		html += "<tbody id=\"sgdg-tinymce-list\"></tbody>";
		html += "<tfoot>";
		html += "<tr>";
		html += "<td class=\"sgdg-tinymce-path\">Root</td>"; // TODO: i18n
		html += "</tr>";
		html += "</tfoot>";
		html += "</table>";
		html += "<div class=\"sgdg-tinymce-footer\">";
		html += "<a id=\"sgdg-tinymce-insert\" class=\"button button-primary\">Insert</a>"; // TODO: i18n
		html += "</div>";
		$("#sgdg-tinymce-modal").html(html);
		$("#sgdg-tinymce-insert").click(function() {tinymce_submit(editor);})
	}

	function tinymce_submit(editor)
	{
		if($( "#sgdg-tinymce-insert" ).attr( "disabled" ))
		{
			return;
		}
		editor.insertContent("[sgdg path=\"" + path.join("/") + "\"]");
		tb_remove();
	}

	function ajax_query()
	{
		$( "#sgdg-tinymce-list" ).html( "" );
		$( "#sgdg-tinymce-insert" ).attr( "disabled", "disabled" );
		$.get(localize.ajax_url, {
			_ajax_nonce: localize.nonce,
			action: "list_gallery_dir",
			path: path
			}, function(data)
			{
				$( "#sgdg-tinymce-insert" ).removeAttr( "disabled" );
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
				$( "#sgdg-tinymce-list" ).html( html );
				html = "Root" // TODO: i18n
				len = path.length;
				for (i = 0; i < len; i++) {
					html += " > ";
					html += path[i];
				}
				$(".sgdg-tinymce-path").html( html );
				$("#sgdg-tinymce-list label").click(function()
					{
						var newDir = $( this ).html();
						if(newDir === "..")
						{
							path.pop();
						}
						else
						{
							path.push(newDir);
						}
						ajax_query();
					});
			}
		);
	}
});
