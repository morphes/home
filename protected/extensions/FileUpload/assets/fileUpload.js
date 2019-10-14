(function( $ ){

	$.fn.fileUpload = function( options ) {
		var obj = this; // объект jquery инпута
		var self = this.get(0); // js объект инпута

		/** Параметры объекта (для работы и отправки на сервер)*/
		var options = $.extend(true, {}, $.fn.fileUpload.defaultOtions, options || {});
		var queue = []; // очередь отправки
		var inUpload = 0; // Количество файлов в процессе загрузки
		var totalSize = 0; // Размер загружаемых файлов
		var loaded = 0; // Сколько загружено на данный момент

		var _success = function(response){
			options.onSuccess(response);
		}

		var _error = function(response){
			options.onError(response)
		}

		var _progress = function(event){
			loaded += event.loaded;
			var data = {'loaded':loaded, 'total':totalSize};
			options.onProgress(event, data);
		}

		var _finalUpload = function(){
			loaded = totalSize;
			var data = {'loaded':loaded, 'total':totalSize};
			obj.attr({'disabled':false});
			options.onFinished(data);
		}

		/** Обработчик загрузки отдельной formData */
		var _upload = function(formData){
			inUpload++;
			var xhr = new XMLHttpRequest();
			xhr.upload.addEventListener("progress", _progress, false);
			xhr.onreadystatechange = function(){
				if(this.readyState == 4) {
					inUpload--;
					_uploadFiles();
					if(this.status == 200) {
						_success($.parseJSON(this.responseText));
					} else {
						_error(this.responseText);
					}
					delete formData;
					delete this;

				}
			}
			xhr.open("POST", options.url);
			xhr.send(formData);
		}

		/** Обработчик загрузки из очереди */
		var _uploadFiles = function(){
			if (inUpload >= options.maxConnections)
				return true;

			if (inUpload == 0 && queue.length == 0){
				_finalUpload();
				return true;
			}

			while ( queue.length > 0 && inUpload < options.maxConnections ) {
				var data = queue.pop();
				_upload(data);
			}
		}

		/** Составление formData и постановка в очередь на отправку */
		var _sendFiles = function(){
			obj.attr({'disabled':'disabled'});
			var postData = options.postParams;
			var item = self;
			var fileName = options.fileName;

			totalSize = 0;
			loaded = 0;
			if (item.files.length == 0){ return true; }

			/** Отправка одним POST */
			if (options.multiple) {
				var formData = new FormData();
				for (var key in postData) {
					formData.append(key, postData[key]);
				}
				for (var i in item.files) {
					if (item.files[i] instanceof File) {
						totalSize += item.files[i].size;
						formData.append(fileName+'['+i+']', item.files[i]);

					}
				}
				queue.push(formData);
				options.onStart({'loaded':loaded, 'total':totalSize});
				_uploadFiles();
			} else {
				var length = item.files.length;
				for (var i in item.files) {
					if (item.files[i] instanceof File) {
						var formData = new FormData();
						for (var key in postData) {
							formData.append(key, postData[key]);
						}
						totalSize += item.files[i].size;
						formData.append(fileName, item.files[i]);
						queue.push(formData);
					}
				}
				options.onStart({'loaded':loaded, 'total':totalSize});
				_uploadFiles();
			}
		}

		obj.live('change', _sendFiles);
	};

	$.fn.fileUpload.defaultOtions = {
		'url':'',
		'postParams':{},
		'fileName':'file',
		'maxConnections':2,
		'multiple':false, // multiple send
		'onSuccess':function(response){ },
		'onProgress':function(event, data){ },
		'onError':function(response){ },
		'onFinished':function(data){ },
		'onStart':function(data){ }
	};
})( jQuery );

