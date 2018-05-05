jQuery(document).ready(function($) {
	function listGdriveDir(path)
	{
		path = path || [];
		$("#root_selector_body").html("");
		$.get(sgdg_jquery_localize.ajax_url, {
			_ajax_nonce: sgdg_jquery_localize.nonce,
			action: "list_gdrive_dir",
			path: path
		}, function(data)
			{
				var html = "";
				for(var i = 0; i < data.length; i++)
				{
					html += "<tr class=\"";
					if(i % 2 === 1)
					{
						html += "alternate";
					}
					html += "\"><td class=\"row-title\"><label data-id=\"" + data[i].id + "\">" + data[i].name + "</label></td></tr>";
				}
				$("#root_selector_body").html(html);
				$("#root_selector_body label").click(function()
					{
						path.push($(this).attr("data-id"))
						listGdriveDir(path);
					});
			});
	}

	listGdriveDir();
});
