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

	var iconPath = $.cleditor.imagesPath()+'gallery/add_gallery.png';

	// The number of gallery
	var numGallery = 0;

	// Define the hello button
	$.cleditor.buttons.addgallery = {
		name: "addgallery",
		css: { background: "url("+iconPath+") no-repeat 5px 4px" },
		title: "Add gallery",
		command: "inserthtml",
		popupName: "addgallery",
		popupClass: "cleditorPrompt",
		buttonClick: loadClick,
		popupContent: (function(){ return getTemplate('add'); }),
		afterEditorInit: function(data, popups){

			var style = data.doc.createElement('link');
			style.rel = 'stylesheet';
			style.type = 'text/css';
			style.href = $.cleditor.imagesPath()+'../plugins/jquery.cleditor.gallery.css';
			data.doc.getElementsByTagName('head')[0].appendChild(style);


			$(data.doc).find('.gallery_btn').bind('click', function(){
				editGallery($(this), data.options.settings.addgallery);
			});
		}
	};

	// Method which execute if plugin button were pushed
	function loadClick(e, data)
	{

		var $popup = $(data.popup);
		$popup.css({'maxHeight': '480px', 'overflowY':'scroll'});

		// Получаем набор опций для нашего плагина
		var settings = data.editor.options.settings.addgallery;

		// Получаем номер галереи
		$.ajax({
			url	: settings['urlGetNumber'],
			type	: 'POST',
			async	: false,
			data	: {modelId: settings['model_id']},
			dataType: 'JSON',
			success	: function(response){
				numGallery = parseInt(response.num);
			}
		});


		bindShowSelectPopup($popup, data, settings);

		_clickButtonSave($popup, data, settings);

	}

	/**
	 * Навешиваем клик на ссылку, по которой показывается попап
	 * добавления файлов для новой галереи.
	 * @param $popup
	 * @param settings
	 */
	function bindShowAddPopup($popup, data, settings)
	{
		$popup.find('.add_gallery').bind('click', function(){
			$htmlAdd = getTemplate('add');

			$popup.html($htmlAdd);

			bindShowSelectPopup($popup, data, settings);



			_clickButtonSave($popup, data, settings);

			return false;
		});
	}
	
	function _clickButtonSave($popup, data, settings)
	{

		//Событие выбора изображения в input поле
		$popup.find('.new_image input').bind('change', function(e){

			changeFile($popup, this, settings);

		});

		// Нажатие на кнопку "Сохранить"
		$popup.find('.save_gallery').unbind('click').bind('click', function(e){

			var $html = $('<span>');
			$html.addClass('gallery_btn');
			$html.attr('data-model-id', settings['model_id']);
			$html.attr('data-num', numGallery);
			$html.text('Фотогалерея #'+numGallery);

			// Сохраняем описания
			$.post(
				settings['urlSaveDesc'],
				$(this).parents('form').serialize(),
				function(response){
					// Вставляем нопку фотогалереи в редактор

					data.editor.execCommand(data.command, $('<div>').append($html).html(), null, data.button);


					// Событие на всталенную кнопку фотогалереи в редкаторе
					$(data.editor.doc).find('.gallery_btn').unbind('click').bind('click', function(){
						editGallery($(this), settings);
					});


					$popup.find('.loaded_images').html('');

					data.editor.hidePopups();
					data.editor.focus();
				}
			);

			return false;
		});
	}

	/**
	 * Навешиваем клик на ссылку, по которой показывается попап выбора
	 * галереи из уже загруженных.
	 * @param $popup
	 * @param settings
	 */
	function bindShowSelectPopup($popup, data, settings)
	{
		// Клик по ссылке "выбрать из загруженного"
		$popup.find('.select_gallery').bind('click', function(e){
			$htmlSelect = getTemplate('select');

			$popup.html($htmlSelect);

			bindShowAddPopup($popup, data, settings);

			// Загрузка списка уже имеющихся галерей и материала
			$.ajax({
				url	: settings['urlGetListGallery'],
				type	: 'POST',
				async	: false,
				data	: {modelId: settings['model_id']},
				dataType: 'HTML',
				success	: function(response){
					$popup.find('.loaded_galleries').html(response);
				}
			});

			// Нажатие на кнопку "Вставить"
			$popup.find('.insert_gallery').unbind('click').bind('click', function(e){
				// Вставляем нопку фотогалереи в редактор

				var num = $('.title_gallery:checked').attr('data-num');

				var $html = $('<span>');
				$html.addClass('gallery_btn');
				$html.attr('data-model-id', settings['model_id']);
				$html.attr('data-num', num);
				$html.text('Фотогалерея #'+num);

				data.editor.execCommand(data.command,  $('<div>').append($html).html(), null, data.button);

				// Событие на всталенную кнопку фотогалереи в редкаторе
				$(data.editor.doc).find('.gallery_btn').unbind('click').bind('click', function(){
					editGallery($(this), settings);
				});

				data.editor.hidePopups();
				data.editor.focus();
			});

			return false;
		});
	}

	/**
	 * Навешивает на копки в редакторе показ фотографий уже добавленных к Знанию
	 * @param span
	 */
	function editGallery($span, settings)
	{
		$('.edit_gallery').remove();

		// Получаем номер галереи
		numGallery = parseInt($span.attr('data-num'));

		var top = $('.cleditorToolbar').offset().top+27;
		var left = $('.cleditorToolbar').offset().left+200;

		var $html = $('<div>');
		$html.addClass('edit_gallery');
		$html.css({
			'position': 		'absolute',
			'top': 			top+'px',
			'left': 		left+'px',
			'max-height': 		'480px',
			'overflow-y': 		'scroll',
			'border':		'1px solid #999',
			'backgroundColor': 	'#F6F7F9',
			'padding':		'10px 15px'
		});
		$html.html(getTemplate('add'));

		$.post(
			settings['urlGetImages'],
			$span.data(),
			function(response){
				$html.find('.loaded_images').append(response);

				$('body').append($('<div>').append($html).html());

				var $edit_gallery = $('.edit_gallery');

				$edit_gallery.find('.loaded_images .delete_img_gallery')
					.unbind('click')
					.bind('click', function(){

						deleteImages($(this), settings);

						return false;
					});

				//Событие выбора изображения в input поле
				$edit_gallery.find('.new_image input').unbind('change').bind('change', function(e){

					changeFile($('.edit_gallery'), this, settings);

				});

				$edit_gallery.find('.save_gallery').unbind('click').bind('click', function(e){
					// Сохраняем описания
					$.post(
						settings['urlSaveDesc'],
						$(this).parents('form').serialize(),
						function(response){
							$('.edit_gallery').remove();
						}
					);
					return false;
				});
			}
		);


	}

	function changeFile(container, input, settings)
	{
		var file = input.files[0];
		var url = settings['urlUploadImage'];

		// Сохраняем фотку на серваке и вставляем ее в попап.
		uploadFile(file, url, function(response){
			container.find('.loaded_images').append(response);

			// Событие удаления фотографии
			container.find('.loaded_images .delete_img_gallery:last')
				.unbind('click')
				.bind('click', function(){

					deleteImages($(this), settings);

					return false;
				});
		});
	}

	/**
	 * Навешивает событие удаления фоток из галереи
	 * @param element
	 */
	function deleteImages($element, settings)
	{
		$.post(
			settings['urlDeleteImage'],
			$element.data(),
			function(response){
				$element.parents('.img_item').remove();
			}
		);
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
			url:$.cleditor.imagesPath()+'../templates/gallery/'+template+'.html',
			type: 'GET',
			async: false,
			dataType:'html',
			success: function(response){
				text = response;
			}
		});

		return text;
	}

	/**
	 * Загрзука одного файла на сервер с помощью POST Запроса
	 *
	 * @param file
	 * @param url
	 * @param callback
	 */
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
		formData.append('MediaGallery[upload]', file);
		formData.append('numGallery', numGallery);
		xhr.send(formData);
	}


})(jQuery);