var idea = new CIdea({});

function CIdea(options){
	var self = this;
	this._options = {
		'ideaId':0,
		'ideaType':0,
		'popupUrl':''
	};
	/**
	 * функция для показа/скрытия тегов
	 */
	this.showTags = function(){
		$('.photos').on('click', '.tags span',function(){
			var a = $(this);
			var li = a.parent();
			var text = li.html();
			var parent = $(this).parents('.room_head');

			if(li.hasClass('current')){
				li.removeClass('current');
				text = text.replace('↑','&darr;');
				li.html(text)
				parent.find('.tags_list').hide();
			}else{
				li.addClass('current');
				text = text.replace('↓','&uarr;');
				li.html(text)
				parent.find('.tags_list').show();
			}
		})
	};

	/**
	 * функция, для фиксации сайдбара и смены активного пункта меню.
	 * Используется плагины из twitter bootstrap
	 */
	this.initSidebar = function(){
		var 	w = $('.idea_sidebar'),
			h = w.outerHeight(true),
			p = w.offset(),
			lHash = (location.hash) ? location.hash : '';

		w.affix({
			offset: {
				top: h + p.top
			}
		});

		var flag = false, c = 0, i = 1;

		setTimeout(function(){$(window).trigger('scrollTop')}, 1);
		
		if (lHash) {
			//проверка на старый хэш
			if(/(room)/.test(lHash)){

				lHash = '#r_'+lHash.split('/')[2];
			}
			js.scrollTo(lHash, function(){
				var y = $(window).scrollTop();
				$(window).scrollTop(y-50);
			});
		}

		$(window).scroll(function() {
			var page = $('.-layout-page'),
			    sidebar = $('.idea_sidebar');
			flag = (sidebar.hasClass('affix')) ? true : false ;
			if (flag) {
				page.addClass('minified_left_side');
				if (c == 0) {
					sidebar.css({opacity: 0}).animate({opacity: 1}, 1000);
				};
				i = 0; c++;
			}
			else {
				page.removeClass('minified_left_side');
				c = 0; i++;
				if (i == 1) {
					$('#left_side').css({opacity: 0}).animate({opacity: 1}, 1000);
					if ($('.icon-info').is('.active'))
						$('.icon-info').trigger('click');
					if ($('.icon-share').is('.active'))
						$('.icon-share').trigger('click');
				}
			}
		});
		
		w.on('click', '.room_list li a', function(){
			if ($(this).parent().hasClass('active')) {
				return false;
			}

			var hash = this.hash;
			js.scrollTo(this.hash, function(){
				location.hash = hash;
				var y = $(window).scrollTop();
				$(window).scrollTop(y-30);
			});
			return false;
		});

		$('.icon-share, .icon-info').on('click', function(e){
			// e.preventDefault();
			var p = $(this).position();
			if ($(this).is('.active')) {
				$(this).next().css({opacity: 1}).animate({opacity: 0}, 250, function(){
					$(this).remove();
				});
			}
			else {
				if ($(this).is('.icon-share')) {
					var locator = $('#left_side .share_block');
				}
				if ($(this).is('.icon-info')) {
					var locator = $('#idea_info > .idea_properties');
					if ($('.icon-share').is('.active'))
						$('.icon-share').trigger('click');
				}
				locator
					.clone()
					.insertAfter($(this))
					.addClass('minified')
					.css({top: p.top})
					.animate({opacity: 1}, 500);					
			}
			$(this).toggleClass('active');
			return false;
		});
		$('a.pseudo-link, a.comments_quant').on('click', function(e){
			// e.preventDefault();
			js.scrollTo('#comments');
			return false;
		});
	};

	/*функция, определяющая позицию блока с товаром, в зависимости от его положения на фотографии
	* Если товар расположен левее середины фотографии, то блоку с товаром будет присвоен класс left
	* */
	this.initProductLayer = function(){
		$('.photos').find('.image_container').each(function(){
			var container = $(this);
			container.find('.product_label').each(function(){
				var prod = $(this);
				var left = parseFloat(prod.data('left'));
				var top = parseFloat(prod.data('top'));

				if(left<=50)
					prod.find('.product_item').addClass('left');
				if(top>60)
					//prod.find('.product_item').addClass('top');
					prod.find('.product_item').css({top:prod.find('.product_item').height()*(-1)+6})
			});
		});
	};
	/**
	 * функция, показывающая все фотографии помещения
	 * для отмены загрузки всех фотографий сразу подменяем атрибут src для изображения и подставляем его обратно после клика на ссылку "показать еще"
	 */
	this.showPhotos = function(){
		/*$('.hidden_photos .photo_item_img img').each(function(){
			$(this).attr('data-src',$(this).attr('src'));
			$(this).removeAttr('src');

		});

		$('.show_more').click(function(){
			var parent = $(this).parents('.room');
			parent.find('.hidden_photos').removeClass('hide');
			parent.find('.hidden_photos img').each(function(){
				$(this).attr('src',$(this).attr('data-src'));
			});
			$(this).hide();
		})*/
	};

	/**
	 * фунцкция для прокрутки страницы до комментариев
	 */
	this.commentScroll = function(){
		$('.sidebar-tools.comment span, .comments_quant').click(function(){
			var comments = $('#comments');
			var destination = comments.offset().top - 12;
			$("html:not(:animated),body:not(:animated)").animate({scrollTop:destination}, 500);
			return false;
		});
	};

	/**
	 * фунцкция для показа/скрытия описания идеи
	 */
	this.showDescript = function(){
		var p = $('.description');
		var ellipsis = p.find('.ellipsis');
		var more = p.find('span.hide');
		var text = p.find('span.desc_min');
		$('.all_elements_link').click(function(){
			if(ellipsis.hasClass('hide')){
				ellipsis.removeClass('hide');
				more.addClass('hide');
				text.removeClass('hide');
				$(this).find('span:not(.arrow)').text('Показать полностью');
				$(this).find('span.arrow').html('&darr;');
			}else{
				more.removeClass('hide');
				ellipsis.addClass('hide');
				text.addClass('hide');
				$(this).find('span:not(.arrow)').text('Скрыть описание');
				$(this).find('span.arrow').html('&uarr;');
			}
		})
	};

	/**
	 * функция для загрузки данных в попап
	 */
	var gal;
	this.showPopup = function(id){
		var imgId;


		$('.photos').on('click','.image_container>img',function(){
			imgId = $(this).data('id');
			var id = self._options.ideaId;

			var parent = $('#blind');
			if(!parent.hasClass('loaded')){
				parent.clone()
					.appendTo($('body'))
					.addClass('loaded')
				parent.remove();
			}
			var container = $('#container');

			container.parent()
				.fadeIn(300)
				.css('top',$(window).scrollTop());
			$('body').css({'overflow':'hidden'});
			if(gal==undefined){
				$.ajax({
					url:self._options.popupUrl,
					type: "get",
					dataType: "json",
					data: {'idea_id':self._options.ideaId},
					async: false,
					success: function(response) {
						if (response.success) {
							container.html(response.html);
						}
						if (response.error){
							//window.reload();
						}
					},
					error: function() {
						//window.reload();
					}
				});
				self._initPopup(imgId);


			}
			var arr = $('#idea_thumbs').find('a:not(.thumbs_arrow)');
			gal[0].slideTo(arr.index($('#thumb'+imgId)));

		})
	};
	var flag= true;
	/**
	 * функция для инициализации попапа
	 */
	this._initPopup = function(index){

		var screen = $(window),
			w = screen.width(), //ширина экрана
			h = screen.height(), //высота экрана
			container = $('#container'), //контейнер с попапом
			mask = container.parent(), //маска (полупрозрачный фон)
			left = container.find('.player_left'), //левая часть попапа
			right = container.find('.player_right'), //правая часть попапа
			gallery = $('#carousel'), //контейнер с фотографиями идеи
			photos = left.find('li'), //фотографии в попапе
			galArrows = left.find('.page_arrow'),
			galNext = left.find('.next'),
			galPrev = left.find('.prev'),
			desc = right.find('.photo_descript'), //описание фотографии
			thumbContainer = $('#idea_thumbs'), // контейнер с превьюхами
			thumbs = thumbContainer.find('a:not(.thumbs_arrow)'), //превьюшки
			prev = thumbContainer.find('.prev'), //кнопка prev для превью
			next = thumbContainer.find('.next'),  //кнопка next для превью
			close = container.find('.close'), //кнопка закрытия
			firstP = 0,
			leftPos = ((w-container.width())/2<10) ? 10 : (w-container.width())/ 2, //позиция контейнера в зависимости от разрешения экрана
			topPos = ((h-container.height())/2<10) ? 10 : (h-container.height())/ 2,
			roomId,
			a,
			href,
			title,
			message,
			activeIndex,
			flagKeyArrows;


		/*инициализация скрола*/
		desc.tinyscrollbar();
		container.css({'left':leftPos,top:topPos});
		/*инициализация галереи*/
		gal = gallery.carousel({
			vertical:'middle',
			thumbs:thumbs,
			afterStop:function(e,l){
				//'прелоад' изображений в попапе
				gal[0].loadImages();

				activeIndex = gal[0].getCurPage();
				if(activeIndex==firstP)
					galArrows.removeClass('disable').filter('.prev').addClass('disable');
				else if(activeIndex==thumbs.length-1)
					galArrows.removeClass('disable').filter('.next').addClass('disable');
				else
					galArrows.removeClass('disable');

				//пролистывание превью
				var lastP = firstP+6;
				if (activeIndex < firstP) {
					var num  = firstP-activeIndex;
					for (var i=0; i<num; i++)
						prev.click();
				} else if (activeIndex > lastP) {
					var num = activeIndex-lastP;
					for (var i=0; i<num; i++)
						next.click();
				}

				/*смена описания помещения*/
				if(roomId!=thumbs.eq(activeIndex).data('room')){
					roomId = thumbs.eq(activeIndex).data('room');
					$('.popup_room_info').hide();
				}

				/*смена описания фотографии*/
				title = $.trim($('#room_id_'+roomId).show().find('h2').text());
				desc.tinyscrollbar();

				$('.popup_photo_info').hide();
				message = $.trim($('#photo_id_'+thumbs.eq(activeIndex).data('photo')).show().find('.overview').text());
				desc.tinyscrollbar();

				/*подменяем href на ссылки для расшаривания*/
				right.find('.share_block a').each(function(){
					a = $(this);
					href = a.data('href');
					href = href.replace(/\{id\}/g,thumbs.eq(activeIndex).data('photo'));
					href= href.replace('{title}',encodeURIComponent(title));
					href= href.replace('{message}',encodeURIComponent(message));
					a.prop('href',href);
				});

				var activePage = left.find('li.active');
				if(activePage.find('.products').length==0){
					var activePageId = activePage.children('div').attr('id').replace('origin_','');
					var products = $('#p_'+activePageId).find('.image_container .product_label').clone();
					var productsContainer = $('<div class="products"></div>');
					var offset = activePage.find('img').offset();
					var leftmargin = offset.left-leftPos-15; //15 - padding-left у #player_left
					var containerStyles = {
						'left':leftmargin,
						'top':activePage.find('img').css('margin-top'),
						'width':activePage.find('img').css('width'),
						'height':activePage.find('img').css('height')
					};


					products.each(function(){
						productsContainer.append($(this));
					});
					productsContainer.css(containerStyles);
					activePage.find('div').append(productsContainer);
				}
			}

		});

		/*кастомные превьюшки*/
		thumbs.bind('click', function(e){
			thumbs.removeClass('active');
			$(this).addClass('active');
			gal[0].slideTo(thumbs.index(this));
			return false;
		});

		/*события на кнопки prev, next*/
		galArrows.click(function(){
			if(!$(this).hasClass('disable')){
				if($(this).hasClass('next'))
					gal[0].slideTo(activeIndex+1);
				else
					gal[0].slideTo(activeIndex-1);
			}
		});

		//события на стрелки клавиатуры
		$(document).on({
			'keydown': function(e){
				if (flagKeyArrows != 'down') {
					flagKeyArrows = 'down';

					if (e.keyCode == 39 || e.keyCode == 40) {
						galNext.click();
						return false;
					} else if (e.keyCode == 37 || e.keyCode == 38) {
						galPrev.click();
						return false;
					}
				}
			},
			'keyup': function(){
				flagKeyArrows = 'up';
			}
		}, 'body');


		/*события для кнопок next/prev превью*/
		next.click(function(){
			if(!next.hasClass('disabled')){
				thumbs.eq(firstP).hide();
				thumbs.eq(firstP+7).show();
				firstP++;

				if(firstP > 0){
					prev.removeClass('disabled');
				}

				if (thumbs.length-1 <= firstP+6) {
					next.addClass('disabled');
				}
			}
		});

		prev.click(function(){
			if(!prev.hasClass('disabled')){
				firstP--;
				thumbs.eq(firstP).show();
				thumbs.eq(firstP+7).hide();

				if( thumbs.length-7 > firstP ){
					next.removeClass('disabled');
				}
				if (firstP <= 0) {
					prev.addClass('disabled');
				}
			}
		});


		/*закрытие попапа */
		close.click(function(){
			mask.fadeOut(200);
			$('body').css({'overflow':'auto'})
			flag = true;
		});

		$(document).click(function(e){
			var target = $(e.target);
			if (target.is(mask)){
				close.trigger('click');
			}
		});
	};

	this._showthumb = function(thumbs,index){
		thumbs.eq(index).hide();
		thumbs.eq(index+7).show();
	};

	this.setOptions = function(options){
		$.extend(true, this._options, options)
	}
}

