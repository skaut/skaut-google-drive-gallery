"use strict";
jQuery( document ).ready(function($) {
	tinymce.PluginManager.add('sgdg_tinymce_button', function(editor, url) {
		editor.addButton('sgdg_tinymce_button', {
			text: 'SGDG',
			icon: false,
			onclick: function() {tinymce_onclick(editor);}
		});
	});
	function tinymce_onclick(editor)
	{
		editor.windowManager.open({
			title: 'Titleeee',
			html: tinymce_html(),
			width: '600',
			onsubmit: function(e) {tinymce_submit(editor, e);}
		})
		ajax_query([]);
	}
	function tinymce_html()
	{
		var html = '<table class="widefat">';
		html += '<thead>';
		html += '<tr>';
		html += '<th class="sgdg_root_selection_path"></th>';
		html += '</tr>';
		html += '</thead>';
		html += '<tbody id="sgdg_root_selection_body"></tbody>';
		html += '<tfoot>';
		html += '<tr>';
		html += '<td class="sgdg_root_selection_path"></td>';
		html += '</tr>';
		html += '</tfoot>';
		html += '</table>';
		return html;
	}

	function tinymce_submit(editor, e)
	{}

	function ajax_query(path)
	{
		//$.get(sgdg_jquery_localize.ajax_url, {
		//_ajax_nonce: sgdg_jquery_localize.nonce,
		//action: "list_gallery_dir",
		//path: path
		//}, function(data)
		//{
		//console.log(data)
		//}
		//);
	}
});
