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

	var iconPath = $.cleditor.imagesPath()+'/format_media.png';

	// Хранит родительский тег выделенной области, который является первым ребенком body
	var parentSelection;

	var styleTypes = {
		'style0' : ['<div>', '</div>'],
		'style1' : ['<div class="preview_text">', '</div>'],
		'style2' : ['<div class="respondent_info"><div class="respondent_info_bg">', '</div></div>'],
		'style3' : ['<div class="reportage question">', '</div>'],
		'style4' : ['<div class="reportage">', '</div>'],
		'style5' : ['<div class="quote"><div class="quote_bg"><span>', '</span></div></div>']
	};

	// Define the select gallery button
	$.cleditor.buttons.fpreviewtext = {
		name: "fpreviewtext",
		css: { background: "url("+iconPath+") no-repeat 5px 4px" },
		title: "Format preview text",
		command: 'inserthtml',
		buttonClick: loadClick,
		popupName: "fpreviewtext",
		popupClass: "cleditorPrompt",
		popupContent: (function(){ return getTemplate('formatmedia/list_style'); }),
		afterEditorInit: function(data, popups){

			var style = data.doc.createElement('link');
			style.rel = 'stylesheet';
			style.type = 'text/css';
			style.href = '/css/media.css';
			data.doc.getElementsByTagName('head')[0].appendChild(style);


			$(data.doc).find('body').addClass('article_text');
		}
	};

	// Method which execute if plugin button were pushed
	function loadClick(e, data)
	{
		var wi = data.editor.doc;

		if (wi.getSelection()) {
			var range = wi.getSelection().getRangeAt(0);

			var start = range.startContainer;
			var end = range.endContainer;
			var root = range.commonAncestorContainer;


			if(start.nodeName == "#text") start = start.parentNode;
			if(end.nodeName == "#text") end = end.parentNode;

			if(start == end) root = start;



			if (root.nodeName.toLowerCase() == 'body')
			{
				$(root).html('<div>'+$(root).text()+'</div>');

				parentSelection = $(root).find('div').get(0);

			} else {
				get_parent_body(root);
			}
		}




		$('ul.list_style li').click(function(){
			var type = $(this).data('type');

			formatText(type, data.editor.doc);

			data.editor.hidePopups();
			data.editor.focus();
		});
	}

	/**
	 * Обрамляет выделенный текст указанным типом стилей
	 * @param name
	 */
	function formatText(name, doc)
	{
		if (styleTypes[name] == undefined)
			alert('Ошибка формата стиля');
		else {
			var elem = $(parentSelection);

			if ( ! elem.next().is('div')) {
				elem.after('<div>&nbsp;</div>');
			}
			elem.replaceWith(styleTypes[name][0]+parentSelection.innerText+styleTypes[name][1]);


			var range = doc.createRange();
			range.setStart($(doc).find('div:last').get(0), 0);
			range.setEnd($(doc).find('div:last').get(0), 0);

			var sel = doc.getSelection();
			sel.removeAllRanges();
			sel.addRange(range);
		}

	}


	function get_parent_body(cur)
	{
		if (cur.parentNode.tagName.toLowerCase() == 'body')
			parentSelection = cur;
		else
			get_parent_body(cur.parentNode);

		return;
	}

	/**
	 * Получает тело шаблона по имени
	 *
	 * @param template
	 * @return {*}
	 */
	function getTemplate(template)
	{
		var text;

		$.ajax({
			url:$.cleditor.imagesPath()+'../templates/'+template+'.html',
			type: 'GET',
			async: false,
			dataType:'html',
			success: function(response){
				text = response;
			}
		});

		return text;
	}

})(jQuery);