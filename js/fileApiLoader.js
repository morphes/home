/**
 * Created with JetBrains PhpStorm.
 * User: serg
 * Date: 6/19/12
 * Time: 8:39 AM
 * Объект, который предназанчен для загрузки фотографий на сервер с помощью
 * технологии fileAPI
 */

var fileApiLoader = {
	// Массив объектов для хранения выбранных пользователем фотографий
	filesList: [],
	// Текущий индекс для файла в массиве filesList
	indexFile: 0,
	// Массив размеров загружаемых на сервер файлов
	totalLoaded: [],
	// Общий размер всех загружаемых на сервер файлов
	totalSize: 0,
	// Временный массив, в который сохраняются выбранные файлы из конкретного input'а
	files: [],
	// Имя массива, которое должно быть у всех параметров для отправки на сервер.
	namePostParams: '',

	/**
	 * Общая инициализация. Передаются и настриваются общие параметры
	 * @param settings
	 */
	initMain: function(settings)
	{
		var self = this;


		self.namePostParams = settings.namePostParams;
	},

	/**
	 * Функция которую вызываем, чтобы инициализировать возможность загрузки
	 * файлов с помощью fileApi на сервер.
	 *
	 * @param params Объект настроечных параметров
	 * {
	 * 	input 			JQuery 	- объект JQuery input="file" для выбора изображения
	 *      containerForImages 	JQuery 	- объект JQuery элемента, в который будем складывать изображения
	 *      imgBlock		string 	- html код изображения для складывания в containerForImages
	 *      				  В html коде обязательное наличие css классов
	 *      				  .api_img - указывается для картники, в которую будет вставлена превьюшка
	 *      				  .api_del_img - указывается на элемент, который используется для удаления, выбранного изображения
	 *      				  .api_img_desc - указывается textarea, в которую вписывается описание
	 *      hideInputAfterSelect	boolean	- флаг обозначающий необходимость скрыть input со страницы
	 *      additionalAttributes	Object	- объект вида id:value для дополнительных параметров. Доп. параметры
	 *      				  сохраняются в виде атрибутов к картинке, а затем отправляются на сервер.
	 * }
	 */
	initLoadImages: function(params)
	{
		var self = this;

		params.imgBlock = $('<div>'+params.imgBlock+'</div>');

		params.input.live('change', function(evt){
			self.files = evt.target.files;
			self._showFiles(params)
		});
	},

	/**
	 * Функция для показа выбранных пользователем изображений в input=file
	 *
	 * @param params Входные параметры передаваемые в функцию initLoadImages
	 * @param i Порядковый номер файла (в случае multiple) выбранного пльзователем
	 */
	_showFiles: function(params, i)
	{
		var self = this;

		if(typeof(i) == 'undefined')
			i = 0;

		if(i < self.files.length) {
			self._showFile(params, i, function(i){
				self._showFiles(params, i+1);
			});
		}
	},

	/**
	 * Загружает файл пользователя в браузер, получает его данные и показывает на странице.
	 *
	 * @param params Входные параметры передаваемые в функцию initLoadImages
	 * @param i Порядковый номер файла (в случае multiple) выбранного пользователем
	 * @param callback Функция обратного вызова. Нужна для последовательной загрузки нескольких
	 * 	выбранных файлов в Input=file (случай multiple)
	 */
	_showFile: function(params, i, callback)
	{
		var self = this;

		var f = self.files[i];

		if (!f.type.match('image.*')){
			alert('Файл ' + f.name + ' не является изображением.');
			if(callback != undefined) callback(i);
			return;
		}

		var reader = new FileReader();
		reader.onload = (function(theFile)
		{
			return function(e)
			{
				var th = this;
				var img = new Image();
				img.onload = function()
				{
					var styleOrientation = (img.width > img.height)
						? 'height:150px;'
						: 'width:150px;';

					// При необходимости скрываем Input
					if (params.hideInputAfterSelect)
						params.input.parent().hide();


					/* ------------------------------------------------------
					 *  НАВЕШИВАЕМ СВОЙСТВА ДЛЯ ЭЛЕМЕНТОВ БЛОКА С КАРТИНКОЙ
					 * ------------------------------------------------------
					 */
					var imgBlock = params.imgBlock.clone();

					imgBlock.find('.api_img')
						.attr('src', e.target.result)
						.attr('style', styleOrientation)
						.attr('id', 'image_'+self.indexFile);

					// Если есть дополнительные атрибуты добавляем их картинке
					if (params.additionalAttributes != undefined) {
						var all_params = '';
						for (var param in params.additionalAttributes) {
							imgBlock.find('.api_img').attr(param, params.additionalAttributes[param]);

							all_params += param+',';
						}

						imgBlock.find('.api_img').attr('add_attr', all_params);
					}


					imgBlock.find('.api_del_img')
						.attr('id', self.indexFile);

					imgBlock.find('.api_img_desc')
						.attr('name', self.namePostParams+'[desc]['+self.indexFile+']');


					// Вставляем блок с картинкой на страницу
					params.containerForImages.append(imgBlock.html());


					/* ----------------------------
					 *  Навешиваем событие УДАЛЕНИЯ
					 * --------------------------
					 */
					$('#'+self.indexFile).live('click', function(){

						if ($(this).attr('id')) {
							delete self.filesList[this.id];
							$(this).parents('.api_img_block').remove();
						}

						// Если Input скрыт, то показываем его
						if (params.input.parent().is(':hidden'))
							params.input.parent().show();

						return false;
					});


					self.filesList[self.indexFile] = theFile;
					self.indexFile++;
					delete self.files[i];
					delete th;
					if(callback != undefined) callback(i);
				};

				img.src = e.target.result;
			};
		})(f);
		reader.readAsDataURL(f);
	},



	/**
	 * Запуск загрузки фотографий на сервер и последующий сабмит формы.
	 * Скрывает со страницы кнопки сабмита формы actionsContainer, показывает
	 * прогресс бар progressbarContainer
	 *
	 * @param params Объект настроечных параметров
	 * {
	 * 	actionsContainer	JQuery 	- объект JQuery содержащий кнопки сабмита формы
	 *      progressbarContainer 	JQuery 	- объект JQuery контейнера с прогрессбаром
	 *      progressPercent		JQuery 	- объект JQuery ссылающийся на элемент, отвечающий за
	 *      				  отображение процента прогресса. Ему меняется css
	 *      				  свойство width в процентах.
	 *      progressText		JQuery	- объект JQuery ссылающийся на элемент, в который
	 *      				  выводится процент азгрузки текстом x%
	 *      loadUrl			string	- URL страницы на которую будет отправлен POST запрос
	 *      				  с изображением и дополнительными данными
	 *      form			JQuery 	- объкт JQuery ссылающийся на форму, которую нужно сабмитить
	 * }
	 */
	startUpload: function(params)
	{
		var self = this;

		params.progressbarContainer.show();
		params.actionsContainer.hide();

		self.totalSize = self._getFilesSize(self.filesList);

		if(self.filesList.length == 0) {
			self._finishUpload(params);
		} else {
			/*
			 * Проход по массиву файлов и их отправка
			 */
			self._sendFiles(params);
		}
	},

	/**
	 * Функция вызывается для начала отправки всех загруженных пользователем
	 * изображений на сервер.
	 *
	 * @param params Входные параметры, передаваемые в функцию startUpload
	 * @param i Порядковый номер номер файла в массиве filesList
	 * @private
	 */
	_sendFiles: function(params, i)
	{
		var self = this;

		if(typeof(i) == 'undefined')
			i = 0;

		if(i < self.filesList.length) {
			self._sendFile(params, i, function(i){
				self._sendFiles(params, i+1);
			});
		}
	},

	/**
	 * Отправка файла по протоколу XmlHttp
	 *
	 * @param params Входные параметры, передаваемые в функцию startUpload
	 * @param cnt Порядковый номер файла в массиве filesList
	 * @param callback Функция обратного вызова для отправки следующего файла.
	 * 	Используется для последовательной отправки файлов.
	 * @private
	 */
	_sendFile: function(params, cnt, callback)
	{
		var self = this;

		if (typeof(self.filesList[cnt]) == 'object')
		{

			var xhr = new XMLHttpRequest();
			self.totalLoaded[cnt] = 0;

			/*
			 * Подключение обработчика события процесса загрузки (для прогрессбара)
			 */
			if ( xhr )
				xhr.upload.addEventListener("progress", function(e){ self._updateProgress(params, e, cnt) }, false);

			var file = new FormData();

			/*
			 * Событие, вызванное по итогу отправки очередного файла
			 */
			xhr.onreadystatechange = function(){
				if(this.readyState == 4) {

					if(this.status == 200) {
						/*
						 * Отправка всей формы в случае успешной отправки последнего файла в очереди
						 */
						if(cnt == self.filesList.length - 1) {
							self._finishUpload(params);
							return false;
						}
					}
					delete file;
					delete self.filesList[cnt];
					delete this;

					if (callback != undefined) callback(cnt);
				}
			};

			/*
			 * Отправка файла
			 */
			xhr.open("POST", params.loadUrl);

			file.append(self.namePostParams+'[image]', self.filesList[cnt]);
			file.append(self.namePostParams+'[desc]', $('textarea[name="'+self.namePostParams+'[desc]['+cnt+']'+'"]').val());

			// Получаем список дополнительных атрибутов
			var curImg = $('#image_'+cnt);

			var strAttr = curImg.attr('add_attr');
			if (strAttr != undefined)
			{
				var arrAttr = strAttr.split(',');

				if (arrAttr.length > 0) {
					for (var i = 0; i < arrAttr.length; i++) {
						var value = curImg.attr(arrAttr[i]);
						if (value != undefined) {
							file.append(self.namePostParams+'['+arrAttr[i]+']', value);
						}
					}
				}
			}

			xhr.send(file);
		}
		else
		{
			if(cnt == self.filesList.length - 1) {
				self._finishUpload(params);
				return false;
			}

			delete self.filesList[cnt];
			delete this;

			if (callback != undefined) callback(cnt);
		}
	},

	/**
	 * Функция отрисовки прогрессбара
	 *
	 * @param params Входные параметры, передаваемые в функцию startUpload
	 * @param e Событие по загрузки файла на сервер.
	 * @param cnt Порядковый номер файла из массива filesList
	 * @private
	 */
	_updateProgress: function (params, e, cnt)
	{
		var self = this;
		if (e.lengthComputable) {
			self.totalLoaded[cnt] = e.loaded;

			var loaded = 0;
			for(var i = 1; i < self.totalLoaded.length; i++){
				loaded += self.totalLoaded[i];
			}

			var total = parseInt((loaded / self.totalSize) * 100);
			params.progressPercent.css('width', total+'%');
			params.progressText.text(total+'%');
		}
	},

	/**
	 * Функция вызывается по завершению загрузки всех фоток аяксом.
	 * Помещает прогресс бар на 100% и сабмит основную форму.
	 *
	 * @param params Входные параметры, передаваемые в функцию startUpload
	 * @private
	 */
	_finishUpload: function (params)
	{
		params.progressPercent.css('width', '100%');
		params.progressText.text('100%');

		params.form.submit();
	},

	/**
	 * Размер массива файлов для загрузки
	 */
	_getFilesSize: function(files)
	{
		var total = 0;
		for(var i = 0; i < files.length; i++){
			if(typeof files[i] != "undefined")
				total+=files[i].size;
		}
		return total;
	}
};
