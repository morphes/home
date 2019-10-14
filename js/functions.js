if (!$.support.opacity) $('html').addClass('oldies');
$(function(){

	if ($('.my_th').length) {
		var i = -1;
		$('h5.myservices').each(function(){
			var chCnt = 0;
			i++;
			var tbl = $(this).next();
			tbl.find('.servise_check').each(function(){
				if($(this).is(':checked'))
					chCnt++;
				i++;
			});
			if(chCnt){
				tbl.show();
			}
			if((i-chCnt)==0){
				$(this).find('input').prop('checked',true)
			}

			$(this).find('.act_service_count').text('Выбрано '+formatNumeral(chCnt)).show();
		});

		$('.all_servise_check').click(function(){
			var index=this.id.split('_')[1];

			if($(this).is(':checked')){
				$('#list_'+index+' .servise_check').prop('checked', true);
				$('#list_'+index+' tr').addClass('active');
				if($('#list_'+index).parent('div').is(':hidden')){
					$('#list_'+index).parent('div').slideDown();
					$(this).parent().children('.list_status').toggleClass("open");
					$(this).parent().children('.act_service_count').hide();
				}
			}else{
				$('#list_'+index+' .servise_check').prop('checked', false);
				$('#list_'+index+' tr').removeClass('active');
			}
		});
		$('.servise_check').click(function(){
			var index = $(this).parents('table').attr('id');
			index = index.split('_')[1];
			if($(this).is(':checked')){
				$(this).parents('tr').addClass('active');
				var i = 0;
				$('#list_'+index+' .servise_check').each(function(){
					if($(this).is(':checked')){
					}else{
						i++;
					}
				});
				if(i==0){
					$('#check_'+index).prop('checked', true);
				}
			}else{
				$(this).parents('tr').removeClass('active');

				$('#check_'+index).prop('checked', false);
			}
		});

		$('.my_serv_list').each(function(){
			$(this).find('tr').each(function(){
				$(this).find('td.required_field').each(function(){
					if($(this).find('span').text() == "Не указан"){
						$(this).find('span').addClass('required_notice');

					}
				})

			})
		})
	}
	/*------отмечаем чекбоксы на странице редактирования услуг*/

	/*выбор стажа*/

	$(".exp_current:not(.disabled)").live('click',function(){

		var flag =$(this).next().is(':visible');
		if(flag){
			$(this).next().hide();
		}else{
			$('.drop_down ul:visible').hide();
			$(this).next().show();
		}

	});

	$('.drop_down ul li').live('click',function(){
		var ul = $(this).parent();
		var favorite_list = $(this).parents('.favorite_list').find('input');

		if(!ul.parent().hasClass('room_selector')){
			ul.prev('span').html($(this).text()+'<i></i>');
			ul.find('li').removeClass('active');
			$(this).addClass('active');
		}

		ul.prev('span.required_notice').removeClass('required_notice');

		$(this).parents('td').children('input').val($(this).attr('data-value'))
		$('.drop_down ul').hide();



		if($(this).hasClass('new_fav_list')){
			favorite_list.removeClass('hide');
			favorite_list.addClass('show');
		}else{
			favorite_list.addClass('hide');
			favorite_list.removeClass('show');
		}

		// Если есть класс set_input, то следующему input вставляем выбранное значение
		if (ul.hasClass('set_input')) {
			var val = $(this).attr('data-value');
			ul.next('input').val(val);
		}

		// Сабмитим форму, в которой находится наш ul, если есть класс need_submit
		if (ul.hasClass('need_submit')) {
			ul.parents('form').submit();
		}

		// Если указано имя функции, то выполняем ее после выбора значения.
		if (ul.attr('data-callback')) {
			var callbackFunction = window[ ul.attr('data-callback') ];
			// Передаем в него элемент LI, по которому кликнули
			callbackFunction($(this));
		}

	});
	$(document).click(function(e){
		var match = $(e.target).closest(".drop_down .exp_current");
		if (!match.length){
			$('.drop_down ul:visible').hide();
		}

		if($('.favorite_conteiner').length){
			var favorite = $(e.target).closest(".favorite_conteiner");
			if (!favorite.length){
				$('.favorite_list:visible').hide();
				$('.add_to_favorite, .favorite_icon').removeClass('clicked');
				$('.new_fav_input').addClass('hide');
				$('.new_fav_input').removeClass('show');
			}
		}

		if($('.add_this_to_favorite').length){
			var favorite_item = $(e.target).closest(".add_this_to_favorite");
			if (!favorite_item.length){
				$('.favorite_list:visible .drop_down ul li:first').trigger('click');
				$('.favorite_list:visible').hide();
				$('.add_to_favorite, .favorite_icon,.favorite_button').removeClass('clicked');
				$('.new_fav_input.show').removeClass('show');
				$('.new_fav_input').addClass('hide');
			}
		}

		if($('.tender_list').length){
			var tenders_list = $(e.target).closest(".respond");
			if (!tenders_list.length){
				$('.tender_respond:visible').hide();
				$('.respond input:checked:not(.added)').prop('checked',false).removeClass('checked');
			}
		}


	});
	/*----выбор стажа*/

	$(".myservices a,.myservices .list_status").click(function(){
		var index = $(this).parent().children('.all_servise_check').attr('id');
		index = index.split('_')[1];
		if($(this).parent().next("div").is(':visible')){
			var count = 0;
			$('#list_'+index+' .servise_check').each(function(){
				if(!$(this).is(':checked')){
				}else{
					count++;
				}
			});
			$(this).parent().children('.act_service_count').text('Выбрано '+ formatNumeral(count)).toggle();
		}else{
			$(this).parent().children('.act_service_count').hide();
		}
        $(this).parent().next("div").slideToggle();
        $(this).parent().children('.list_status').toggleClass("open");
		return false;
    });


	$('#file_input').change(function(){
		$('#file_input_text').val($(this).val());
		$('.upload_price .btn_grey').removeAttr('disabled');
		$('.upload_price .btn_conteiner').removeClass('disabled');
	})
	$('.not_uploaded').click(function(){
		$('.upload_links').hide();
		$('#price_form').show();
		return false;
	});



	if($('div').is('.btn_project')){
		$(document).click(function(e){
			var match = $(e.target).closest(".servise_choice,.btn_project");
			if (!match.length){
				$('.servise_choice_list').addClass('hide');
			}
		});
	}

	/*выпадающий список для добавления проекта*/
	$('.service_choice:not(.disabled)').on('click',function(){
		if($(this).hasClass('empty_service_list'))
			$('#popup-message-guest').modal({
				overlayClose:true
			});
		else
			$('.servise_choice_list').removeClass('hide');
		return false;
	});





	/*редактирование профиля*/
	$('.avatar_input').change(function(){
		$('.avatar_input_text').val($(this).val());
	});

	/*----редактирование профиля*/


	/*  открыть/закррыть описание фотографии в плеере фоток*/
	$('.player-info i:not(.close), .show_proj_desc').live('click',function(){
		$('.show_proj_desc').hide();
		$('.player-info i').addClass('close');
		$('.player-info span').fadeIn(250);
		return false;
	});
	$('.player-info i.close').live('click',function(){
		$('.show_proj_desc').show();
		$('.player-info i').removeClass('close');
		$('.player-info span').hide();
		return false;
	});


	$('.gallery_button').hover(function(){
		$(this).stop().animate({opacity:1},200);
	},
	function(){
		$(this).stop().animate({opacity:0.5},200);
	});


	/*скрываем список элементов избранного*/
	$('.hide_items').click(function(){
		if($(this).is('.showed')){
			$(this).removeClass('showed');
			$(this).next().slideDown();
			$(this).find('a').text('Свернуть');
		}else{
			$(this).addClass('showed');
			$(this).next().slideUp();
			$(this).find('a').text('Развернуть');
		}
		return false;
	});


	if($('.competition_background').length){
		var bg = $('.competition_background').clone();
		var offset = $('.competition_rules a').offset();

		$('.competition_background').remove();
		bg.insertAfter('#header');
		$('.competition_background').width($(document).width());
		$('.content').css({'background':'transparent'});
		$('.competition_rules a').click(function(event,trigger){
			var ul = $(this).next();
			if(ul.is(':visible')){
				if(!trigger){
					ul.slideUp();
				}

			}else{
				ul.slideDown();
			}
			return false;
		});
		$('.competition_descript_left a').click(function(){
			$('.competition_rules a').trigger('click',true);
			if($.browser.safari)
				var bodyelem = $("body")
			else
				var bodyelem = $("html,body")
			$(bodyelem).animate({scrollTop:offset.top}, 500);
			return false;
		})
	}

	if($('.pm_conteiner').length){

		if ($('.good-title').length) {
			$('.good-title').show();
			setTimeout("$('.good-title').fadeOut(600)",3000);
		} else if ($('.error-title').length) {
			$('.error-title').show();
			setTimeout("$('.error-title').fadeOut(600)",3000);
		}
	}

	/*победители конкурса*/
	if($(".competition_winners").length){
		var i=0;
		var c = $(".competition_winners");
		var ul = c.find('ul');
		var li = ul.find('li');
		var active = c.find('ul li.active');
		li.each(function(){
			i++;
		});
		ul.width(400*i);
		$('.competition_winners li:eq(2) img').css({width:180,'margin-top':'0'});
		$('.competition_winners li:eq(2)').css({width:180,'margin-top':'0'}).addClass('active');
		var j=0;
		$('.next').click(function(){
			var first = $('.competition_winners li:first');
			var copy = first.clone();
			copy.insertAfter($('.competition_winners li:last'));
			$('.competition_winners li').removeClass('active');
			first.stop(false,false,true).animate({width:'0'},100,function(){

				//$('.competition_winners li').removeClass('active');
				first.remove();
			});

			$('.competition_winners li:eq(3)').stop(false,false,true).addClass('active');
			$('.competition_winners li:eq(3)').stop(false,false,true).animate({width:180});
			$('.competition_winners li:eq(3) img').stop(false,false,true).animate({width:180,'margin-top':'0'});
			$('.competition_winners li:eq(2)').stop(false,false,true).animate({width:143});
			$('.competition_winners li:eq(2) img').stop(false,false,true).animate({width:100,'margin-top':'25'});

		})
		$('.prev').click(function(){
			var last = $('.competition_winners li:last');
			var copy = last.clone();
			copy.insertBefore($('.competition_winners li:first'));
			$('.competition_winners li').removeClass('active');
			ul.css({left:-140});
			ul.stop(false,false,true).animate({left:'0'},150,function(){

				ul.css({right:0});
			});
			$('.competition_winners li').removeClass('active');
			last.remove();
			$('.competition_winners li:eq(2)').stop(false,false,true).animate({width:180});
			$('.competition_winners li:eq(2) img').stop(false,false,true).animate({width:180,'margin-top':'0'});
			$('.competition_winners li:eq(3)').stop(false,false,true).animate({width:143});
			$('.competition_winners li:eq(3) img').stop(false,false,true).animate({width:100,'margin-top':'25'});
			$('.competition_winners li:eq(2)').addClass('active');
		})
		$('.prev,.next').dblclick(function(){
			return false;
		})
	}
});

/**
 * Скрытие/показ тегов проекта идеи
 */
function showIdeaTags() {
    if($('.tags').length){
        var tagContainer = $('.tags_list');
        var span = tagContainer.next();
        var h = tagContainer.height();
        if(h/18>2){
            tagContainer.height(36);
            span.show();
        }
        span.click(function(){
            if(span.hasClass('opened')){
                tagContainer.height(36);
                span.text('Показать все');
            }else{
                tagContainer.height(h);
                span.text('Скрыть');
            }
            span.toggleClass('opened');
            return false;
        })
    }
}

/**
 * Функция получает значение GET параметр с именем name из URL
 * @param name
 */
function getURLParameter(name) {
	return decodeURI(
		(RegExp(name + '=' + '(.+?)(&|$)').exec(location.search)||[,null])[1]
	);
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
function favoriteLinkCnt(obj,sign,deleteObj) {
	if (deleteObj === undefined)
		deleteObj = false;

	var span = obj.find('.quant_f');
	var cnt = parseInt(span.text());

	if (sign == "plus") {
		span.text(cnt + 1);
		$('.myfavorite').addClass('not_empty');
	} else {

		if ((cnt - 1) <= 0)
		{
			$('.myfavorite').removeClass('not_empty');
			span.text(0);

			if (deleteObj)
				obj.remove()

		} else {
			span.text(cnt - 1);
		}
	}
}


/**
 * Функция наращивает или уменьшает кол-во откликов у тендеру
 * Для объекта внутри которого есть элемент с классом .quant
 * Если deleteObj == true и счетчик кол-ва уменьшается до ноля,
 * объект obj удаляется со страницы.
 *
 * @param obj Объект внутри которого есть элемент с классом .quant, содержащий число
 * @param sign Действие: plus и minus
 * @param deleteObj Флаг удаления объекта obj после обнуления счетчика
 */
function quantCounter(obj,sign,deleteObj) {
	if (deleteObj === undefined)
		deleteObj = false;

	var span = obj.find('.quant');
	var cnt = parseInt(span.text());

	if (sign == "plus") {
		span.text(cnt + 1);
	} else {

		if ((cnt - 1) <= 0)
		{
			span.text(0);

			if (deleteObj)
				obj.remove()

		} else {
			span.text(cnt - 1);
		}
	}
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

 /*минимальное значение в массиве*/
function min(v) {
	var m = v[0]
	for (var i = 0; i <= v.length - 1; i++) {
		if (v[i] < m)
			m = v[i];
	}
	return m;
}

/*позиционирование подменю*/
function submenuPosition() {
	var current = $('#nav li strong').parent('li'),
		o = current.position(),
		s = $('.submenu'),
		l = o.left + current.outerWidth(true);
	s.css({left: l - s.outerWidth() + 5});
}

/**
 * Функция для замены символов новой строки на <br>
 * @param str - строка из textarea
 * @return {*}
 */
function nl2br( str ) {
	return str.replace(/([^>])\n/g, '$1<br/>');
}
function br2nl( str ) {
	return str.replace(/<br>/g, '\n');
}

var js = new CMain();

function CMain(){
	var self = this;

	this.drowTriangle = function(obj){
		var paddingTop = parseInt(obj.css('padding-top'));
		var paddingBottom = parseInt(obj.css('padding-bottom'));
		var h = obj.height();
		h = h+paddingTop+paddingBottom;
		var triangle = $('<i></i>');

		triangle.css({"border-width":(h+1)/2});
		obj.append(triangle);
	};


	this.initSpecList = function(){
		self._servicesToggler();
		self._searchAutocomplete();
		self.backToServicesList();
	};

	this._servicesToggler = function(){
		$('.services_list').on('click','.service_item',function(e){
			if(!$(e.target).is('a')){
				var a = $(this).find('.service_toggler');
				var parent = $(this);
				var container = parent.find('ul');
				var arr = container.find('li');
				if(parent.hasClass('current')){
					var maxH = 100;
					if(container.hasClass('short')){
						maxH = 80;
					}

					container.animate({'max-height':maxH});
					parent.removeClass('current');
					a.text(a.attr('data-text'));
				}else{
					var h = 0;
					container.find('li').each(function(){
						h = h+($(this).height()+8);
					});
					if(container.hasClass('short') && (arr.length > 3)){
						container.animate({'max-height':h});
						parent.addClass('current');
						a.text('Скрыть');
					}
					if(!container.hasClass('short') && (arr.length > 4)){
						container.animate({'max-height':h});
						parent.addClass('current');
						a.text('Скрыть');
					}

				}
			}

		})
	};

	this._searchAutocomplete = function(){

		var timer = setTimeout('',100);
		var scroll = false;
		$('#spec_autocomplete').keyup(function(){
			var input = $(this);
			var val = input.val();
			clearInterval(timer);
			if(val.length>2){

				$(window).bind('scroll',function() {
					    if($(window).scrollTop() > ($(document).height() - $(window).height())-400) {
						if(scroll==false){
						    scroll=true;
						    var next_page_url = $("#next_page_url");
						    if(next_page_url.val() != '0') {
							$.ajax({
							    url: next_page_url.val(),
							    dataType: "json",
							    success: function(response) {
								next_page_url.remove();
								$(".search_content_specs").append(response.html);
								scroll = false;
							    },
							    error: function() {
								scroll = false;
							    }
							});
						    } else {
							scroll = false;
						    }
						}
					}
				});
				$('.services_content, .search_content:visible').addClass('disabled');
				timer = setTimeout("loadResult()",200)
			}else{
				$('.search_content').hide().removeClass('disabled');
				$('.services_content').show().removeClass('disabled');
				$('.popular_words:hidden').slideDown();
			}

		})
	};



	this.backToServicesList = function(){
		$('.services_list').on('click','.back_to_services',function(){
			$('.search_content').hide();
			$('.services_content').show();
			$('.popular_words').slideDown();
		})
	};

	this.serviceToggler = function(){
		var list = $('.search_list');
		list.find('.item').each(function(){
			var item = $(this);
			var services = item.find('.item_path');
			var w = 0;
			if(services.find('a').length>3){
				services.find('a').each(function(i){
					w = w+ ($(this).width()+7);
					if(i>=2){
						services.addClass('short').width(w);
						services.after('<span class="toggler">...</span></span>');
						return false;
					}
				});
			}

		});
		list.on('click','.toggler',function(){
			$(this).prev().removeClass('short').width('auto');
			$(this).remove();
		});
	};

	this.isExpert = function(){
		$('.expert_icon').hover(function(){
			$(this).parent().addClass('hover');
		});
		$('.is_expert').mouseleave(function(){
			$(this).removeClass('hover');
		})
	};

	this.initExperts = function(){
		var copy;
		$('.expert_rules').click(function(){

			if(!copy){
				copy = $('.expert_info').removeClass('hide').clone();
				$('.expert_info').remove();
			}

			var container = $('.expert_link');
			var rules = container.find('.expert_rules_content');

			copy.show();
			container.append(copy);

			rules.find('.btn_grey').live('click',function(){
				$(this).parent().hide();
				copy.addClass('with_form');
				copy.find('.expert_form.show_first').fadeIn();
				self._sendExpertRequest();
				return false;
			});

			copy.find('.close').click(function(){
				copy.fadeOut(100).removeClass('with_form');
				copy.find('.expert_form').hide();
				copy.find('.expert_rules_content .btn_conteiner').show();
			});

			self._expertService();

			self._expertLogin(copy);
		});
	};

	/**
	 * Авторизация пользователя в форме "Хочу стать экспертом"
	 * @param copy Объект jquery $('.expert_info')
	 * @private
	 */
	this._expertLogin = function(copy){
		copy.find('.btn_login').click(function(){
			var $form = $(this).parents('form');
			$form.find('.error-title').hide();
			$.post(
				'/site/ajaxlogin',
				$form.serialize(),
				function(data){
					if (data.success) {
						copy.find('.expert_form').hide();
						copy.find('.expert_form:not(.show_first)').show();
					} else {
						$form.find('.error-title').show();
					}
				}, 'json'
			);
			return false;
		});
	};

	this._expertService = function(){
		$('#service_selector').change(function(){
			var parent = $(this).parents('.request_form_item');
			if(this.value == 0){
				parent.next().show();
			}else{
				parent.next().hide();
				parent.next().find('input').val('');
			}
		})
	};

	this._sendExpertRequest = function(){
		var container = $('.expert_form.request');
		container.find('.btn_grey').click(function(){
			//container.html('<img style="margin:90px 195px;opacity:0.5;" src="/img/loaderT.gif">')
			$('#want_be_expert').find('.error').removeClass('error');
			$.post(
				'/social/expert/sendDesire',
				$('#want_be_expert').serialize(),
				function(data){
					if (data.success) {
						// Если форма отправлена
						$.gritter.add({
							title: data['name']+', <br> ваша заявка была успешно отправлена',
							text: 'Наш менеджер свяжется с вами в течении 2-ух рабочих дней',
							sticky: false,
							time: '4000',
							class_name: '',
							position: 'bottom-left'
							});
						$('.expert_info').hide();
					} else {
						// Если ошибка отправки
						for (var i = 0; i < data.errorFields.length; i++) {
							$('[name="Expert['+data.errorFields[i]+']"]').addClass('error');
						}
					}
				}, 'json'
			);

			return false;
		});

		$('.your_variant').click(function(){
			$(this).hide().next().show().focus();
		});

		$('.clear_var').click(function(){
			$(this).prev().val('').parent().hide().prev().show();
		});

		$('#service_selector').change(function(){
			var parent = $(this).parents('.request_form_item');
			if(this.value == 0){
				parent.next().show();
			}else{
				parent.next().hide();
				parent.next().find('input').val('');
			}
		});
	};

	this.scrollTop = function(){
		var win = $(window);
		var docH = $(document).height();
		var winH = win.height();
		var footH = $('#footer').height();
		var link = $('.scroll_top');
		win.scroll(function(){
			docH = $(document).height();
			winH = win.height();
			link = $('.scroll_top');

			if(win.scrollTop()>winH){
				if(link.length==0){
					$('.wrapper-inside').append($('<div class="scroll_top hide"><span>Наверх </span> <i></i></div>'));
					$('.scroll_top').fadeIn();
				}else{
					link.fadeIn();
				}
				/*определяем позицию кнопки при разных значениях*/
				if(win.scrollTop()>docH-winH-footH){
					link.css({'bottom':((docH-footH)-(win.scrollTop()+winH))*(-1)})
				}else{
					link.css('bottom',0);
				}

			}else{
				link.fadeOut();
			}
		});

		$('body').on('click','.scroll_top',function(){
			$("html:not(:animated),body:not(:animated)").animate({scrollTop:0}, 200)
		})
	};

	this.scrollTo = function(hash, callback){
		var elem = $(hash),
			offset = elem.offset();
		if ($.browser.safari) {
			var bodyelem = $('body');
		}
		else {
			var bodyelem = $('html, body');
		}
		$(bodyelem).animate({scrollTop: offset.top-40}, 500, callback);
	};



}

CMain.prototype.getFileApiSupport = function(){
	return this.fileApiSupport;
};

function loadResult (){
	/*ajax запрос, по success:*/
    var input = $('#spec_autocomplete');
	
	setTimeout(function(){
		$.post('/member/specialist/quickSearch/term/'+input.val(), {"with_services": 1}, function(response){
			response = $.parseJSON(response);
			$('.search_content_specs').html(response.html);
			$('.search_content').find('.block_head').html(response.founded_text);
			$('.search_content').show().removeClass('disabled');
			$('.services_content').hide().removeClass('disabled');
			$('.popular_words:visible').slideUp();
		});
	},100)
}


function reload(){
	window.location.reload();
}