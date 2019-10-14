CCommon = function(){
	var 	fileApiSupport = fileApiCheck(),
		page = $('.-layout-page'),
		_options = {
			domain:window.location.hostname,
			citySet:false, // Установлен ли город в каталоге
			cityUrlPos:2, // Позиция города в урле
			userId:0     //
		};



	/*Функция для определения мобильного устройства*/
	var isMobile = {
		Android:function () {
			return navigator.userAgent.match(/Android/i);
		},
		BlackBerry:function () {
			return navigator.userAgent.match(/BlackBerry/i);
		},
		iOS:function () {
			return navigator.userAgent.match(/iPhone|iPad|iPod/i);
		},
		Opera:function () {
			return navigator.userAgent.match(/Opera Mini/i);
		},
		Windows:function () {
			return navigator.userAgent.match(/IEMobile/i);
		},
		any:function () {
			return (this.Android() || this.BlackBerry() || this.iOS() || this.Opera() || this.Windows());
		}
	};

	function tooltip(){
		tooltip = $('<span>').appendTo('body').addClass('-tooltip');
		$('body').on('mouseover','[data-tooltip]', function(e){
			e.stopPropagation();
			var toggler = $(e.currentTarget), offset = toggler.offset(), tooltip = $('.-tooltip'), match = toggler.data('tooltip').split('-');

			tooltip
				.text(toggler.data('title'))
				.addClass('-tooltip-visible ' + toggler.data('tooltip'));

			switch (match[2]) {
				case 'top':
					t = offset.top - tooltip.outerHeight(true) + 0;
					break;
				case 'bottom':
					t = offset.top + toggler.outerHeight() + 7;
					break;
				case 'middle':
					break;
			}
			switch (match[3]) {
				case 'center':
					l = offset.left - ((tooltip.outerWidth(true) / 2) - (toggler.outerWidth() / 2));
					break;
				case 'left':
					l = offset.left - tooltip.outerWidth(true) + 16;
					break;
				case 'right':
					l = offset.left + toggler.outerWidth() - 16;
					break;
			}
			tooltip
				.css({top: t, left: l, display: 'none'})
				.fadeIn(200);
		}).on('mouseleave','[data-tooltip]', function(){
				var toggler = $(this);
				$('.-tooltip')
					.removeClass('-tooltip-visible ' + toggler.data('tooltip'))
					.removeAttr('style');
			});
	}

	/**
	 * Функция для переключения табов, при наличии атрибута data-url загружает контент и вставляет в таб.
	 * @param container - контейнер с табами
	 * @param afterload - callback-функция, вызывается после загрузки контнента.
	 */
	function tabs(container,afterload){
		var controls = container.find('.-tab-menu');
		controls.on('click','li',function(){
			var li = $(this),
				data = li.data(),
				tabContent = container.next();

			if (!li.hasClass('current')){
				if (data.url)
					_loadContent(li,tabContent);
				else
					_changeContent(li,tabContent);
			}

			controls.find('li')
				.removeClass('current');

			li.addClass('current');
		});
		function _loadContent(li,tabContent){
			var data = li.data();
			tabContent.addClass('-loading');

			$.get(
				data.url,
				function(data){
					if (data.success) {
						tabContent.replaceWith(data.html);
						if (afterload != undefined){
							afterload();
						}
					} else {
						console.error(data.error)
					}
				}, 'json'
			);
		}
		function _changeContent(li,tabContent){
			var data = li.data();
			if(data.id){
				tabContent.find('.tab')
					.hide()
					.filter('#tab_'+data.id)
					.show();
			}else{
				console.error('Не указан Id таба, который нужно показать');
			}
		}
	}

	/**
	 * функция, для смены режима картинок на grayscale
	 * @param obj - массив объектов, в которых лежит картинка
	 */
	function grayscale(obj){
		obj.BlackAndWhite({
			hoverEffect : true,
			webworkerPath : false,
			responsive:true,
			speed: {
				fadeIn: 200,
				fadeOut: 500
			}
		});
	}

	//функция авторизации
	function login(){
		var popupForm,
		    page = $('body');
		page.on('click','.-login',function(){
			$.modal.close();
			$('#popup-login').modal({
				overlayClose:true,
				onShow: function(){
					_popupFormClick($("#popup-login"));
				}
			});

			return false;
		});


		function _popupFormClick(popupForm)
		{
			popupForm.on('click','.-button',function(){

				/*отправка запроса, вывод ошибок*/
				popupForm.find('.error-title').hide();

				$.post(
					'/site/ajaxlogin', popupForm.find('form').serialize(),
					function(response){
						if (response.success) {
							window.location = window.location.href.replace( /#.*/, '');
						} else if(response.tmpPassRequired) {
							document.forms['ajaxlogin'].submit();
						}
						else {
							if (response.message)
								popupForm.find('.error-title').html(response.message);
							else
								popupForm.find('.error-title').html('Такого пользователя не существует или пароль введен неверно');

							popupForm.find('.error-title').show();
						}
					}, 'json'
				);

				return false;
			});
		}
	}

	function _expandable(){
		return false;
	}

	function slider(slider,afterShow){
		var 	container = slider.find('.-slider-content'),
			slides = container.find('.-slide'),
			cnt = slides.length,
			control = slider.find('.-slider-controls span'),
			preview = slider.find('.-slider-preview span'),
			activeIndex = 0;

		container.width(slides.width()*cnt);
		var gal = container.carousel({
			vertical:'top',
			control:control,
			preview:preview,
			play:4000
		});
	}

	function globalSearch(){
		var input = $('.-layout-header-search-form input'), toHide = input.parent().parent().next().children().not('.pill-buttons');
		input.on('focus blur', function(e){
			switch(e.type) {
				case 'focus': width = 370;
					break;
				default: width = 210;
					break;
			}
			toHide.toggle();
			$(this).stop().animate({width: width}, 'fast').removeClass('-autocomplete');
		});
		 // Тестовые данные
		/*var data = [
			{label:'Ванная',itemLink:'/users/slavyanskii',category:'товары',categoryLink:'google.ru'},
			{label:'Ванная большая',itemLink:'/users/slavyanskii',category:'товары',categoryLink:'google.ru'},
			{label:'Ванная маленькая',itemLink:'/users/slavyanskii',category:'товары',categoryLink:''},
			{label:'Ванная красная',itemLink:'/users/slavyanskii',category:'Идеи',categoryLink:''},
			{label:'Ванная красная',itemLink:'yandex.ru',category:'Идеи',categoryLink:''},
			{label:'Ванная красная',itemLink:'yandex.ru',category:'Журнал',categoryLink:''},
			{label:'Ванная красная',itemLink:'yandex.ru',category:'Журнал',categoryLink:''}
		];*/
		$.widget( "custom.catcomplete", $.ui.autocomplete, {
			_renderMenu: function( ul, items ) {
				var that = this,currentCategory = "";
				var l = items.length;

				$.each( items, function( index, item ) {
					if(index==0){
						ul.addClass('-global-search-autocomplete');
						if($('.top-banner').length)
							$('.-global-search-autocomplete').addClass('-with-banner');
					}
					if ( item.category != currentCategory ) {
						currentCategory = item.category;
						$( "<li class='-category -gray -small'>" ).data( "item.autocomplete", item )
							.append( "<a style='-underline' data-url="+item.categoryLink+">" + item.category + "</a>" )
							.appendTo( ul );
					}

					return $( "<li>" ).data( "item.autocomplete", item ).append( "<a href='#' data-url="+item.itemLink+"><span>" + item.label + "</span></a>" ).appendTo( ul );

					that._renderItem( ul, item );
				});
			}
		});

		input.catcomplete({
			minLength: 3,
			delay: 100,
			source: "/search/ajaxAutocomplete",// Если надо тестить, вставляем data
			create:function(ui,ul){
				$('.-global-search-autocomplete').css({'overflow':'visible',top:top});
			},
			open:function(data){
				input.addClass('-autocomplete');
				$('.-global-search-autocomplete').css({top:top})
					.append("<li class='-all-search-results'><a class='-pointer-right -red' href='http://"+document.domain+"/search?q="+input.val()+"'>" +
						"<span>Показать больше результатов для «" + input.val() + "»</span></a></li>");
			},
			select:function(event,ui){
				window.location = ui.item.itemLink;
			}
		});

		$('body').on('click','.-global-search-autocomplete .ui-menu-item a',function(){
			window.location = $(this).data('url');
		});
	}

	/**
	 * функция для фиксации элемента на странице.
	 * После того, как скролл привысит первоначальное положение элемента ему будет присвоен класс -affix
	 * @param obj - объект на странице, который нужно зафиксировать
	 */
	function affix(obj){
		if (obj.size()==0)
			return;
		var scroll = obj.offset().top ;
		var body = $('body');
		$(window).on('scroll', function(){

			if ($(this).scrollTop() > scroll) {
				if (!body.hasClass('-affix')) {
					body.addClass('-affix');
				}
			}
			else {
				if (body.hasClass('-affix')) {
					body.removeClass('-affix');
				}
			}
		});
	}

	/*Кнопка "Наверх"*/
	function scrollTop(){
		$('body').append($('<div class="-up -gray -hidden '+narrow+'"><i class="-icon-arrow-up -icon-only"></i> <span>Наверх</span></div>'));
		var win = $(window),
			docH = $(document).height(),
			winH = win.height(),
			narrow = (win.width()>1080) ? "" : "-narrow",
			link  = $('.-up');

		var tmp = $('.-layout-header');
		var topPosition = (tmp.size() > 0) ? tmp.offset().top : 0;
		topPosition = (topPosition < 0) ? 0 : topPosition;
		link.addClass(narrow);
		win.scroll(function(){
			winH = win.height();
			if (win.scrollTop() > topPosition) {

				if (link.hasClass('-hidden')) {
					link.removeClass('-hidden').fadeIn();
				}

			} else {

				if (!link.hasClass('-hidden')) {
					link.fadeOut(100, function(){
						link.addClass('-hidden');
					});
				}
			}
		});

		win.resize(function(){
			if(win.width()>1080)
				link.removeClass('-narrow');
			else
				link.addClass('-narrow');
		});

		link.on('click',function(){
			$("html:not(:animated),body:not(:animated)").animate({scrollTop:0}, 200)
		})
	}

	/*Прокрутка странницы до нужного элемента*/
	function scrollTo(hash, callback){
		var 	elem = $(hash),
			offset = elem.offset();
		if ($.browser.safari) {
			var bodyelem = $('body');
		}
		else {
			var bodyelem = $('html, body');
		}
		$(bodyelem).animate({scrollTop: offset.top-40}, 500, callback);
	}

	/*подсказки, hint*/
	function hintUp(){
		var page = $('.-layout-page');
		page.on('click', '[data-dropdown]', function(e){
			var 	toggler = $(this),
				data = toggler.data(),
				dropdown = $('#' + data.dropdown),
				position = toggler.position(),
				width = dropdown.outerWidth(),
				togglerMargin = parseInt(toggler.css('margin-left')); // отступ указателя слева, для его выравнивания по центру;
			$('.-dropdown:visible').hide();
			if (data.dropdownRight) {
				left = position.left - 20 + toggler.width() - dropdown.width();
				pointer = dropdown.width() - (toggler.width() / 2);
			}
			else {
				left = (position.left + toggler.width()/2 + togglerMargin) - width/2;
				pointer = "50%";
			}
			dropdown.css({left:left,top: position.top+15})
				.find('.-dropdown-pointer i')
				.css({left: pointer})
				.end()
				.fadeToggle('fast');
			return false;
		});

		/*скрываем подсказку при клике мимо области, возвращаем ее первоначальный вид*/
		page.on('click', function(e){
			var match = $(e.target).closest("[data-dropdown], .-dropdown");
			if (!match.length){
				$('.-dropdown:visible').hide()
					.find('.-dropdown-content>div')
					.hide()
					.eq(0)
					.show();
			}
		});
	}

	/**
	 * Автокомплиты
	 * @param input - input, на который навешивается автокомплит (объект jQuery)
	 * @param url - url для загрузки вариантов (строка)
	 * @param clear - наличие иконки для очистки автокомплита (boolean)
	 * @param submit - флаг для перезагруски страницы после выбора варианта (boolean)
	 */
	function CustomAutocomplete (input, url, clear, submit){
		$('body').append('<div class="standart-autocomplete"></div>');
		var url =[
			{label:'Новосибирск',id:1},
			{label:'Москва',id:2},
			{label:'Красноярск',id:3},
			{label:'Черепаново',id:4},
			{label:'Пашино',id:5}
		];
		input.autocomplete({
			minLength: 2,
			delay: 100,
			source: url,
			appendTo:'.standart-autocomplete',
			position:{'offset':'0 -2'},
			select:function(event, ui){
				$(this).prev()
					.val(ui.item.id)
					.change();
			}
		});
		if(clear == true){
			input.after('<i class="-icon-cross-circle-xs -icon-only -red -absolute clear-autocomplete"></i>');
			input.next().on('click',function(){
				input.val('')
					.prev()
					.val('')
					.change();
			})
		}
		if(submit == true){
			input.prev().change(function(){
				input.parents('form')
					.submit();
			});
		}
	}

	/*функции для работы с избранным*/
	function initFavorite(){
		var 	body = $('body'), popup;

		body.on('click', '.favorite-icon, .add_to_favorite > span',function(){
			var 	favorite = $(this);
			popup = $('#popup-favorite')
			if(favorite.hasClass('-icon-heart')){
				_removeFromFavorite(favorite);
			}else{
				if(favorite.hasClass('guest'))
					_addToFavorite(favorite);
				else
					_openFavorite(favorite);
			}
		});

		function _openFavorite(favorite){
			popup.modal({
				overlayClose:true,
				onShow: function(c){
					popup = c.data;
					_listOptions(favorite);
				},
				onClose: function(){
					_closeFavorite();
					$.modal.close();
				}
			});
			favorite.attr('data-group-id', popup.find('select').val())
				.data('group-id', popup.find('select').val());

			popup.find('.-button').off('click')
				.on('click',function(){
					_addToFavorite(favorite);
					return false;
				});
		}

		function _closeFavorite(favorite){
			popup.find('input[type="text"]').val('');
			popup.find('select').focus().change();
		}

		function _listOptions(favorite){
			popup.find('select, input[type="text"]').off('focus').on('focus',function(){
				$(this).prev().find('input').prop('checked',true);
			});
			popup.find('select, input[type="text"]').off('change').on('change',function(){
				if ($(this).attr('type') == 'text')
					favorite.attr('data-group-id', 'new').data('group-id','new');
				else
					favorite.attr('data-group-id', $(this).val()).data('group-id', $(this).val());
			});
		}

		function _addToFavorite(favorite){
			var data = favorite.data();
			if (data.groupId == 'new') {
				/* Создаем список */
				$.ajax({
					url: 	'/member/favorite/create/name/' + popup.find('input[type="text"]').val(),
					async: 	false,
					success:function (response) {
						if (response.success) {
							groupId = response.id;
							/*
							 После удачного создания Группы пишем ее в дату,
							 чтобы затем добавить элемент в эту группу так, какбдто
							 он уже был ранее.
							 */
							data.groupId = groupId;

						} else {
							alert("Ошибка создания списка!\n" + response.html);
						}
					},
					dataType: 'json'
				});
			}

			if (parseInt(data.groupId) >= 0)

				// добавляем в избранное
				$.post(
					'/member/favorite/additem/groupid/'+data.groupId+'/itemid/'+data.itemId+'/itemmodel/'+data.itemModel,
					{data:data.data},
					function(response) {
						
						if (response.success) {
							_closeFavorite(favorite);
							favorite.removeClass('-icon-heart-empty').addClass('-icon-heart');
							_favoriteLinkCnt($('.myfavorite'), 'plus');
							switch (favorite.data('callback')) {
								case 'text':
									favorite.find('i, span').text('Из избранного');
									break;
								case 'tooltip':
									favorite.attr('data-title','удалить из избранного').data('title','удалить из избранного');
									break;
							}

							$.modal.close();

							if (response.needReloadPage) {
								window.reload();
							}
						} else {
							alert(response.error);
						}
					}, 'json'
				);
		}

		function _removeFromFavorite(favorite){

			doAction({
				'yes':function(){
					var data = favorite.data();
					$.get(
						'/member/favorite/removeitem/itemid/'+data.itemId+'/itemmodel/'+data.itemModel,
						function(response){
							if (response.success) {
								favorite.removeClass('-icon-heart').addClass('-icon-heart-empty');
								_closeFavorite(favorite);
								switch (data.callback) {
									case 'text':
										favorite.find('i,span').text('В избранное');
										break;
									case 'tooltip':
										favorite.attr('data-title','добавить в избранное').data('title','добавить в избранное');
										break;
								}

								if (data.deleteItem)
									_removeFromFavoritePage(favorite);
								else
									_favoriteLinkCnt($('.myfavorite'), 'minus');

							} else {
								alert(response.error);
							}

						}, 'json'
					);

				},
				no: function(){
				}
			}, 'Удалить из избранного?');
		}

		//функция для удаления элемента на странице избранного, при удалении его из избранного
		function _removeFromFavoritePage(favorite)
		{
			var favoriteContainer = favorite.parents('.favorite_item');
			favorite.parents('.item').fadeOut(200, function(){
				$(this).remove();

				_favoriteLinkCnt(favoriteContainer.find('h3'), 'minus');

				if(favoriteContainer.find('.favorite_list_conteiner .item').length == 0)
					favoriteContainer.hide().remove();
			});
		}

		/**
		 * Функция наращивает или уменьшает кол-во элементов в избранном
		 * Для объекта внутри которого есть элемент с классом .quant_f
		 * Если deleteObj == true и счетчик кол-ва уменьшается до ноля,
		 * объект obj удаляется со страницы.
		 *
		 * @param obj Объект внутри которого есть элемент с классом .quant_f, содержащий число
		 * @param sign Действие: plus и minus
		 * @param deleteObj Флаг удаления объекта obj после обнуления счетчика
		 */
		function _favoriteLinkCnt(obj,sign,deleteObj) {
			if (deleteObj === undefined)
				deleteObj = false;

			var favoriteIcon = $('.user-favorite');
			var data = favoriteIcon.data();
			var span = obj.find('.quant_f');
			var qnt = data.qnt;


			if (sign == "plus") {
				qnt = qnt+1;
				span.text(qnt);
				favoriteIcon.removeClass('-icon-heart-empty')
					.addClass('-icon-heart')
					.data('qnt',qnt);
			} else {

				if ((qnt - 1) <= 0)
				{
					qnt = 0
					favoriteIcon.removeClass('-icon-heart')
						.addClass('-icon-heart-empty')
						.data('qnt',qnt);
					span.text(qnt);

					if (deleteObj)
						obj.remove()

				} else {
					qnt = qnt-1
					span.text(qnt);
					favoriteIcon.data('qnt',qnt);
				}
			}

			var title = formatNumeral(qnt,['элемент', 'элемента', 'элементов']);
			favoriteIcon.data('title','В избранном ' + title).attr('data-title','В избранном ' + title);
		}
	}

	/*cookie management*/
	function setCookie(name, value, props) {
		props = props || {};
		var exp = props.expires;
		if (typeof exp == "number" && exp) {
			var d = new Date();
			d.setTime(d.getTime() + exp*1000);
			exp = props.expires = d;
		}
		if(exp && exp.toUTCString) { props.expires = exp.toUTCString() }

		value = encodeURIComponent(value);
		var updatedCookie = name + "=" + value;
		for(var propName in props){
			updatedCookie += "; " + propName;
			var propValue = props[propName];
			if(propValue !== true){ updatedCookie += "=" + propValue }
		}
		document.cookie = updatedCookie
	}

	function getCookie(name) {
		var matches = document.cookie.match(new RegExp(
			"(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
		));
		return matches ? decodeURIComponent(matches[1]) : undefined
	}

	function deleteCookie(name, options) {
		options = options || {};
		value = '';
		options.expires = -1;
		var expires = '';
		if (options.expires && (typeof options.expires == 'number' || options.expires.toUTCString)) {
			var date;
			if (typeof options.expires == 'number') {
				date = new Date();
				date.setTime(date.getTime() + (options.expires * 24 * 60 * 60 * 1000));
			} else {
				date = options.expires;
			}
			expires = '; expires=' + date.toUTCString(); // use expires attribute, max-age is not supported by IE
		}
		var path = options.path ? '; path=' + options.path : '';
		var domain = options.domain ? '; domain=' + options.domain : '';
		var secure = options.secure ? '; secure' : '';
		document.cookie = [name, '=', encodeURIComponent(value), expires, path, domain, secure].join('');
	}

	/*----cookie management*/

	/* oauth блок */
	function oauth(url, title)
	{
		var width = 560;	// ширина окна
		var height = 325;	// высота окна

		// Отступ слева
		var left = parseInt(document.documentElement.clientWidth / 2 - width / 2);
		var top = 250; // Отступ сверху


		var params = 'width='+width+', height='+height+', top='+top+', left='+left+', scrollbars=yes';
		oauthWindow = window.open(url, title, params);

		return false;
	}

	function fileApiCheck() {
		var fileApiSupport = getCookie("fileApiSupport");
		if (typeof fileApiSupport == "undefined") {
			if (window.File && window.FileReader && window.FileList && window.Blob && window.FormData !== undefined) fileApiSupport = "true"; else fileApiSupport = "false";
			setCookie("fileApiSupport", fileApiSupport, {expires:31*24*60*60, path:"/"});

		}
		return fileApiSupport === 'true';
	}

	/*Обратная связь*/
	function feedback(){
		//var self = this;
		var inputCount = 1; // Число input file в форме
		var _maxInputCount = 3;
		var popup = $('#popup-feedback');

		$('.-feedback').on('click',function () {
			popup.modal({
				overlayClose:true,
				onShow:function(){
					_clearErrors();
					_clearForm();
				},
				onClose:function(){
					$.modal.close();
					_clearErrors();
					_clearForm();
				}
			});
			return false;
		});
		if (!fileApiSupport) {
			popup.find('.p-feedback-files').remove();
			popup.find('.file_list').remove();
		} else {
			popup.on('change','.file_list input:last',function(){
				_appendInput( $(this) );
			});
		}
		popup.find("#feedback-form").submit(function(){
			_submit(_clearErrors());
			return false;
		});

		function _appendInput(lastInput){
			if ( fileApiSupport && inputCount < _maxInputCount ) { // слишком много инпутов
				inputCount++;
				lastInput.parent().after('<div><input class="" type="file" size="40"></div>');
			}
		}

		function _clearErrors(){
			popup.find("p.error-title").html('').hide();
			popup.find("p.good-title").html('').hide();
			// удаление error
			popup.find('.error').each(function(){
				$(this).removeClass('error');
			});
		}

		// очистка формы
		function _clearForm(){
			$("#p-feedback-message").val("");
			if (fileApiSupport){
				inputCount = 1;
				popup.find('.file_list input').remove();
				popup.find('.file_list').append('<div><input class="" type="file" size="40"></div>');
			}
		}

		function _submit(onsubmit){
			if (typeof onsubmit !='undefined') onsubmit();
			var url = '/site/feedbackmessage';
			var data = {
				'Feedback[name]':$("#p-feedback-name").val(),
				'Feedback[email]': $("#p-feedback-email").val(),
				'Feedback[message]': $("#p-feedback-message").val(),
				'Feedback[page_url]': $("#p-feedback-page_url").val()
			};

			function successHandler(response){

				if (response.success) {
					popup.find("p.good-title").html(response.message).show();
					popup.find('#p-feedback-message').val('');
				} else if (response.error) {
					var errorCont = popup.find("p.error-title");
					var errors = response.description;
					var str = ''
					for (key in errors){
						$('#p-feedback-'+key).addClass('error')
						str += errors[key]+'<br />';
					}
					errorCont.html(str).show();
				}
			}

			function errorHandler(response){
				alert('Произошла ошибка, повторите попытку позже');
			}

			// fileApi send
			if (fileApiSupport) {
				var formData = new FormData();

				for (var key in data) {
					formData.append(key, data[key]);
				}
				var count = 0;
				popup.find('.file_list input').each(function(){
					if (!this.files[0])
						return true;
					formData.append('UploadedFile['+count+']', this.files[0]);
					count++;
				});

				var xhr = new XMLHttpRequest();
				xhr.onreadystatechange = function(){
					if(this.readyState == 4) {
						if(this.status == 200) {
							successHandler($.parseJSON(this.responseText));
						} else {
							errorHandler(this.responseText);
						}
						delete file;
						delete this;

					}
				};
				xhr.open("POST", url);
				xhr.send(formData);

			} else {
				$.ajax({
					'url':url,
					'data':data,
					'dataType':'json',
					'type':'post',
					success: function(response){
						successHandler(response);
					},
					error: function(response) {
						errorHandler(response);
					}
				});
			}
		}

	}

	/*функции для выбора города*/
	function geoIp(cookieName){

		var block = $('#city_selector');
		block.find('a.-icon-cross-circle-xs').click(function(){
			deleteCookie(cookieName, {'path':'/','domain':_options.domain});
			setCookie("city_deleted", 1, {expires:31*24*60*60, path:"/"});
			changeUrl('');
		});

		$('#cityInput').autocomplete({
			source:'/utility/autocompletecity',
			minLength:3,
			select:function(event, ui){
				changeUrl(ui.item.path);
			}
		}).next().click(function(){
				$(this).prev().val('').focus();
				return false;
			}).end()
			.keyup(function(e){
				if($(this).val()=='' && e.keyCode == 13) {
					alert('c');
					changeUrl('');
				}
			});

	}

	function changeUrl(city){
		var path = location.pathname,
			newPath='',
			path = path.split('/');

		var pos = _options.cityUrlPos;

		if(city){
			deleteCookie("city_deleted", {'path':'/'});
			if (_options.citySet == true) {

				path[pos] = city;//переопределяем элемент массива если город уже выбран


			} else {
				//добавляем элемент на второе место в массиве, если город не был выбран
				path.splice(pos, 0, city);
			}

			/* Если в пути пять элементов, значит мы находимся
			 * в path лежат следующие данные ["", "catalog", "novosibirsk", "sofas", "russia"]
			 * Информацию о стране нужно удалить
			 */
			if (path.length == 5 && path[4] != city) {
				path.pop();
			}
		}else{
			path.splice(pos,1); //удаляем из массива город, если пользователь удалил фильтр по городу
		}


		/* Собираем новый URL
		 * Начинаем с 1, т.к. в нулевой ячейке пустая строка, полученная
		 * в результате применения split.
		 */

		for (var i = 1 in path) {
			if (path[i]) {
				newPath += '/' + path[i];
			}
		}

		window.location.href = newPath + window.location.search;
	}
	// удаление привязанного города и смена адреса
	function setUrl(url) {
		deleteCookie('city_selected', {'path':'/', 'domain':_options.domain});
		window.location.href='/'+url;
	}

	function setOptions(options){
		$.extend(true, _options, options);
	}


	/*Замена стандартного confirm. */
	function doAction(action, title, description) {
		var body = $('body');

		if (title === undefined) title = 'Выполнить операцию?';

		if (description !== undefined)
			description =  '<p><strong>' + description + '</strong></p>';
		else
			description = '<br>';

		body.append('<div class="-col-7 -white-bg -inset-all" id="popup-confirm">' +
			'	<h2>' + title + '</h2>' +
			'	<div class="-gutter-bottom">' + description +
			'		<p>' +
			'			<a href="#" class="-button -button-skyblue handler-yes">Да</a>' +
			'			<a href="#" class="-button -button-skyblue handler-no">Нет</a>' +
			'		</p>' +
			'	</div>' +
			'</div>');

		$('#popup-confirm').modal({
			overlayClose:true
		});


		/* Отвязываем все навешенные ранее действия на "Yes" и "No",
		 * чтобы они неявно не вызывались.
		 */
		body.off('click', '.handler-yes');
		body.off('click', '.handler-no');

		body.on('click','.handler-yes',function () {
			$.modal.close();
			$('#popup-confirm').remove();

			action['yes']();

			return false;
		});
		body.on('click','.handler-no',function () {
			$.modal.close();
			$('#popup-confirm').remove();

			action['no']();

			return false;
		});
	}
	function toggleText(){
		var page = $('.-layout-page');
		page.on('click', '[data-alt]', function(e){
			var text = $(this).text();
			$(this).text($(this).data('alt')).data('alt',text).attr('data-alt',text);
		});
	}

	function rating(obj) {
		var star = obj.find('i'),
			span = obj.find('span'),
			spanText = span.text(),
			input = obj.find('input'),
			index;

		star.mouseenter(function () {
			index = $(this).index();
			star.filter(':lt(' + (index + 1) + ')').addClass('-selected');
			star.filter(':gt(' + (index) + ')').removeClass('-selected');
			span.text($(this).data('rating'));
		}).click(function () {
			index = $(this).index();
			star.filter(':lt(' + (index + 1) + ')').addClass('-icon-star -red').removeClass('-icon-star-empty -red');
				star.filter(':gt(' + (index) + ')').removeClass('-selected');
			input.val(index + 1);
			spanText = $(this).data('rating');
		});

		obj.mouseenter(function () {
			star.removeClass('-icon-star').addClass('-icon-star-empty');
		}).mouseleave(function () {
			if (!input.val() == '') {
				star.filter(':lt(' + (input.val()) + ')').addClass('-icon-star').removeClass('-icon-star-empty');
			}
			star.removeClass('-selected');
			span.text(spanText);
		});
	}

	function hidePromoButton(){
		$('.promo-button i').click(function(){
			$(this).parent().slideUp('fast',function(){
				//тут делаем так что бы плашка больше не показывалась
				$.ajax({
					type: "POST",
					url: "/utility/closeStoreBlock",
					async: true,
					dataType:"json",
					success:function(response){}
				});
			})
		})
	}

	function toggleMyhomeHeadder(){
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
	//ВЫНЕСТИ В ДРУГИЕ ФАЙЛЫ

	/*Регистрация*/
	function register(){
		$('.reg-agreement a').on('click',function(){
			$('#popup-register-agreement').modal({
				overlayClose:true,
				onShow:function(){
					$('#site-rules').tinyscrollbar();
				}
			});
			return false;
		});
	}

	function offerPopup() 
	{
		$('#toggleOfferPopup').on('click',function(){
			$('#popupOfferAgreement').modal({
				overlayClose:true,
				onShow:function(){
					$('#offerBody').tinyscrollbar();
				}
			});
			return false;
		});
	}

	/*Попапы для Личных Сообщений*/
	function userMessage() {
		var page = $('.page-content');
		page.on('click', '#new_message, .write-message', function () {
			if ($(this).hasClass('-guest')) {

				$('#popup-message-guest').modal({
					overlayClose: true
				});

			} else {
				$.ajax({
					type: "POST",
					url: "/member/profile/StatHitAjax",
					async: false,
					data:{'userId':_options.userId},
					dataType:"json",
					success:function(response){

					}
				});

				$('#popup-message').modal({
					overlayClose: true,
					onShow: function () {
						$('#recipient').autocomplete({
							'showAnim': 'fold',
							'delay': 10,
							'autoFocus': true,
							'create': function (event, ui) {
								$("#recipient").autocomplete("search");
							},
							'select': function (event, ui) {
								$("#MsgBody_recipient_id").val(ui.item.id);
							},
							'source': '/utility/autocompleteuser'
						});
						$("#userMessage_files").MultiFile({
							'afterFileAppend': function (element, value, master_element) {
								var selector = master_element.list.selector;
								$(selector).appendTo("#fileslist");
							},
							'accept': 'jpg|jpeg|png|bmp|zip|pdf',
							'max': 10,
							'STRING': {'remove': ' ', 'denied': 'Данный тип файла запрещен к загрузке', 'duplicate': 'Уже выбран'}
						});
					}
				});
			}
			return false;
		});
	}

	/**
	 * Отображение попапа об удачной отправке сообщения
	 */
	function userMessageGood()
	{
		$('#popup-message-good').modal({
			overlayClose: true
		});
	}

	/*выбор города*/
	function citySelector(){
		$('.toggle-next').on('click', function(){
			$(this).parent().toggle()
				.next().toggle()
				.find('input').focus();
		});
	}

	function hitUserServ($userId, $serviceId, $cityId){

		$.ajax({
			type: "POST",
			url: 	'/stat/StatHitAjax',
			async: 	false,
			dataType: 'json',
			data:{'item':{'userId':$userId, 'serviceId':$serviceId, 'cityId':$cityId}}
		});
	}

	/**
	 * @brief Возвращает правильное слово для указанного числительного
	 * @param integer $value Целое число
	 * @param array $words Массив из 3-х элементов. Например array('морковка', 'морковки', 'морковок')
	 * @param boolean $only_word Если флаг указан true, то возвращается только слово, без числа $value
	 * @return string
	 */
	function formatNumeral(value, words) {
		if (words == undefined)
			words = ['услуга', 'услуги', 'услуг'];

		var only_word = false;
		var num = value * 1;

		var cases = [2, 0, 1, 1, 1, 2];

		// Получаем правильное слово для указанного числа
		var result = words[ (num % 100 > 4 && num % 100 < 20) ? 2 : cases[min(v = [num % 10, 5])] ];
		// Возвращаем вместе с числом или только слово.
		if (only_word)
			return result;
		else
			return num + ' ' + result;
	}

	function initPayment() {
		$('body').on('click', '.pay', function () {
			var data = $(this).data();

			var itemId = $(this).attr("data-id");

			var form = $('#pay-form'),
			    indexPage = $(this).find('.-checkbox'),
			    indexPageVal = indexPage.find('input').prop('checked');

			$.ajax({
				url: '/member/specialist/GetPayFormAjax',
				async: false,
				type:"POST",
				data: {item:{id:itemId, serviceId:data.serviceId, cityId:data.cityId}},
				success: function (response) {
					if (response.success) {
						$('#pay-form').html(response.html)
							.modal({
								overlayClose: true,
								onShow: function (obj) {
									var form = obj.data,
										service = form.find('.services');
									service.find('span').text(data.service);
			 						service.find('input').val(form.find('#servId').val());
									_changeService(form);
									_selectMethod(form);
									//_loadPrice(data.serviceId, data, form);
								}
							});

					} else {
						alert("Ошибка создания списка!\n" + response.html);
					}
				},
				dataType: 'json'
			});

			form.on('changeCity', function(e, cityId) {
				var 	form = $('#pay-form');
					servId = form.find('#servId').val();
				_loadPrice(servId, cityId, form)

			});

			return false;
		});

		function _loadPrice(serviceId, cityId, form){

			$.ajax({
				url: '/member/specialist/GetRate',
				async: false,
				type:"POST",
				data: {item:{serviceId:serviceId, cityId:cityId}},
				success: function (response) {
					if (response.success) {

						form.find('.loaded-content').html(response.html);
					} else {
						alert("Ошибка создания списка!\n" + response.html);
					}
				},
				dataType: 'json'
			});

			//ajax- запрос для подгрузки цен, по success
			//
			_selectMethod(form);
		}

		function _selectMethod(form){

			form.on('click','.loaded-content .-col-wrap',function(){
				var 	parent = $(this).parent(),
					input = parent.find('input'),
					value = $(this).data('value'),
					select = $(this).data('select');

				parent.find('.-col-wrap').removeClass('current');
				$(this).addClass('current');
				input.val(value);
				if(select == 'period'){
					form.find('.summary .summ').text($(this).find('.summ').text());
					_calcPrice(form);
					form.find('span>span').addClass('selected')
				}
			});

			form.find('.-checkbox input').change(function(){
				var 	span = $(this).next().find('span'),
					summary = form.find('.summary .summ'),
					linkInMain = form.find('#linkInMain');

				span.toggleClass('selected checked');

				if(linkInMain.hasClass('-hidden')){
					linkInMain.removeClass('-hidden');

				} else{
					linkInMain.addClass('-hidden');
				}
				_calcPrice(form);
			});
		}

		function _changeService(form){
			$('body').click(function(e){
				var services = $(e.target).closest(".dropdown");
				if (!services.length){
					$('.dropdown .-hidden:visible').hide();
				}
			});

			form.find('.service .dropdown').click(function(){
				$(this).find('.-hidden').toggle();
				$(this).find('.-hidden').tinyscrollbar();
			}).find('li:not(.level1)').click(function(){
				var 	id = $(this).data('id'),
					name = $(this).find('a').text();

				$(this).parents('.dropdown').find('>span').text(name);
				$(this).parents('.dropdown').find('>input').val(id);
				var cityId = form.find('#val_city_id').val();
				if($(this).parents('.dropdown').hasClass('services')){

					_loadPrice(id, cityId, form)

				}

			});
		}

		function _calcPrice(form){

			var 	totalPrice = form.find('.summary .summ'),
				indexPage = form.find('.-checkbox'),
				indexPageVal = indexPage.find('input').prop('checked'),

				indexPagePrice = indexPageVal ? parseInt(indexPage.find('span>span').text()) : 0,
				servicePrice = parseInt(form.find('.period .current').find('.summ').text());
			totalPrice.text(indexPagePrice + servicePrice + ' руб.');

			var inMainPrice = Math.round(servicePrice*0.75);
			//indexPageVal.val(Math.round(servicePrice*0.75));
			form.find('#inMain').text('+'+inMainPrice+' руб.');
			form.find('#totalPrice').val(indexPagePrice + servicePrice);

			indexPage.find('input').val(inMainPrice);
		}
	}

	return {
		tooltip:tooltip,
		tabs:tabs,
		grayscale:grayscale,
		login:login,
		slider:slider,
		affix:affix,
		globalSearch:globalSearch,
		feedback:feedback,
		scrollTop:scrollTop,
		scrollTo:scrollTo,
		register:register,
		userMessage:userMessage,
		hintUp:hintUp,
		CustomAutocomplete:CustomAutocomplete,
		citySelector:citySelector,
		userMessageGood:userMessageGood,
		offerPopup:offerPopup,
		setCookie:setCookie,
		getCookie:getCookie,
		deleteCookie:deleteCookie,
		oauth:oauth,
		geoIp:geoIp,
		changeUrl:changeUrl,
		setUrl:setUrl,
		setOptions:setOptions,
		doAction:doAction,
		isMobile:isMobile,
		initFavorite:initFavorite,
		toggleText:toggleText,
		hitUserServ:hitUserServ,
		rating:rating,
		hidePromoButton:hidePromoButton,
		toggleMyhomeHeadder:toggleMyhomeHeadder,
		formatNumeral:formatNumeral,
		initPayment:initPayment
	};


}();

$(function(){
	CCommon.globalSearch();
	CCommon.feedback();
	CCommon.hintUp();
	CCommon.toggleText();
	if(CCommon.isMobile.any() == null){
		CCommon.affix($('.-layout-header'));
		CCommon.scrollTop();
		CCommon.tooltip();
	}else{
		$('body').addClass('touch');
	}

	// обработка кнопок формы регистрации
	/*
	$('.reg-choise').on('click', function(e) {
		// console.log();
		if($(e.target).hasClass('reg-exec')) {
			$('#regForm').css('display', 'none');
			$('#regFormExec').css('display', 'block');
		} else {
			$('#regForm').css('display', 'block');
			$('#regFormExec').css('display', 'none');
		}
	})
	*/
});
