lib.module('mod.Auth');

// Зависимости
lib.include('mod.Common');

var Auth = function() {
	/**
	 *	Функция инициализации класса.
	 *	На элемент с rel=modal_#popupId навешивается
	 *	подгрузка страницы в уже открытое модальное окно
	 */
	function init() {
		$('*[rel="modal"]').on('click', function() {
			var a = $(this),
			    popup = $(a.attr('href')),
			    h = $(window).height(),
			    height, t;
			if (a.data('src')) {
				$.get(a.data('src'), function(data) {
					popup.fadeOut('fast', function() {
						$(this).html(data);
					}).fadeIn('fast', function() {
						height = $(this).height();
						t = (h - height) / 2;
						popup.parents('#simplemodal-container').animate({top: t, height: height}, function() {
							$('.standart-autocomplete > ul').css({borderLeftColor: '#e5e5e5'});
							// фокус на первом текстовом поле формы
							$(':input[type="text"]:eq(0)', popup).focus();
						});
					});
				});
			}
			return false;
		});
		$('*[rel="gethtml"]').on('click', function() {
			var a = $(this),
			    wrapper = $(a.attr('href')),
			    h = $(window).height(),
			    height, t;
			if (a.data('src')) {
				$.get(a.data('src'), function(data) {
					wrapper.fadeOut('fast', function() {
						$(this).html(data);
					}).fadeIn('fast', function() {
						wrapper.addClass('fixed');
					});
				})
			}
			return false;
		});
	}

	/**
	 *	Функция инициализации авторизации
	 */
	function login() {
		// Задаем контекст
		var page = $('body');
		// По клику на объекте с селектором .-login в контексте
		// вызываем модальное окно авторизации
		page.on('click', '.-login', function() {
			// Если мы уже на странице авторизации,
			// то не делаем ничего,
			if (page.hasClass('auth login')) {
				return false;
			}
			// а на любой другой странице:
			else {
				// сначала сворачиваем предыдущее модальное окно,
				$.modal.close();
				// затем открываем div#popupAuth в модальное окно
				$('#popupAuth').modal({
					overlayClose: true,
					// анимируем появление модального окна
					onOpen: function(dialog) {
						_customModalShow(dialog);
					},
					// после появления запускаем обработчик формы
					onShow: function(dialog) {
						_authFormClick(dialog.data);
					},
					// анимируем закрытие модального окна
					onClose: function(dialog) {
						_customModalHide(dialog);
					}
				});
			}
			return false;
		});

		// Если мы на странице авторизации,
		// то модальное окно мы не вызываем, а сразу
		// запускаем обработчик формы авторизации

		if (page.hasClass('auth login')) {
			_authFormClick($('.page-content > .auth-form', page));
		}

		/**
		 *	Функция-обработчик формы авторизации
		 *	@param authForm - объект формы
		 */
		function _authFormClick(authForm) {
			authForm.on('click', '.-button', function(e) {
				e.stopImmediatePropagation();
				var submit = $(this);
				// Если у кнопки сабмита есть класс .-disabled,
				// то ничего не делаем - значит в полях есть ошибки
				if (submit.hasClass('disabled')) return false;

				// Убирает отметки об ошибках
				_clearHighlightedErrors(authForm);

				// Если нет, то сабмитим форму на первом слайде
				// console.log('Done auth!');
				$.post(
					'/site/ajaxlogin', authForm.find('form').serialize(),
					function(response) {
						if (response.success) {
							// Если все данные верны - редиректим
							window.location = window.location.href.replace( /#.*/, '');
						} else {
							// Если есть ошибка, то выводим сообщение рядом с полем
							for (key in response.message) {
								var $inp = authForm.find('input[name="'+key+'"]');
								if ($inp.size() > 0) {
									_highlightError($inp, response.message[key]);
								}
							}
						}
					}, 'json'
				);
				return false;
			});
		}
	}

	/**
	 *	Функция инициализации регистрации
	 */
	function registration() {
		// Задаем контекст
		var page = $('body');
		// По клику на объекте с селектором .-registration в контексте
		// вызываем модальное окно авторизации
		page.on('click', '.-registration', function() {
			// Если мы уже на странице регистрации,
			// то не делаем ничего,
			if (page.hasClass('auth registration')) {
				return false;
			}
			// а на любой другой странице:
			else {
				// сначала сворачиваем предыдущее модальное окно,
				$.modal.close();
				// затем открываем div#popupReg в модальное окно
				$('#popupReg').modal({
					overlayClose: true,
					// анимируем появление модального окна
					onOpen: function(dialog) {
						_customModalShow(dialog, function() {
							$(':input:eq(0)', dialog.data).focus();
						});
					},
					// после появления запускаем обработчик формы
					onShow: function(dialog) {
						_regFormClick(dialog.data);
					},
					// анимируем закрытие модального окна
					onClose: function(dialog) {
						_customModalHide(dialog);
					}
				});
			}
			return false;
		});

		// Если мы на странице авторизации,
		// то модальное окно мы не вызываем, а сразу
		// запускаем обработчик формы авторизации

		if (page.hasClass('auth registration')) {
			_regFormClick($('.page-content > .reg-form', page));
		}

		/**
		 * Функция-обработчик формы регистрации
		 *
		 * @param regForm - объект формы
		 */
		function _regFormClick(regForm) {

			saveRegReturnUrl();

			regForm.on('click', '.-button', function(e) {
				e.stopImmediatePropagation();
				var submit = $(this);
				// Если это кнопка "Далее" на первом слайде,
				if (submit.hasClass('next')) {
					// Если у кнопки сабмита есть класс .-disabled,
					// то ничего не делаем - значит в полях есть ошибки
					if (submit.hasClass('disabled')) return false;

					_clearHighlightedErrors(regForm);

					// Если нет, то сабмитим форму на первом слайде
					// console.log('Done reg!');
					$.post(
						'/site/ajaxRegistration', regForm.find('form').serialize(),
						function(response) {
							if (response.success) {
								// Если все данные верны - редиректим
								// window.location = window.location.href.replace( /#.*/, '');
								
								// Затем переключаемся на второй слайд
								var first = $('.step-1', regForm),
								    last = first.next();
								// анимируем лейблы слайдов ("Анкета" и "Услуги")
								$('.step-label', first)
									.animate({
										backgroundColor: '#fff', 
										borderColor: '#e9e9e9', 
										color: '#cccccc'}, 
										'fast')
								.children()
									.animate({
										borderColor: '#e9e9e9',
										color: '#cccccc'}, 
										'fast',
										// По завершении анимации лейблов
										function() {
											// Удаляем у "потухшего" лейбла класс .current
											$(this).removeClass('current');
											// Двигаем первый слайд на его ширину влево
											first
												.animate({marginLeft: '-560px'}, function() {
												// Анимируем лейбл второго слайда в активное состояние
												$('.step-label', last)
													.animate({
														backgroundColor: '#cccccc',
														borderColor: '#cccccc',
														color: '#ffffff'},
														'fast')
												.children()
													.animate({
														borderColor: '#cccccc',
														color: '#cccccc'},
														'fast',
														// По окончании анимации добавляем этому лейблу класс .current
														function() {
															$(this).addClass('current');
														}
													);
												// Показываем содержимое второго слайда
												$(this).next().find('.-hidden').removeClass('-hidden');
												// Определяем открытое модальное окно
												// для дальнейшей анимации
												var popup = $(this).parents('.simplemodal-data');
												// Изменяем позицию и высоту модального окна
												// исходя из высоты подгруженного содержимого
												popup.parents('#simplemodal-container')
													.animate({
														top: ($(window).height() - popup.height()) / 2, 
														height: popup.height()
													});
												// Навешиваем на список услуг кастомный скроллбар
												$('#servicesList').tinyscrollbar().disableSelection();
											});
										}
									);
							} else {

								// Если нажали кнопку "Далее" в расширенной форме регистрации
								// Подсвечиваем ошибки
								if (response.errorFields instanceof Object) {
									for (key in response.errorFields) {
										_highlightError($('#' + key), response.errorFields[key]);
									}
								}
							}
						}, 'json'
					);

				} else {

					if (submit.hasClass('-button-orange')) {
						// Если нажали на кнопку "Зарегистрироваться"
						// в простой форме регистрации

						// Очищаем ошибки
						_clearHighlightedErrors(regForm);

						$.post(
							'/site/ajaxRegistration',
							regForm.find('form').serialize(),
							function(response) {

								if (response.success) {

									document.location.href = '/site/promo';

								} else {
									// Подсвечиваем ошибки
									if (response.errorFields instanceof Object) {
										for (key in response.errorFields) {
											_highlightError($('#' + key), response.errorFields[key]);
										}
									}
								}
							}, 'json'
						);


					} else if (submit.hasClass('-button-skyblue')) {
						// Если нажали на кнопку "Готово"
						// в расширенной форме регистрации

						$.post(
							'/site/ajaxSaveServices',
							regForm.find('form').serialize(),
							function(response) {

								if (response.success) {

									document.location.href = '/site/promo';

								}
							}, 'json'
						);
					}
				}
				return false;
			// На keyup любого текстового поля формы, кроме автокомплита города, 
			// должна происходить валидация поля
			}).on('keyup', ':input[type="text"]:not(.city-autocomplete)', function() {
				// Валидируем поле
				_validateFormInput($(this), '/site/ajaxRegistration');
			});
		}
		// По клику на псевдорадиобаттоне выбора типа юзера
		// при регистрации ("Специалист" или "Организация")
		page.on('click', '.spec-type li', function() {
			var input = $('div:first-child :input[type="text"]', '#regForm_1'),
			    text = {
			    	o: input.attr('placeholder'),
			    	n: input.data('alt'),
			    	i: $(this).data('id')
			    };
			// Меняем класс у текущего и всех соседних псевдорадобаттонов
			$(this).siblings().andSelf().toggleClass('current');
			// Меняем плейсхолдер у инпута "Название организации"
			// и cохраняем id роли в соседнее скрытое поле 
			input.data('alt', text.o).attr('placeholder', text.n).focus().next().val(text.i);
		});
	}

	/**
	 *	Функция валидации полей формы
	 *	@param input - объект поля формы
	 */
	function _validateFormInput(input, urlValidate) {
		// Если поле не пустое, то валидируем его
		if (input.val() != '') {
			// Очищаем таймаут на событие
			clearTimeout($.data(this, 'timer'));
			// Ждем 1000 миллисекунд после последнего события keyup в инпуте
			var wait = setTimeout(function () {
				// Выполняем валидацию
				var name = input[0].name,
				    value = input[0].value,
				    submit = $(input).parents('form').find('.-button[type="submit"]');

				var obj = {};
				obj[name] = value;

				// Отправляем данные на сервер
				$.post(urlValidate, obj, function(data){

					if (!data.success && data.errorFields[input[0].id]) {
						_highlightError(input, data.errorFields[input[0].id]);
					} else {
						// Удаляем сообщение об ошибке
						input.removeClass('error-field').nextAll('.error-tile').remove();
					}


					submit.removeClass('disabled');

				}, 'json');

			}, 1000);
			$(this).data('timer', wait);
		}
	}

	/**
	 * Функция подсвечивания ошибки рядом с полем формы
	 *
	 * @param input Объект JQuery для подсчечиваемого поля
	 * @param error_msg String Текст ошибки
	 * @private
	 */
	function _highlightError(input, error_msg) {
		// Если это первая ошибка
		if (!input.hasClass('error-field')) {
			$('<span class="error-tile -hidden"></span>')
				// к полю добавляем span.error.-hidden
				.appendTo(input.addClass('error-field').parent())
				// c текcтом data.error
				.text(error_msg)
				// и анимируем его
				.css({maxWidth: input.outerWidth()})
				.css('padding', '0 0.5em')
				.animate({
					left: input.outerWidth() - 2
				});
		}
		// Если ошибка не первая, то
		else {
			// Просто меняем текст ошибки
			input.nextAll('.error-tile').text(error_msg);
		}
	}

	/**
	 *	Кастомная анимация появления модального окна
	 */
	function _customModalShow(dialog, callback) {
		dialog.overlay.fadeIn(200, function() {
			dialog.data.hide();
			dialog.container.fadeIn(200, function() {
				dialog.data.fadeIn(200, callback);
			});
		});
	}

	/**
	 *	Кастомная анимация закрытия модального окна
	 */
	function _customModalHide(dialog, callback) {
		dialog.data.fadeOut(200, function() {
			dialog.container.fadeOut(200, function() {
				dialog.overlay.fadeOut(200, callback);
			});
		});
	}

	/**
	 * Снимает отметку об ошибке в полях формы $form
	 *
	 * @param $form JQuery Объект на форму
	 * @private
	 */
	function _clearHighlightedErrors($form)
	{
		$form.find('.error-field').removeClass('error-field').end().find('.error-tile').remove();
	}

	/**
	 * Сохраняет текущий url пользователя в localStorage браузера.
	 * Он используется затем на странице site/promo для возврата назад
	 */
	function saveRegReturnUrl()
	{
		if (localStorage) {
			localStorage.setItem('returnRegUrl', window.location.href);
		}
	}

	/**
	 * Редиректит пользователя на url, который был записан с помощью метода
	 * saveRegReturnUrl
	 */
	function redirectToRegReturnUrl()
	{
		if (localStorage) {
			var url = localStorage.getItem('returnRegUrl');
			if (url != undefined) {
				localStorage.removeItem('returnRegUrl');
				window.location.href = url;
			}
		}
	}

	return {
		init: init,
		login: login,
		registration: registration,
		saveRegReturnUrl: saveRegReturnUrl,
		redirectToRegReturnUrl: redirectToRegReturnUrl
	};
}();