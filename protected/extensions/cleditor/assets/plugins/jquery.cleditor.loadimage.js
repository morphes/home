/**
 @preserve CLEditor Icon Plugin v1.0
 http://premiumsoftware.net/cleditor
 requires CLEditor v1.2 or later

 Copyright 2010, Chris Landowski, Premium Software, LLC
 Dual licensed under the MIT or GPL Version 2 licenses.
 */

// ==ClosureCompiler==
// @compilation_level SIMPLE_OPTIMIZATIONS
// @output_file_name jquery.cleditor.icon.min.js
// ==/ClosureCompiler==

(function($) {

	var iconPath = $.cleditor.imagesPath()+'load_image.png';
	var uploadUrl = '/media/admin/mediaKnowledge/uploadimage';

	// Define the hello button
	$.cleditor.buttons.loadimage = {
		name: "loadimage",
		css: { background: "url("+iconPath+") no-repeat 5px 4px" },
		image: "load_image.png",
		title: "Upload Image",
		command: "inserthtml",
		popupName: "loadimage",
		popupClass: "cleditorPrompt",
		popupContent: "<h2>Изображение для загрузки</h2><br><form enctype='multipart/form-data' action='/media/admin/mediaknowledge/load'>alt: <input type=text name=alt ><br><br><input type=file name=upload></form>",
		buttonClick: loadClick,
		afterEditorInit: function(data, popups){
			var pluginSettings = data.options.settings.loadImage;
			if ( pluginSettings && pluginSettings.loadUrl)
				uploadUrl = pluginSettings.loadUrl;
		}
	};


	// Add the button to the default controls before the bold button
	/*
	$.cleditor.defaultOptions.controls = $.cleditor.defaultOptions.controls
		.replace("bold", "hello bold");
		*/



	function loadClick(e, data)
	{
		$(data.popup).find('input[type=file]').unbind('change').bind('change', function(e){

			var editor = data.editor;

			var file = this.files[0];

			if (typeof file == 'object') {
				var url = uploadUrl;
				var alt = $(data.popup).find('input[name=alt]').val();

				uploadFile(file, url, function(response){

					var html = '<img src="' + response + '" alt="' + alt + '"/>';

					editor.execCommand(data.command, html, null, data.button);

					editor.hidePopups();
					editor.focus();
				});
			}


		});
	}

	function uploadFile(file, url, callback) {
		var xhr = new XMLHttpRequest();
		var formData = new FormData();
		// Событие, вызванное по итогу отправки очередного файла
		xhr.onreadystatechange = function(){
			if(this.readyState == 4) {
				if(this.status == 200) {
					// some handler
				}
				delete file;
				delete this;
				if(callback != undefined) callback(this.responseText);
			}
		}
		xhr.open("POST", url);
		formData.append('upload', file);
		xhr.send(formData);
	}


})(jQuery);