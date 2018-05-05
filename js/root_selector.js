jQuery(document).ready(function($) {
	//$(".pref").change(function()
		//{
			//var this2 = this;
			$.get(sgdg_jquery_localize.ajax_url, {
				_ajax_nonce: sgdg_jquery_localize.nonce,
				action: "list_gdrive_dir",
			}, function(data)
				{
					console.log(data);
					var html = "";
					for(var i = 0; i < data.length; i++)
					{
						html += "<tr class=\"";
						if(i % 2 === 1)
						{
							html += "alternate";
						}
						html += "\"><td>" + data[i].name + "</td><td>" + data[i].id + "</td></tr>";
					}
					$('#root_selector_body').html(html);
				});
		//});
});
