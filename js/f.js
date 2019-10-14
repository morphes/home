if (!$.support.opacity)
	$('html').addClass('oldies');
$(function () {
	var ios = (navigator.userAgent.match(/like Mac OS X/i)) ? true : false;
	if (ios) $('html').addClass('ios');

	var touchDevice = ('ontouchstart' in document.documentElement) ? true : false;
	if (touchDevice) $('html').addClass('touch');

	var oldies = (!$.support.opacity) ? true : false;

	// placeholders for inputs
	$('.textInput-placeholder').each(function () {
		$(this).focusin(
			function () {
				if ($(this).val() == $(this).attr('data-placeholder')) {
					$(this).val('').removeClass('textInput-placeholder');
				}
			}).focusout(function () {
				if ($(this).val() == '') {
					$(this).val($(this).attr('data-placeholder')).addClass('textInput-placeholder');
				}
			});
	});

	/* Исправил навешивание клика на Live */
	$('.user-photo .toggler').live('click', function () {
		$('.user-photo .user-photo-controls').slideToggle(100);
		return false;
	});

	/* Временно коментим согласие на правила
	 $('.fpa-agreed input[type="checkbox"]').click(function(){
	 if ($(this).is(':checked')) {
	 $('.form-project-add .fpa-submit button').removeAttr('disabled').removeAttr('title').removeClass('button2-disabled');
	 } else {
	 $('.form-project-add .fpa-submit button').attr('disabled','disabled').attr('title', 'Чтобы продолжить, ознакомтесь с пользовательским соглашением.').addClass('button2-disabled');
	 }
	 });*/

	if ($('.sortby').length) {
		$('.pathBar .sortby').click(function () {
			var filterItem = $("#filter [name='sortby']");
			var w = $(this);
			var list = w.find('ul');
			if (!list.is(':visible')) {
				list.slideDown(100);
				$(document).mousedown(function (e) {
					var target = $(e.target);
					if (target.hasClass('sortby') || target.parents('.sortby').length) {
					} else {
						w.find('span').trigger('click');
					}
				});
			} else {
				list.hide();
				$(document).unbind('mousedown');
			}
			;
			list.find('li').click(function () {
				var item = $(this);
				//$("#filter [name='filter']").val(0);
				filterItem.val(item.attr('data-rel'));
				$("#filter").submit();
//				list.find('.active').removeClass('active');
//				item.addClass('active');
//				w.find('span').text(item.text());
			});
		});
	}
	;
	var h = $(window).height();
	//alert(h);
	$(".wrapper-inside .content").css("min-height", h - 350 + "px");


	/*изменение типа помещения на странице добавления интерьера*/
	$('.fpa-space-edit').live('click', function () {
		var item = $(this).parents('.header');
		item.find('strong').hide();
		item.find('select').show();
		item.find('.fpa-space-edit').addClass('fpa-space-save').html('Сохранить');

		return false;
	});
	$('.fpa-space-save').live('click', function () {
		var item = $(this).parents('.header');
		item.find('strong').html(item.find('select option:selected').html()).show();
		item.find('select').hide();
		item.find('.fpa-space-save').html('Изменить').removeClass('fpa-space-save');
	});
	/*----изменение типа помещения*/

	/* События для показа подсказок в форме создания идей */
	$('.form-project-add .show_hint').live('mouseenter',
		function () {
			$(this).prev('p.hint').show();
		}).live('mouseleave', function () {
			$(this).prev('p.hint').hide();
		});

	$('.fpa-submit .fpa-later').click(function () {
		$('form#idea-making-form-step-2')
			.append('<input type="hidden" name="later" value="yes">')
			.submit();
	});
	/*----События для показа подсказок в форме создания идей */

	// Скрытие успешного сохранения
	$(".good-title").fadeOut(3000);


/*ставим фокус на textarea*/
	$('.leave-comment-link a').click(function () {
		$('.comments textarea').focus();
		return false;
	});
	/*----ставим фокус на textarea*/

	/*подсвечиваем рейтинг*/
	if ($('.rating-leave').length) {
		(function () {
			var r = $('.rating-leave');
			var s = r.find('i');
			s.mouseenter(
				function () {
					var index = s.index(this);
					s.filter(':lt(' + (index + 1) + ')').addClass('hovered');
					s.filter(':gt(' + (index) + ')').removeClass('hovered');
				}).click(function () {
					var index = s.index(this);
					s.filter(':lt(' + (index + 1) + ')').addClass('active');
					r.find('input').val(index + 1);
				});
			r.mouseenter(
				function () {
					s.removeClass('active');
				}).mouseleave(function () {
					if (!r.find('input').val() == '') {
						s.filter(':lt(' + (r.find('input').val()) + ')').addClass('active');
					}
					s.removeClass('hovered');
				});
		})();
	}
	;
	/*----подсвечиваем рейтинг*/

	/*вызов плеера фоток*/
	if ($('.shadow_block #player').length) {
		photoPlayer.init();

		if ($('.idea-listing').length) {
			$('.idea-listing a').click(function () {
				var a = $(this);
				if (!a.hasClass('active')) {
					$('.idea-listing .active').removeClass('active');
					a.addClass('active');
					var r = a.attr('data-rel') * 1;
					photoPlayer.load(r);
					$('.idea-features').hide();
					$('.idea-features-' + a.attr('data-rel')).show();
					if (r != 0) {
						$('.idea-authors').hide();
					} else {
						$('.idea-authors').show();
					};
				};
				return false;
			});
		};
	};

	/*if ($('.article_photo #player').length) {
		mediaPhotoPlayer.init();
	};*/
	/*----вызов плеера фоток*/


	/* Удаление проекта */
	$('.del_this_project a').click(function () {
		var item = $(this).parents('.item');
		var a = $(this);
		doAction({
			'yes':function(){
				$.get(
					a.attr("data-link"),
					function (response) {
						if (response.success) {
							item.animate({width:0}, 200, function () {
								item.remove();
							});
						}
					},
					'json'
				);

			},
			no: function(){

			}
		},'Удаление проекта', 'Вы действительно хотите удалить проект?');

		return false;
	});

	/* ----Удаление проекта */

	/*Изменение стиля ссылки "изменить фото" аватарки пользователя */
	$('.file_load').live('mouseover', function () {
		$('a.pseudo-change').addClass('pseudo-file');
	});
	$('.file_load').live('mouseout', function () {
		$('a.pseudo-change').removeClass('pseudo-file');
	});

	/*---- изменение стиля*/

	if ($('.registration').length) {
		$('.registration .switcher a').click(function () {
			var a = $(this);
			if (!a.parent().hasClass('active')) {
				$('.registration .switcher .active').removeClass('active');
				a.parent().addClass('active');
				if (a.attr('data-rel') == 'user') {
					$('.form-user').show();
					$('.form-designer').hide();
				} else {
					$('.form-designer').show();
					$('.form-user').hide();
				}
			}
			;
			return false;
		});
		/* Внес небольшие изменения. */
		$('.form-designer .fd-person .selectInput').change(function () {
			var val = $(this).val();
			if (val == 'Designer') {
				$('.form-designer').removeClass('form-designer-jur').addClass('form-designer-phys');
				$('.fd-company label').html('Имя <span class="required">*</span>').next('span').hide();
			} else {
				$('.form-designer').removeClass('form-designer-phys').addClass('form-designer-jur');
				$('.fd-company label').html('Название компании <span class="required">*</span>').next('span').show();
			}
			;
		});

	}
	;

	/*вакансии*/
	if ($('.vacancies-list').length) {
		(function () {
			$('.vacancies-list h2 a').click(function () {
				$(this).parents('.item').trigger('click');
				return false;
			});
			$('.vacancies-list .item').click(function () {
				var a = $(this);

				if (!a.hasClass('active')) {
					$('.vacancies-list .active').removeClass('active');
					a.addClass('active');
					setHash(a.attr('id').replace('vacancy-', ''));
				} else {
					a.removeClass('active');
					setHash('0');
				}
			});
			if (getHash()) {
				var item = $('#vacancy-' + getHash());
				if (item.length) {
					item.trigger('click');
					$(document).scrollTop(item.offset().top - 12);
				}
			}
		})();
	}
	/*----вакансии*/

	if ($('.accordion-list').length) {
		(function () {
			$('.accordion-list h2 a').click(function () {
				$(this).parents('.item').trigger('click');
				return false;
			});
			$('.accordion-list .item').click(function (e) {
				var a = $(this);
				var href = typeof(e.target.href);
				if (href != "undefined") {
				} else {
					if (!a.hasClass('active')) {
						$('.accordion-list .active').removeClass('active');
						a.addClass('active');


						var destination = a.offset().top - 12;
						$("html:not(:animated),body:not(:animated)").animate({scrollTop:destination}, 500)
						setHash(a.attr('id').replace('id-', ''));
					} else {
						a.removeClass('active');
						setHash('0');
					}
				}
			});
			if (getHash()) {
				var item = $('#id-' + getHash());
				if (item.length) {
					item.trigger('click');
					$(document).scrollTop(item.offset().top - 12);
				}
			}
		})();
	}

	$('.c-hinter').hinter();

	$('.project-docs a.handler').live('click', function () {
		if (!$(this).parent("div").hasClass('pd-active')) {
			$(this).parent("div").addClass('pd-active');
			$(document).mousedown(md);
		} else {
			$(document).unbind('mousedown', md);
			$(this).parent("div").removeClass('pd-active');
		}
		;
		return false;
	});
	function md(e) {
		var target = $(e.target);
		if ($(e.target).closest(".pd-active").length) {
		} else {
			if ($('.pd-certificate').hasClass("pd-active")) {
				$('.pd-certificate').removeClass('pd-active');
			}
			if ($('.pd-history').hasClass("pd-active")) {
				$('.pd-history').removeClass('pd-active');
			}
		}
		;
	}

	/*----фильтр городов*/


	$('.gallery-210 .item').live('mouseenter',
		function () {
			$(this).find('.autor_functions').stop().animate({
				opacity:1
			}, 200);
		}).mouseleave(function () {
			$(this).find('.autor_functions').stop().animate({
				opacity:0.5
			}, 200);
		});

	$('.show_big').live('click', function () {
		var id = this.id;
		id = id.split('_')[1];
		$('.photos_list a#ph_' + id).trigger('click');
	})
});
/*----$(document).ready();*/


/*фотоплеер*/
var mediaPhotoPlayer = function () {

	var p, slideshow, items, list, photos, next, nav, hide, counter, photo, info,text, inner, isRolling, author,
		current = 0,
		qnt = 0;

	this.init = function(n) {

		// Добавил.
		// При инициализации надо загружать всегда первую фотку.
		current = 0;
		p = $('#'+n);
		author = (p.attr('data-author')) ? true : false;
		items = p.find('a');
		list = items;

		create();
	}

	;

	this.loadGallery = function(n) {
		current = 0;
		qnt = 0;
		clearTimeout(slideshow);

		if (n) {
			list = items.filter('.player-gallery-' + n);
			create();
		} else {
			list = items;
			create();
		}
		;
	}

	;

	function create() {
		var pContainer = p.parents('.article_photo');
		var isArticle = pContainer.is('div');
		if(isArticle){
			var plInfo = '<div class="player-info">'+
				'		<div class="image_desc"><span></span></div>' +
				'		<div class="image_quant">' +
				'			<span class="image_quant_cur"></span> из ' +
				'			<span class="image_quant_all"></span>' +
				'		</div><div class="clear"></div>' +
				'	</div>';
		}else{
			var plInfo = '<div class="player-info"><a class="show_proj_desc" href="#">Раскрыть описание фотографии</a><i class=""></i><span></span></div>';
		}

		p.empty().append('<div class="player-container"></s><div class="player-photo"></div>' +
			'	<div class="player-nav player-prev"><i></i></div>' +
			'	<div class="player-nav player-next"><i></i></div>' +
			((author) ? '<div class="player-copy"><a href="#" class="player-copy-handler" target="_blank">Найти дубликаты изображения в интернете</a><div class="c-hinter"><i></i><p class="c-hinter-text">С помощью этого сервиса вы можете проверить, не используются ли изображения ваших проектов на сторонних ресурсах.</p></div></div>' : '') +
			plInfo+
			'</div>'+
			'	<div class="player-photos">' +
			'		<div class="player-photos-wrapper"><div class="player-photos-inner"></div></div>' +
			'	</div>' +
			'<p class="photo_detail_desc"></p>');
		p.find('.player-photos-inner').html(list);
		list = p.find('.player-photos');

		photos = new Array(); // photos array [url,title,loading indicator,gallery]

		var ph = "";
		list.find('a').each(function (i) {
			var a = $(this);
			if (i == 0) $(this).addClass('active');
			qnt = i + 1;
			photos[i] = {
				url:a.attr('data-preview'),
				title:a.attr('title'),
				loaded:false,
				copy:(author) ? a.attr('data-copy') : false,
				origin:a.attr('href'),
				descript:a.attr('data-descript')
			};
			ph += "<a id='ph_" + i + "' rel='example_group' href='" + a.attr('href') + "'></a>";
		});

		p.find('.photos_list').remove();
		$('<div class="photos_list" style="display:none">' + ph + '</div>').insertAfter('#right_side');
		list.find('a:eq(0)').addClass('active');

		photo = p.find('.player-photo');
		info = p.find('.player-info');
		text = p.find('.photo_detail_desc');

		if (qnt > 1) {
			var currentRoll = 0;
			var size = 9; // preview counter

			nav = p.find('.player-nav');
			nav.click(function () {
				clearTimeout(slideshow);
				p.find('.player-slideshow i').removeClass('active');

				var next = ($(this).hasClass('player-prev')) ? current - 1 : current + 1;
				next = (next < 0) ? qnt - 1 : ( (next > (qnt - 1)) ? 0 : next );

				if (qnt > size) {

					if (!isRolling && !$(this).hasClass('disabled')) {
						if (next < currentRoll || next > (currentRoll + size - 1)) {
							var val;
							if (next > current) {

								val = (next - size + 1) < 0 ? 0 : (next - size + 1);
								if($('.show_all_photos a').hasClass('closet')){
									inner.animate({
										marginLeft:-val * 64 + 'px'
									}, 200, function () {
										isRolling = false;
									});
								}

								currentRoll = val;
								checkPNav();
							} else if (next < current) {
								val = next > (qnt - size + 1) ? qnt - size + 1 : next;
								if($('.show_all_photos a').hasClass('closet')){
									inner.animate({
										marginLeft:-(val) * 64 + 'px'
									}, 200, function () {
										isRolling = false;
									});
								}
								currentRoll = val;
								checkPNav();
							}
						}

					}
				}

				load(next);
			});

			list.find('a').click(function () {
				if (!$(this).hasClass('active')) {
					var index = list.find('a').index(this);
					load(index);

					if (qnt > 16) {
						p.find('.player-list').trigger('click');
					}
				}

				return false;
			});

			if (qnt > size) {
				p.find('.player-photos-wrapper').addClass('player-photos-wrapper-nav');
				inner = p.find('.player-photos-inner');
				list.append('<div class="pp-prev disabled"><i></i></div><div class="pp-next"><i></i></div>');
				inner.width(qnt * 64);

				if(photo.parents().hasClass('article_photo')){
					list.append('<div class="show_all_photos">' +
							'<a href="#" class="closet">Показать все фото</a>' +
							'<span>&darr;</span>' +
						'</div>')
				}

				list.find('.pp-prev').click(function () {
					if (!isRolling && !$(this).hasClass('disabled')) {
						var val = currentRoll >= 4 ? 4 : currentRoll;
						isRolling = true;
						inner.animate({
							marginLeft:'+=' + (val * 64) + 'px'
						}, 200, function () {
							isRolling = false;
						});
						currentRoll -= val;
						checkPNav();
					}
				});
				list.find('.pp-next').click(function () {
					if (!isRolling && !$(this).hasClass('disabled')) {
						var val = (qnt - currentRoll - size - 1) > 4 ? 4 : qnt - currentRoll - size;
						isRolling = true;
						inner.animate({
							marginLeft:'-=' + (val * 64) + 'px'
						}, 200, function () {
							isRolling = false;
						});
						currentRoll += val;
						checkPNav();
					}
				});
				var wrapHeight = Math.ceil(qnt/11)*69;
				var showAll = list.find('.show_all_photos a');
				showAll.click(function () {
					if($(this).is('.closet')){
						inner.width(704);
						inner.animate({'margin-left':0});
						p.find('.player-photos-wrapper').animate({margin:0},200,function(){
							$(this).css({'z-index':10});
							$(this).animate({height:wrapHeight},200,function(){
								showAll.toggleClass('closet').next().html('&uarr;');
							});

						});
						currentRoll = 0;
						checkPNav();
					}else{

						checkPNav();
						p.find('.player-photos-wrapper').animate({height:64},200,function(){
							$(this).css({'z-index':0});
							$(this).animate({"margin-right":'62px',"margin-left":'62px'},200,function(){
								showAll.toggleClass('closet').next().html('&darr;');

								inner.width(qnt * 64);
							});
						});
					}

					return false;
				});
				function checkPNav() {
					if (currentRoll <= 0) {
						list.find('.pp-prev').addClass('disabled');
						list.find('.pp-next').removeClass('disabled');
					} else if (currentRoll >= qnt - size) {
						list.find('.pp-prev').removeClass('disabled');
						list.find('.pp-next').addClass('disabled');
					} else {
						list.find('.pp-prev').removeClass('disabled');
						list.find('.pp-next').removeClass('disabled');
					}
				}
			}

		} else {
			p.find('.player-photos').remove();
			p.find('.player-nav').remove();
			p.find('.player-info').addClass('no_preview');
		}

		var pr_head = $('.project_head').clone();
		pr_head.find('.favorite_conteiner').remove();

		pr_head = '<div class="project_head">'+pr_head.html()+'</div>';
		/*
		$("a[rel='example_group']").fancybox({
			'overlayColor':'#000',
			'overlayOpacity':0.9,
			'titleShow':true,
			'title':pr_head,
			'padding':0
		});*/

		load(current);
	}


	function load(index) {
		current = index;
		preload(current);

		var prevWrapper = photo.find('div:not(:last)');
		setTimeout((function () {
			prevWrapper.remove();
		}), 100);

		var wrapper = $('<div><span></span></div>').css({opacity:0});
		photo.append(wrapper);

		if (photos[index].loaded) {
			wrapper
				.find('span').append('<img class="" id="img_' + index + '" src="' + photos[index].url + '" alt="">').end()
				.animate({opacity:1}, 300);
		} else {
			wrapper.animate({opacity:1}, 300);
			var img = $(new Image())
				.load(function () {
					if (index == current) {
						img
							.css({opacity:0})
							.appendTo(wrapper.find('span'))
							.animate({opacity:1}, 300);
					}
				})
				.attr({src:photos[index].url, id:"img_" + index});
		}
		;
		if (photos[index].title) {
			info.find('.image_desc span').text(photos[index].title);
			//info.show();
		} else {
			info.find('.image_desc span').html('');
			//info.hide();
		}

		if(photos[index].descript){
			text.text(photos[index].descript);
		}else{
			text.text('');
		}
		info.find('.image_quant_cur').text(current+1);
		info.find('.image_quant_all').text(qnt);

		list.find('.active').removeClass('active');
		list.find('a:eq(' + current + ')').addClass('active');
		p.find('.player-copy-handler').attr('href', photos[index].copy);

		p.find('.player-container').hover(function(){
			info.stop().animate({opacity:1},150);
		},
		function(){
			info.stop().animate({opacity:0},150);
		});

	}

	;

	function getNext() {
		return ((current + 1) > (qnt - 1)) ? 0 : (current + 1);
	}

	;

	function preload(index) {
		var start = index - 1;
		preloadImage(index);

		if (qnt > 3) {
			for (var i = start; i < (start + 3); i++) {
				if (i != index) {
					preloadImage((i < 0) ? qnt + i : ( (i > (qnt - 1)) ? i - qnt : i ));
				}
			}
			;
		} else {
			for (var i = 0; i < qnt; i++) {
				if (i != index) {
					preloadImage(i);
				}
			}
		}

		function preloadImage(i) {
			if (photos[i].loaded == false) {
				$(new Image()).load(
					function () {
						photos[i].loaded = true;
					}).attr('src', photos[i].url);
			}
		}

	}




};

/*----плеер*/
/*временная мера !!!!*/
var photoPlayer = (function () {

	var p, slideshow, items, list, photos, next, nav, hide, counter, photo, info, inner, isRolling, author,
		current = 0,
		qnt = 0;

	function init(n) {
		// ааОаБаАаВаИаЛ.
		// абаИ аИаНаИбаИаАаЛаИаЗаАбаИаИ аНаАаДаО аЗаАаГббаЖаАбб аВбаЕаГаДаА аПаЕбаВбб баОбаКб.
		current = 0;
		p = $('#player');
		author = (p.attr('data-author')) ? true : false;
		items = p.find('a');
		list = items;

		create();
	}

	;

	function loadGallery(n) {
		current = 0;
		qnt = 0;
		clearTimeout(slideshow);

		if (n) {
			list = items.filter('.player-gallery-' + n);
			create();
		} else {
			list = items;
			create();
		}
		;
	}

	;

	function create() {
		p.empty().append('<div class="player-photo"></div>' +
			'	<div class="player-nav player-prev"><i></i></div>' +
			'	<div class="player-nav player-next"><i></i></div>' +
			((author) ? '<div class="player-copy"><a href="#" class="player-copy-handler" target="_blank">Найти дубликаты изображения в интернете</a><div class="c-hinter"><i></i><p class="c-hinter-text">С помощью этого сервиса вы можете проверить, не используются ли изображения ваших проектов на сторонних ресурсах.</p></div></div>' : '') +
			'	<div class="player-photos">' +
			'		<div class="player-photos-wrapper"><div class="player-photos-inner"></div></div>' +
			'	</div>' +
			'	<div class="player-info"><a class="show_proj_desc" href="#">Раскрыть описание фотографии</a><i class=""></i><span></span></div>');
		p.find('.player-photos-inner').html(list);
		list = p.find('.player-photos');

		photos = new Array(); // photos array [url,title,loading indicator,gallery]

		var ph = "";
		list.find('a').each(function (i) {
			var a = $(this);
			if (i == 0) $(this).addClass('active');
			qnt = i + 1;
			photos[i] = {
				url:a.attr('data-preview'),
				title:a.attr('title'),
				loaded:false,
				copy:(author) ? a.attr('data-copy') : false,
				origin:a.attr('href')
			};
			ph += "<a id='ph_" + i + "' rel='example_group' href='" + a.attr('href') + "'></a>";
		});

		$('.photos_list').remove();
		$('<div class="photos_list" style="display:none">' + ph + '</div>').insertAfter('#right_side');
		list.find('a:eq(0)').addClass('active');

		photo = p.find('.player-photo');
		info = p.find('.player-info');

		if (qnt > 1) {
			var currentRoll = 0;
			var size = 12; // preview counter

			nav = p.find('.player-nav');
			nav.click(function () {
				clearTimeout(slideshow);
				p.find('.player-slideshow i').removeClass('active');

				var next = ($(this).hasClass('player-prev')) ? current - 1 : current + 1;
				next = (next < 0) ? qnt - 1 : ( (next > (qnt - 1)) ? 0 : next );

				if (qnt > 14) {

					if (!isRolling && !$(this).hasClass('disabled')) {
						if (next < currentRoll || next > (currentRoll + size - 1)) {
							var val;
							if (next > current) {
								val = (next - size + 1) < 0 ? 0 : (next - size + 1);
								inner.animate({
									marginLeft:-val * 50 + 'px'
								}, 200, function () {
									isRolling = false;
								});
								currentRoll = val;
								checkPNav();
							} else if (next < current) {
								val = next > (qnt - size + 1) ? qnt - size + 1 : next;
								inner.animate({
									marginLeft:-(val) * 50 + 'px'
								}, 200, function () {
									isRolling = false;
								});
								currentRoll = val;
								checkPNav();
							}
						}

					}
				}

				load(next);
			});

			list.find('a').click(function () {
				if (!$(this).hasClass('active')) {
					var index = list.find('a').index(this);
					load(index);

					if (qnt > 16) {
						p.find('.player-list').trigger('click');
					}
				}

				return false;
			});

			if (qnt > 14) {
				p.find('.player-photos-wrapper').addClass('player-photos-wrapper-nav');
				inner = p.find('.player-photos-inner');
				list.append('<div class="pp-prev disabled"><i></i></div><div class="pp-next"><i></i></div>');
				inner.width(qnt * 50);

				list.find('.pp-prev').click(function () {
					if (!isRolling && !$(this).hasClass('disabled')) {
						var val = currentRoll >= 4 ? 4 : currentRoll;
						isRolling = true;
						inner.animate({
							marginLeft:'+=' + (val * 50) + 'px'
						}, 200, function () {
							isRolling = false;
						});
						currentRoll -= val;
						checkPNav();
					}
				});
				list.find('.pp-next').click(function () {
					if (!isRolling && !$(this).hasClass('disabled')) {
						var val = (qnt - currentRoll - size - 1) > 4 ? 4 : qnt - currentRoll - size;
						isRolling = true;
						inner.animate({
							marginLeft:'-=' + (val * 50) + 'px'
						}, 200, function () {
							isRolling = false;
						});
						currentRoll += val;
						checkPNav();
					}
				});
				function checkPNav() {
					if (currentRoll <= 0) {
						list.find('.pp-prev').addClass('disabled');
						list.find('.pp-next').removeClass('disabled');
					} else if (currentRoll >= qnt - size) {
						list.find('.pp-prev').removeClass('disabled');
						list.find('.pp-next').addClass('disabled');
					} else {
						list.find('.pp-prev').removeClass('disabled');
						list.find('.pp-next').removeClass('disabled');
					}
				}
			}

		} else {
			p.find('.player-photos').remove();
			p.find('.player-nav').remove();
			p.find('.player-info').addClass('no_preview');
		}

		var pr_head = $('.project_head').clone();
		pr_head.find('.favorite_conteiner').remove();

		pr_head = '<div class="project_head">'+pr_head.html()+'</div>';

		$("a[rel='example_group']").fancybox({
			'overlayColor':'#000',
			'overlayOpacity':0.9,
			'titleShow':true,
			'title':pr_head,
			'padding':0
		});

		load(current);
	}


	function load(index) {
		current = index;
		preload(current);

		var prevWrapper = photo.find('div:not(:last)');
		setTimeout((function () {
			prevWrapper.remove();
		}), 100);

		var wrapper = $('<div><span></span></div>').css({opacity:0});
		photo.append(wrapper);

		if (photos[index].loaded) {
			wrapper
				.find('span').append('<img class="show_big" id="img_' + index + '" src="' + photos[index].url + '" alt="">').end()
				.animate({opacity:1}, 300);
		} else {
			wrapper.animate({opacity:1}, 300);
			var img = $(new Image())
				.load(function () {
					if (index == current) {
						img
							.css({opacity:0})
							.appendTo(wrapper.find('span'))
							.animate({opacity:1}, 300);
					}
				})
				.attr({src:photos[index].url, id:"img_" + index, "class":"show_big"});
		}
		if (photos[index].title) {
			info.find('span').text(photos[index].title);
			info.show();
		} else {
			//info.html('');
			info.hide();
		}
		list.find('.active').removeClass('active');
		list.find('a:eq(' + current + ')').addClass('active');
		p.find('.player-copy-handler').attr('href', photos[index].copy);

	}

	function getNext() {
		return ((current + 1) > (qnt - 1)) ? 0 : (current + 1);
	}


	function preload(index) {
		var start = index - 1;
		preloadImage(index);

		if (qnt > 3) {
			for (var i = start; i < (start + 3); i++) {
				if (i != index) {
					preloadImage((i < 0) ? qnt + i : ( (i > (qnt - 1)) ? i - qnt : i ));
				}
			}
		} else {
			for (var i = 0; i < qnt; i++) {
				if (i != index) {
					preloadImage(i);
				}
			}
		}

		function preloadImage(i) {
			if (photos[i].loaded == false) {
				$(new Image()).load(
					function () {
						photos[i].loaded = true;
					}).attr('src', photos[i].url);
			}
		}
	}

	return {
		init:init,
		load:loadGallery
	};

})();
/*подсказки в профиле*/
$.fn.hinter = function () {
	$(this).each(function () {
		var p = $(this),
			handler = p.find('i'),
			index = (p.offset().top * p.offset().left).toString();

		p.attr('data-index', index);
		handler.click(function () {
			if (!p.hasClass('c-hinter-active')) {
				p.addClass('c-hinter-active');
				$(document).mousedown(md);
			} else {
				$(document).unbind('mousedown', md);
				p.removeClass('c-hinter-active');
			}
			
		});
		function md(e) {
			var target = $(e.target);
			if (target.attr('data-index') == index || target.parents('.c-hinter').attr('data-index') == index) {
			} else {
				handler.trigger('click');
			}
			
		}

		
	});
}
/**/

$.fn.list = function () {
	$(this).each(function (i) {

		var list = $(this),
			inner = list.find('.list-inner');

		list.find('.bar-wrapper').remove();

		if (inner.height() > list.height()) {
			$('<div class="bar-wrapper"><b></b><div class="bar"></div></div>').appendTo(list);
			var bar = list.find('.bar'),
				min = inner.height() - list.height(),
				max = list.height() - 150;
			ratio = min / max;
			bar.css('marginTop', parseInt(inner.css('marginTop')) / -ratio);

			bar.unbind('mousedown').mousedown(function (e) {
				var y = e.pageY;
				var pos = Math.abs(parseInt(inner.css('marginTop'))) / ratio;
				$(document).mousemove(function (e) {
					var dif = e.pageY - y;
					dif = (dif + pos);
					dif = (dif < 0) ? 0 : ( (dif > max) ? max : dif );
					bar.css('marginTop', dif);
					inner.css('marginTop', -dif * ratio);
				});
				$(document).mouseup(function (e) {
					$(document).unbind('mousemove');
					$(document).unbind('mouseup');
				});
			});
			list.unbind('mousewheel').mousewheel(function (e, delta) {

				var ev = e.originalEvent;
				if (!ev)
					ev = window.event;
				if (ev.wheelDelta)
					delta = ev.wheelDelta / 120;
				else
					delta = ev.detail / -3;

				var dir = -60 / ratio * delta,
					pos = Math.abs(parseInt(inner.css('marginTop'))) / ratio,
					dif = (dir + pos);

				dif = (dif < 0) ? 0 : ( (dif > max) ? max : dif );
				bar.css('marginTop', dif);
				inner.css('marginTop', -dif * ratio);
				return false;
			});
		}

	});
};

function getHash() {
	var www = window.location.toString();
	var hash = null;
	if (www.indexOf('#') >= 0) {
		hash = www.substring(www.indexOf('#') + 1);
	}
	return hash;
}

function setHash(hash) {
	var www = window.location.toString();
	if (www.indexOf('#') >= 0) {
		www = www.substring(0, www.indexOf('#'));
	}
	window.location.replace(www + '#' + hash);
}

/**
 * Замена стандартного confirm.
 * Пример использования
 */
doAction = function (action, title, description) {
	var body = $('body');

	if (title === undefined) title = 'Выполнить операцию?';

	if (description !== undefined)
		description =  '<p><strong>' + description + '</strong></p>';
	else
		description = '<br>';

	body.append('<div class="popup popup-confirm" id="popup-confirm">' +
		'	<div class="popup-header"><div class="popup-header-wrapper">' + title + '</span></div></div>' +
		'	<div class="popup-body">' + description +
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
};
/*----Замена стандартного confirm.*/


/* Copyright (c) 2010 Brandon Aaron (http://brandonaaron.net)
 * Licensed under the MIT License (LICENSE.txt).
 *
 * Thanks to: http://adomas.org/javascript-mouse-wheel/ for some pointers.
 * Thanks to: Mathias Bank(http://www.mathias-bank.de) for a scope bug fix.
 * Thanks to: Seamus Leahy for adding deltaX and deltaY
 *
 * Version: 3.0.4
 *
 * Requires: 1.2.2+
 */
(function (c) {
	var a = ["DOMMouseScroll", "mousewheel"];
	c.event.special.mousewheel = {setup:function () {
		if (this.addEventListener) {
			for (var d = a.length; d;) {
				this.addEventListener(a[--d], b, false)
			}
		} else {
			this.onmousewheel = b
		}
	}, teardown:function () {
		if (this.removeEventListener) {
			for (var d = a.length; d;) {
				this.removeEventListener(a[--d], b, false)
			}
		} else {
			this.onmousewheel = null
		}
	}};
	c.fn.extend({mousewheel:function (d) {
		return d ? this.bind("mousewheel", d) : this.trigger("mousewheel")
	}, unmousewheel:function (d) {
		return this.unbind("mousewheel", d)
	}});
	function b(i) {
		var g = i || window.event, f = [].slice.call(arguments, 1), j = 0, h = true, e = 0, d = 0;
		i = c.event.fix(g);
		i.type = "mousewheel";
		if (i.wheelDelta) {
			j = i.wheelDelta / 120;
		}
		if (i.detail) {
			j = -i.detail / 3;
		}
		d = j;
		if (g.axis !== undefined && g.axis === g.HORIZONTAL_AXIS) {
			d = 0;
			e = -1 * j
		}
		if (g.wheelDeltaY !== undefined) {
			d = g.wheelDeltaY / 120;
		}
		if (g.wheelDeltaX !== undefined) {
			e = -1 * g.wheelDeltaX / 120;
		}
		f.unshift(i, j, e, d);
		return c.event.handle.apply(this, f);
	}
})(jQuery);



var index = new CIndex();

function CIndex(){
	this.showPopular = function(obj){
		$('.popular_services span').click(function(){
			var parent = $(this).parent();
			if(parent.hasClass('opened')){
				parent.removeClass('opened');
				parent.find('ul').slideUp(100);
			}else{
				parent.addClass('opened');
				parent.find('ul').slideDown(100,'swing');
			}
		});
		$(document).click(function(e){
			var match = $(e.target).closest(".popular_services");
			if (!match.length){
				$('.popular_services').removeClass('opened');
				$('.popular_services').find('ul').slideUp(100);
			}
		});
	};
}
