lib.module('mod.Common');

// Зависимости
lib.include('mod.Auth');

var Common = function() {
	function init() {
		// $('*[rel^="modal_"]').on('click', function() {
		// 	var popup = '#' + this.rel.split('_')[1];
		// 	$.modal.close();
		// 	$(popup).modal({
		// 		overlayClose: true
		// 	});
		// 	return false;
		// });
		Auth.init();
		Auth.login();
		Auth.registration();

		var page = $('body');

		page.on('click', '[data-toggle]', function(e) {
			_toggleDesc(e);
		})		
	}

	/**
	 *	Функция для фиксации элемента на странице.
	 *	После того, как скролл превысит первоначальное положение элемента ему будет присвоен класс -affix
	 *	@param obj - объект на странице, который нужно зафиксировать
	 */
	function affix(obj) {
		if (obj.size() == 0) {
			return;
		}
		var scroll = obj.offset().top,
		    body = $('body');
		$(window).on('scroll', function() {
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

	/**
	 *	Функция для разворачивания/сворачивания блока со скрытым контентом
	 *	@param toggler - объект, по клику на котором происходит действие
	 *	@param block - опционально. Селектор блока, который сворачивается/разворачивается
	 *	если селектор не указан, то будет раскрываться блок c классом toggler'a
	 *	Пример: toggler - .toggle_first-block, блок - .first-block
	 */
	function toggleBlock(toggler, block) {
		var toggler = $(toggler),
		    text = {
		    	o: toggler.text(),
		    	c: toggler.data('title')
		    },
		    block = block ? $(block) : $('.' + toggler[0].className.split(' ')[0].split('_')[1]) ,
		    max = block.hasClass('expanded') ? block.data('height') : Math.max.apply(Math, $('*', block).map(function(){ 
		    	return $(this).height(); 
		    }).get());
		toggler.animate({opacity: 0}, 100, function(){
			block.animate({height: max}, 'fast', function(){
				$(this).toggleClass('expanded');
				toggler.data('title', text.o).text(text.c);
				toggler.animate({opacity: 1}, 100);
			});
		});
	};

	/**
	 *	Функция для создания кнопки "Наверх" в левой части страницы
	 */
	function scrollTop() {
		$('body').append($('<div class="-up -gray -hidden"><i class="-icon-arrow-up"></i><span>Наверх</span></div>'));
		var win = $(window),
		    docH = $(document).height(),
		    winH = win.height(),
		    narrow = (win.width() > 1080) ? '' : '-narrow',
		    link  = $('.-up'),
		    tmp = $('.-layout-header'),
		    topPosition = (tmp.size() > 0) ? tmp.offset().top : 0;

		link.addClass(narrow);

		win.scroll(function() {
			winH = win.height();
			if (win.scrollTop() > topPosition) {
				if (link.hasClass('-hidden')) {
					link.removeClass('-hidden').fadeIn();
				}
			} 
			else {
				if (!link.hasClass('-hidden')) {
					link.fadeOut(100, function(){
						link.addClass('-hidden');
					});
				}
			}
		});

		win.resize(function() {
			if (win.width() > 1080) {
				link.removeClass('-narrow');
			}
			else {
				link.addClass('-narrow');
			}
		});

		link.on('click', function() {
			$("html:not(:animated), body:not(:animated)").animate({scrollTop: 0}, 200);
		});
	}

	/**
	 *	Функция прокрутки страницы до определенного элемента
	 *	@param hash - селектор элемента к которому осуществляется прокрутка
	 *	@param callback
	 */
	function scrollTo(hash, callback) {
		var elem = $(hash),
		    offset = elem.offset();
		if ($.browser.safari) {
			var bodyelem = $('body');
		}
		else {
			var bodyelem = $('html, body');
		}
		$(bodyelem).animate({scrollTop: offset.top - 40}, 500, callback);
	}

	/**
	 *	Автокомплиты
	 *	@param input - input, на который навешивается автокомплит (объект jQuery)
	 *	@param url - url для загрузки вариантов (строка)
	 *	@param clear - наличие иконки для очистки автокомплита (boolean)
	 *	@param submit - флаг для перезагруски страницы после выбора варианта (boolean)
	 */
	function customAutocomplete(input, url, clear, submit) {
		$('body').append('<div class="standart-autocomplete"></div>');

		var url = [
			{ label: 'Новосибирск', id: 1 },
			{ label: 'Москва', id: 2 },
			{ label: 'Красноярск', id: 3 },
			{ label: 'Черепаново', id: 4 },
			{ label: 'Пашино', id: 5 }
		];
		input.autocomplete({
			minLength: 2,
			delay: 100,
			source: url,
			appendTo: '.standart-autocomplete',
			position: {'offset': '0 -2'},
			select: function(event, ui) {
				$(this).prev().val(ui.item.id).change();
			}
		});
		if (clear == true) {
			input.after('<i class="-icon-cross-circle-xs -icon-only -red -absolute clear-autocomplete"></i>');
			input.next().on('click',function() {
				input.val('').prev().val('').change();
			})
		}
		if (submit == true) {
			input.prev().change(function() {
				input.parents('form').submit();
			});
		}
	}

	/**
	 *	Функция скрытия блока по клику на элементе внутри этого блока
	 *	@param block - селектор блока который надо скрыть. Если не указан, то
	 *	будет скрыт ближайший родитель event'a. Если у блока присутствует атрибут
	 *	data-type, то на сервер отправляется запрос, чтобы в дальнейшем этот блок
	 *	не показывался
	 *
	 */
	function hideBlock(block, event) {
		var e = $(event.currentTarget),
		    b = block ? $(block) : e.parent();
		b.fadeOut('fast', function() {
			// if (b.data('type')) {
			// 	$.ajax({
			// 		type: 'POST',
			// 		url: '/utility/closeStoreBlock',
			// 		async: true,
			// 		data: { type: b.data('type') },
			// 		dataType: 'json',
			// 		success: function(response){}
			// 	});
			// }
		});
	}

	/**
	 *	Функция разворачивания полного описания
	 */
	function _toggleDesc(event) {
		var t = $(event.currentTarget),
		    s = t.data('toggle'),
		    b = s ? $('.' + s): false ,
		    v = $('span:first', b),
		    h = $('span:last', b),
		    d = h.is(':visible') ? 'none' : 'inline',
		    o = h.is(':visible') ? 0 : 1 ;

		if (b != false) {
			v.toggleClass('visible');
			if (!h.is(':visible')) {
				h.css({display: d}).animate({opacity: o}, 'normal');
				_toggleText(t);
			}
			else {
				h.animate({opacity: o}, 'fast', function() {
					$(this).css({display: d});
					_toggleText(t);
				});
			}
		}
		return false;
	}

	function _toggleText(obj) {
		if (obj) {
			var text = obj.text();
			obj.text(obj.data('alt')).data('alt',text).attr('data-alt',text);
		}
	}

	return {
		init:init,
		affix:affix,
		toggleBlock:toggleBlock,
		scrollTop:scrollTop,
		scrollTo:scrollTo,
		customAutocomplete:customAutocomplete,
		hideBlock:hideBlock
	};
}();

$(function(){
	Common.init();
});