var minisite = function(){
	function toggleMyHomeHeader(){
		$('#myhomeHeader').hover(function(){
			$(this).stop().animate({top: 0}, 'fast', function(){
				$(this).addClass('visible');
			});
		}, function(){
			$(this).stop().animate({top: -80}, 'fast', function(){
				$(this).removeClass('visible');
			});
		})
	}

	function layoutActions(){
		var 	header = $('.title-image'),
			img = header.find('>img'),
			curBg = '',
			topPosition = 0,
			drag;
		$('body').on('change', '.header-input', function(){
			var file = this.files[0];
			$(this).parents('.title-image').addClass('-loading');
			_uploadHeaderBg(file);
		});
		$('#removeHeaderBg').click(function(){
			_removeHeaderBg();
		});
		header.find('.header-edit a').click(function(){
			$(this).parent().hide();
			_removeHeaderBg();
		});
		header.find('.header-edit span').click(function(){
			$(this).parent().hide();
			_saveHeaderBg();
		});
		header.find('.header-edit span').click(function(){
			$(this).parent().hide();
			_saveHeaderBg();
		});
		header.find('#changeBg').click(function(){
			$('.bg-palette').modal({
				overlayClose: true,
				onShow: function(obj){
					_changePageBg(obj.data);
				}
			});
		});

		// При клике в редактирование Логотипа перебрасываем на страницу редакитрования магазина
		$('body .logo-input').click(function(){
			window.document.location.href = $(this).attr('data-url');
			return false;
		});

		/**
		 * Метод загрузки файла с помощью FormData на сервер.
		 *
		 * @param file Экземпляр файла из тега <input type="file">
		 * @param url Адрес на который будет производится загрузка
		 * @param callback Функция, которая будет вызвана по завершении
		 *  передачи файла на сервер.
		 * @private
		 */
		function _uploadFile(file, url, callback) {
			var xhr = new XMLHttpRequest();
			var formData = new FormData();
			xhr.onreadystatechange = function(){
				if(this.readyState == 4) {
					delete file;
					delete this;
					if (callback != undefined) {
						callback(this.responseText);
					}
				}
			};
			xhr.open("POST", url);
			formData.append("Store[image]", file);
			xhr.send(formData);
		}

		function _uploadHeaderBg(file){

			_uploadFile(file, "/catalog/store/AjaxMoneyHeaderUpload", function(response){
				response = $.parseJSON(response);
				if (response.success) {
					img.attr('src', response.src);

					$('body')
						.removeClass('mini-site-min')
						.addClass('mini-site-full')
						.find('.title-image').removeClass('-loading');
					setTimeout(function(){

						var height = img.height()-230;

						img = header.find('>img').addClass('editable');
						header.find('.header-edit').show();
						header.addClass('editable');
						drag = img.draggable({
							axis: "y",
							stop: function (event, ui) {
								if (ui.position.top > 0) {
									img.animate({'top': 0}, 200);
								} else if (ui.position.top < height * (-1)) {
									img.animate({'top': height * (-1)}, 200);
								}
							}
						});
					}, 400);

				} else {
					var str = '';
					if (response.message instanceof Object) {
						if (response.message instanceof Object) {
							for (key in response.message) {
								str += response.message[key] + '<br>';
							}
						}
					} else {
						str = response.message;
					}
					alert(str);
				}
			});

		}

		function _saveHeaderBg(){
			topPosition = parseInt(img.css('top'));
			//ajax запрос, сохраняющий картинку в хедере
			$.post(
				'/catalog/store/ajaxMoneyHeaderCrop',
				{offsetTop: topPosition},
				function(data){
					drag.draggable('destroy');
					img.removeClass('editable');
					header.removeClass('editable');
				}, 'json'
			);



		}

		function _removeHeaderBg(){
			$.get('/catalog/store/ajaxMoneyHeaderDelete', function(data){
				if (data.success) {
					header.removeClass('editable').find('> img').attr('src', 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==');
					$('body')
						.removeClass('mini-site-full')
						.addClass('mini-site-min');
				} else {
					alert(data.message);
				}
			}, 'json');

		}

		function _changePageBg(obj){
			curBg = $('html').prop('class');
			obj.off('click')
				.on('click','.-button',function(){

					var bgClass = $('.bg-palette').find('span[class~=current]').attr('data-bgClass');
					if (bgClass == undefined) {
						bgClass = 'none';
					}

					$.post(
						'/catalog/store/ajaxMoneySaveBg',
						{bgClass: bgClass},
						function(data){
							if (data.success) {
								$.modal.close();
							} else {
								alert(data.message);
							}
						}, 'json'
					);

				}).on('click','a.-large',function(){
					$('html').removeClass().addClass(curBg);
					$.modal.close();
				})
				.on('click', 'span[class^="bg-"]',function(){
					var className = $(this).attr("class");

					if (!$(this).hasClass('current')) {
						// Если выбрали новый
						$('html').removeAttr("class").toggleClass(className);
						$(this).parent().siblings().children('.current').add(this).toggleClass('current');
					} else {
						// Если кликнули по уже выбранному
						$('html').removeClass();
						$(this).toggleClass('current');
					}
				});
		}
	}

	function toggleDesc(){
		$('.collapsed-text').next().on('click', function(){
			var 	text = $(this).prev(),
				toggler = $(this).next();
			text.toggleClass('expanded');
			toggler.toggleClass('-pointer-down -pointer-up');
		})
	}
	function toggleMap(params){
		$('#toggleMap').click(function(){
			var	 toggler = $(this),
				map = toggler.parent('.map');
			if (map.hasClass('collapsed')) {
				map.animate({width: 940, height: 400}, 'normal', function(){
					params.becomeBig();
				}).toggleClass('collapsed expanded');
				toggler.toggleClass('-pointer-right');
				$(this).find('i').text('Свернуть карту');
			}
			else {
				map.animate({width: 220, height: 140}, 'normal', function(){
					params.becomeSmall();
				}).toggleClass('expanded collapsed');
				toggler.toggleClass('-pointer-right');
				$(this).find('i').text('Развернуть карту');
			}
		});
	}

	function eventActions(){
		var page = $('.mini-site-news');
		$('#addEvent').click(function(){

			$('.event-add-form').modal({
				overlayClose: true,
				onShow: function(obj){
					_saveEvent(obj.data);
				}
			});
		});

		page.on('click','.-icon-pencil-xs', function(){

			var newsId = $(this).parents('.controls').attr('data-newsId');

			$('.event-add-form').modal({
				overlayClose: true,
				onShow: function(obj){
					obj.data.addClass('-loading');
					/*
					 Отправляем Ajax запрос на получение данных, редактируемой новости.
					 */
					$.ajax({
						url: '/catalog/store/ajaxMoneyNewsEdit',
						dataType: 'JSON',
						method: 'POST',
						data: {
							newsId: newsId,
							storeId: $('#storeId').val()
						},
						success: function (data) {
							obj.data.html(data.html).removeClass('-loading');

							_saveEvent(obj.data);
						},
						error: function (data) {
							alert(data.status + ' ' + data.statusText + '(' + data.responseText + ')');
						}
					});

				}
			});
		});

		page.on('click','.-icon-cross-circle-xs', function(){
			var	parent = $(this).parents('.item'),
				id = parent.data('id'),
				newsId = $(this).parents('.controls').attr('data-newsId');


			CCommon.doAction({
				'yes':function(){
					//ajax запрос на удаление новости, по success
					parent.fadeOut(function(){
						$.post(
							'/catalog/store/ajaxMoneyNewsDelete',
							{
								newsId: newsId
							},
							function(data){
								if (data.success) {
									parent.remove();
								} else {
									alert(data.message);
								}
							}, 'json'
						);
					})
				},
				'no' : function(){return false}
			},'Удалить новость?');
		});
		page.on('click','.-icon-cross-circle-xs.detail', function(){
			var newsId = $(this).parents('.controls').attr('data-newsId');


			CCommon.doAction({
				'yes':function(){
					//ajax запрос на удаление новости, по success
					$.post(
						'/catalog/store/ajaxMoneyNewsDelete',
						{
							newsId: newsId
						},
						function(data){
							if (data.success) {
								window.document.location.href = '/news';
							} else {
								alert(data.message);
							}
						}, 'json'
					);

				},
				'no' : function(){return false}
			},'Удалить новость?');
		});

		function _saveEvent(obj){

			obj.find('button.-button').click(function(){

				var form = $(this).parents('form')
					, data = form.serializeArray();

				data.push(
					{
						name: 'storeId',
						value: $('#storeId').val()
					});

				function uploadFile(file, url, data, callback) {
					var xhr = new XMLHttpRequest();
					var formData = new FormData();
					xhr.onreadystatechange = function(){
						if(this.readyState == 4) {
							delete file;
							delete this;
							if (callback != undefined) {
								callback(this.responseText);
							}
						}
					};
					xhr.open("POST", url);
					formData.append("StoreNews[image]", file);

					if (data.length > 0) {
						for (var i = 0; i < data.length; i++) {
							formData.append(data[i]['name'], data[i]['value']);
						}
					}
					xhr.send(formData);
				}

				var errorMessage = $('.error-send-news');
				errorMessage.hide();


				var file = $('.picture_for_news').get(0).files[0];

				uploadFile(file, "/catalog/store/AjaxMoneyNewsImageUpload", data, function(response){

					response = $.parseJSON(response);
					if (response.success) {

						//ajax-запрос, по success:
						$.modal.close();

						window.document.location.reload();

					} else {
						var str = '';
						if (response.message instanceof Object) {
							if (response.message instanceof Object) {
								for (key in response.message) {
									str += '<li>'+response.message[key]+'</li>';
								}
							}
						} else {
							str = response.message;
						}
						errorMessage.show().find('ol').html(str);
					}
				});

				return false;
			});
		}
	}


	function photogalleryActions(){

		var page = $('.mini-site-gallery');
		$('#addPhoto').click(function(){
			$('.photo-add-form').modal({
				overlayClose: true,
				onShow: function(obj){
					_savePhoto(obj.data);
				}
			});
		});

		page.on('click','.-icon-pencil-xs', function(){
			var photoId = $(this).parents('.controls').attr('data-photoId');

			$('.photo-add-form').modal({
				overlayClose: true,
				onShow: function(obj){
					obj.data.addClass('-loading');

					/*
					 Отправляем Ajax запрос на получение данных, редактируемой новости.
					 */
					$.ajax({
						url: '/catalog/store/ajaxMoneyGalleryEdit',
						dataType: 'JSON',
						method: 'POST',
						data: {
							photoId: photoId,
							storeId: $('#storeId').val()
						},
						success: function (data) {
							obj.data.html(data.html).removeClass('-loading');

							_savePhoto(obj.data);
						},
						error: function (data) {
							alert(data.status + ' ' + data.statusText + '(' + data.responseText + ')');
						}
					});
				}
			});
		});

		page.on('click','.-icon-cross-circle-xs', function(){

			var	parent = $(this).parents('.-col-2'),
				photoId = parent.find('.controls').attr('data-photoId');

			CCommon.doAction({
				'yes':function(){
						//ajax запрос на удаление фотографии, по success


						//ajax запрос на удаление новости, по success
						$.post(
							'/catalog/store/ajaxMoneyGalleryDelete',
							{
								photoId: photoId
							},
							function(data){
								if (data.success) {
									parent.fadeOut(function(){
										parent.remove();
									});
								} else {
									alert(data.message);
								}
							}, 'json'
						);
					},
				'no' : function(){return false}
			},'Удалить фотографию?')

		});

		page.on('click', '.thumb', function(){
			_viewPhoto($(this).parent().index());
		});

		page.on('click', '.delete_photo', function(){
			$(this).parents('form').find('.photo-preview').html('');
		});

		function _savePhoto(obj){
			obj.find('button.-button').click(function(){
				btn = $(this);
				if ( ! btn.hasClass('disabled')) {
					btn.addClass('disabled');
				} else {
					return false;
				}

				var 	form = $(this).parents('form'),
					data = form.find('input[type=text], input[type=hidden], textarea').serializeArray();

				data.push(
					{
					name: 'storeId',
					value: $('#storeId').val()
				});

				function uploadFile(file, url, data, callback) {
					var xhr = new XMLHttpRequest();
					var formData = new FormData();
					xhr.onreadystatechange = function(){
						if(this.readyState == 4) {
							delete file;
							delete this;
							if (callback != undefined) {
								callback(this.responseText);
							}
						}
					};
					xhr.open("POST", url);
					formData.append("StoreGallery[image]", file);

					if (data.length > 0) {
						for (var i = 0; i < data.length; i++) {
							formData.append(data[i]['name'], data[i]['value']);
						}
					}
					xhr.send(formData);
				}

				var errorMessage = $('.error-send-photo');
				errorMessage.hide();


				var file = $('.photo_for_gallery').get(0).files[0];

				uploadFile(file, "/catalog/store/AjaxMoneyGalleryImageUpload", data, function(response){
					btn.removeClass('disabled');
					response = $.parseJSON(response);
					if (response.success) {

						//ajax-запрос, по success:
						$.modal.close();

						window.document.location.reload();

					} else {
						var str = '';
						if (response.message instanceof Object) {
							if (response.message instanceof Object) {
								for (key in response.message) {
									str += '<li>'+response.message[key] + '</li>';
								}
							}
						} else {
							str = '<li>'+response.message+'</li>';
						}
						errorMessage.show().find('ol').html(str);
					}
				});



				return false;
			});
			obj.on('click', '.cancel', function(){
				$.modal.close();
				return false;
			})
		}

		function _viewPhoto(index){
			$('.photogallery-view').modal({
				overlayClose: true,
				maxHeight:'90%',
				maxWidth:'90%',
				onShow: function(obj){
					$('body').css({'overflow':'hidden'});
					var	imgContainer = obj.data.find('.image-container'),
						imgInfo = obj.data.find('.image-info'),
						gallery = imgContainer.find('ul'), //контейнер с фотографиями
						control = imgContainer.find('.arrow'),
						imgDesc = imgInfo.find('.photos-descriptions'),
						previewContainer = imgInfo.find('.photos-preview'),
						preview = previewContainer.find('.-col-wrap:not(.arrow)'), //превьюшки
						previewControl = previewContainer.find('.-col-wrap.arrow'),
						next = previewControl.filter('.-slider-next'),
						prev = previewControl.filter('.-slider-prev'),
						activeIndex = 0,
						firstP = 0;

					gallery.height($(window).height()*0.85);
					imgContainer.width(obj.data.width()-250);
					imgInfo.height(obj.data.height()-20);

					var gal = gallery.carousel({
						vertical:'middle',
						preview:preview,
						control:control,
						afterStop:function(e,l){
							//'прелоад' изображений в попапе
							gal[0].loadImages();
							activeIndex = gal[0].getCurPage();

							/*смена описания фотографии*/
							imgDesc.find('div').hide().filter('#ph-'+activeIndex).show();
						}
					});

					/*события для кнопок next/prev превью*/
					next.click(function(){
						if(!next.hasClass('-disabled')){
							preview.eq(firstP).addClass('-hidden');
							preview.eq(firstP+7).removeClass('-hidden');
							firstP++;

							if(firstP > 0){
								prev.removeClass('-disabled');
							}

							if (preview.length-1 <= firstP+6) {
								next.addClass('-disabled');
							}
						}
					});

					prev.click(function(){
						if(!prev.hasClass('-disabled')){
							firstP--;
							preview.eq(firstP).removeClass('-hidden');
							preview.eq(firstP+7).addClass('-hidden');

							if( preview.length-7 > firstP ){
								next.removeClass('-disabled');
							}
							if (firstP <= 0) {
								prev.addClass('-disabled');
							}
						}
					});

					if(index != undefined){
						preview.filter(':not(.arrow)').eq(index).click();
					}
				},
				onClose: function(){
					$('body').css({'overflow':'auto'});
					$.modal.close();
				}
			});

		}
	}

	return {
		toggleMyHomeHeader:toggleMyHomeHeader,
		layoutActions:layoutActions,
		toggleDesc:toggleDesc,
		toggleMap:toggleMap,
		eventActions:eventActions,
		photogalleryActions:photogalleryActions
	}
}();

$(function(){
	minisite.toggleMyHomeHeader();
});