var cat = new CCatalog({});

function CCatalog(options){
	var self = this;
	this._options = {
		roomsUrl:''
	}

	this._initRange = function(){

		var minPrice = parseInt($('#min_price').val());
		var maxPrice = parseInt($('#max_price').val());
		var priceFrom = parseInt($('#price_from').val());
		var priceTo = parseInt($('#price_to').val());

		priceFrom = (priceFrom<minPrice) ? minPrice : priceFrom;
		priceTo = (priceTo>maxPrice) ? maxPrice : priceTo;

		$( "#range" ).slider({
			range: true,
			min: minPrice,
			max: maxPrice,
			values: [priceFrom, priceTo],
			slide: function( event, ui ) {
				$( "#price_from" ).val(ui.values[ 0 ]);
				$( "#price_to" ).val(ui.values[ 1 ]);
			},
			stop: function(event, ui){
				self._updateQuantity($("#price_from"));
			}
		});
		$( "#price_from" ).val($( "#range" ).slider( "values", 0 ));
		$( "#price_to" ).val($( "#range" ).slider( "values", 1 ));
	};

	this.priceChange = function(){
		self._initRange();

		$('#price_from, #price_to').focusout(function(){
			self._initRange();
		})
	};
	this.drowTriangle = function(obj){
		var h = obj.height();
		var triangle = obj.next();
		triangle.css({"border-width":(h+30)/2});
	};

	this.initBreadCrumbs = function(){
		$('.catalog_path > li.parent i').click(function(){
			var i = $(this);
			var li = i.parent();
			var ul = li.parent();
			var id = li.attr('data-id');

			if(li.hasClass('opened')){
				li.removeClass('opened');
			}else{
				ul.find('li').removeClass('opened');
				if(!i.next().is('div')){
					i.addClass('load');

					$.ajax({
						url: "/catalog/category/ajaxLoadChilds",
						dataType: "json",
						async: false,
						data: {category_id: id},
						success: function(response){
							i.after(response.html);

							li.addClass('opened');
							i.removeClass('load');
						},
						error: function(response){
							console.log(response);
						}
					});
				}else{
					li.addClass('opened');
				}
				var colsCnt = li.find('.catalog_path_submenu .submenu_section').size();
				var w = (colsCnt>1) ? colsCnt*245 : 150;
				li.find('.catalog_path_submenu').css({'width':w+'px',right:-1*(w-10)});
			}

			$(document).click(function(e){
				if($('.catalog_path_submenu').length){
					var submenu = $(e.target).closest(".catalog_path>li.parent");
					if (!submenu.length){
						$(".catalog_path>li.parent").removeClass('opened');
					}
				}
			});
		});
	};
	var formTop;
	this.initFilter = function(){
		self._filterToggler();
		self._listToggler();
		$('#filter_form .checkbox-list').each(function(){
			var self = $(this);
			var parent = self.parents('.slide_container');
			var container = self.parents('.filter_item');
			var input = self.children('input:hidden');

			var arr = self.find('input[type="checkbox"]');
			var tmpArr = input.val().split(', ');
			var i,j,k=0;

			for (i=0; i<tmpArr.length; i++) {
				if (tmpArr[i]=='')
					continue;
				for (j=0; j<arr.length; j++){

					if ( arr[j].value==tmpArr[i] ){
						arr[j].checked=true;
						k++;
					}
				}
			}


			arr.click(function(){
				if (this.checked){
					this.checked = true;
				} else {
					this.checked = false;
				}
				var val = '';
				var cnt = 0;
				arr.filter('li input:checked').each(function(){
					if (this.value!='')
						val += this.value+', '
					cnt++;
				});
				input.val(val);
				//getCount($(this));
			});

		});

		$('.slide_container').each(function(){
			var container = $(this);
			if(container.find('input[name="has_value"]').val()>0){
				container.show();
				container.prev().find('i').addClass('opened');
			}
		});

		$('.filter_item.drop_down li').click(function(){
			$('#object_type').val($(this).attr('data-rel'));
			$('.checkbox-list').each(function(){
				$(this).children('input:hidden').val('');
			});

			$('#filter_form').submit();
		});

		$('#filter_form .room_color').each(function(){
			var self = $(this);
			var input = self.children('input:hidden');
			var arr = self.find('.colors_list li');
			var tmpArr = input.val().split(', ');
			var container = self.find('.checked_color');
			var i,j,id, name='';
			for (i=0; i<tmpArr.length; i++) {
				if (tmpArr[i]=='')
					continue;
				for (j=0; j<arr.length; j++){
					var item = $(arr[j]);
					name = item.find('p').html();
					id = item.find('input').val();

					if ( id == tmpArr[i] ){
						item.addClass('c_checked');
						item.find('input').attr('checked',true);
						var span = $('<span></span>').attr( 'id', 'c'+item.attr('id') ).html(' '+name);
						container.append( span );
					}
				}
			}

			arr.click(function(){
				var self=$(this);
				var checkbox = self.find('input');
				var parent = self.parents('.filter_item');
				var input = parent.find('input[type="hidden"]');

				if (self.hasClass('c_checked')){
					self.removeClass('c_checked');
					checkbox.attr('checked',false);
				}

				else{
					self.addClass('c_checked');
					checkbox.attr('checked',true);
				}


				container.empty();
				var name='';
				var colors = '';
				var id = '';
				arr.filter('.c_checked').each(function(){
					name = $(this).find('p').html();
					id = $(this).find('input').val();
					var span = $('<span></span>').attr( 'id', 'c'+$(this).attr('id') ).html(' '+name);
					colors = colors+' '+id;
					container.append(span);
				});
				colors = colors.replace(/\s/g,', ');
				colors = colors.replace(/,/,'');
				colors = colors.replace(/\s/,'');
				input.val(colors);
				//getCount(self);
			});

		});
		formTop = $('#filter_form').offset().top;

		$('#filter_form input,#filter_form select').live('change',function(){
			self._updateQuantity($(this));
		});
		$('.colors_list li').click(function(){
			self._updateQuantity($(this));
		});
	};

	var hideTimer;
	this._updateQuantity = function(obj){
		var filterHint = $('.filter_hint');
		var form = $('#filter_form');
		formTop = form.offset().top;
		var top = obj.offset().top-formTop-5;
		clearTimeout(hideTimer);

		$.get(form.attr('action'), form.serialize(), function(response){
		    response = $.parseJSON(response);
		    $(".filter_hint").html(response.hint_message);
		    $("#filter_action").text("Показать " + response.message);

		    if (filterHint.is(':visible'))
			filterHint.fadeIn().animate({'top': top}, 200);
		    else
			filterHint.css({'top':top}).fadeIn();
		    // Запускаем таймер скрытия
		    hideTimer = setTimeout(function(){ filterHint.fadeOut(); }, 4000);
		});
	};

	this._filterToggler = function(){
		$('.filter_name span').on('click',function(){
			var span = $(this);
			var parent = span.parent();

			parent.next('div').slideToggle();
			span.prev().toggleClass('opened');
		});
	};

	this._listToggler = function(){
		$('#filter_form').on('click','.show_all',function(){
			var span = $(this);
			var parent = span.parent();
			parent.find('.hide_types').slideToggle();
			if(span.hasClass('opened'))
				span.text('Показать все');
			else
				span.text('Скрыть');

			span.toggleClass('opened');
		});
	};

	this.ComentPartToggler = function(){
		var p = $('.comment_part p');
		var span = $('.comment_part span');
		var lineHeight = 18;
		var lineQuant = 2;
		p.each(function(){
			var p = $(this);
			var h = p.height();
			var qnt = parseInt(h/lineHeight);
			if(qnt>2){
				p.height(lineHeight*lineQuant);
				p.next().show();
			}
		});
		span.click(function(){
			var span = $(this);
			var p = span.prev();
			if(p.hasClass('opened')){
				p.height(lineHeight*lineQuant).toggleClass('opened');
			}else{
				p.css({height:'auto'}).toggleClass('opened');
			}
		});

	};


	this.photo = function(){
		var photo = $('.product_photo_big');
		var container = $('.product_photo');
		var buttons = container.find('.buttons');
		var ul = $('.product_photo_previews ul');
		var list = ul.find('li');
		var listContainer = $('.product_photo_previews_container');
		var bigPhoto = $('.product_photo_big img');
		var qnt = 0;
		var next = 5;
		var prev = -1;
		var ph="";
		var photos = new Array();
		var w = list.width();
		ul.find('a').each(function (i) {
			var a = $(this);
			if (i == 0) $(this).addClass('active');
			qnt = i + 1;
			photos[i] = {
				title:a.attr('title'),
				loaded:false,
				origin:a.attr('href')
			};
			$(new Image()).attr('src',a.attr('href'));
			ph = ph+"<a id='ph_" + i + "' rel='photo_group' href='" + a.attr('data-src') + "'></a>";
		});
		container.append('<div class="photos_list hide">'+ph+'</div>');



		list.click(function(){
			var li = $(this);
			if(!li.hasClass('current')){
				var index = li.index();
				list.removeClass('current');
				li.addClass('current');
				bigPhoto.animate({opacity:0},200,function(){
					bigPhoto.attr({'src':photos[index].origin,'id':"oroginPhoto_"+index,'class':'show_origin'});
					bigPhoto.animate({opacity:1},200);
				});
			}
			return false;
		});

		if(qnt>5){
			ul.width(qnt*(w+8));
			container.find('.product_photo_previews').addClass('padding');
			buttons.show();
			buttons.click(function(){
				var c = $(this);
				if(c.hasClass('prev_photo')){
					if(prev>=0){
						var dw = w+8;
						next--;
						prev--;
						ul.animate({right:'-='+dw},150);
						container.find('.next_photo').removeClass('disabled');
						if(prev==-1){
							$(this).addClass('disabled');
						}
					}
				}
				if(c.hasClass('next_photo')){
					if(next<qnt){
						var t = w+8;
						next++;
						prev++;
						ul.animate({right:'+='+t},150);
						container.find('.prev_photo').removeClass('disabled');
						if(next==qnt){
							$(this).addClass('disabled');
						}
					}
				}
			})
		}

		$("a[rel='photo_group']").fancybox({
			'overlayColor':'#000',
			'overlayOpacity':0.9,
			'titleShow':true,
			'title':'<h2 class="headline">'+$('h1').text()+'</h2>',
			'padding':0,
			'module':'catalog'
		});

		$('.show_origin').live('click', function () {
			var id = this.id;
			id = id.split('_')[1];
			$('a#ph_' + id).trigger('click');
		});

		$('.zoom_link').click(function(){
			$('.show_origin').trigger('click');
		});
	};

	this.initCarusele = function(){
		$('.catalog_index .catalog_row').each(function(){
			var container = $(this).find('.catalog_items_list_small');
			var items = container.find('.item');
			var cnt = items.length;
			container.width(cnt*158);
		});
		self._initCaruseleControls();
	};
	this._initCaruseleControls = function(){
		$('.catalog_row_items .gallery_button').click(function(){

			var a = $(this);
			var container = a.parents('.catalog_row_items').find('.catalog_items_list_small');
			var items = container.find('.item');
			var cnt = items.length;
			var maxMargin = (cnt)*158-(4*158);

			if(a.is('.btn_left')){
				if(container.css('right')!="0px"){
					container.animate({right:"-=158"},150);
				}
			}else{
				if(container.css('right')<maxMargin+"px"){
					container.animate({right:"+=158"},150);
				}
			}
		});
	};

	this.menuTriangle = function(){
		var obj = $('.red_menu li.current');
		var h = obj.height();
		var triangle = $('<i></i>');
		triangle.css({"border-width":(h+13)/2});
		obj.append(triangle);
	};

	this.menuTabs = function(){
		$('.menu_tabs li span').click(function(){
			var li = $(this).parent();
			var content = li.attr('data-content');
			var ul = li.parent();

			if(!li.is('.current')){
				ul.find('li').removeClass('current');
				li.addClass('current');
                var update_url = $('#menu_tabs_update_url').val();
                $.ajax({
                    url: update_url + '/type/' + content,
                    dataType: 'json',
                    beforeSend : function() {
                        $('.red_menu').html('<img style="margin:50px 35%;" src="/img/loader.gif">');
                    },
                    success: function(data) {
                        $('.red_menu').html('<ul>'+data.html+'</ul>');
                    }
                });
			}
		});
	};


	this.loadMap = function(){
		$('.shops_list li h2').click(function(){
			var li = $(this).parents("li");
			var parent = $('.shops_list');

			parent.find("li").removeClass("current");
			li.addClass("current");
		});
	};

	this.scroll = function(){
		$('.shops_list').tinyscrollbar();
	};

	this.showHint = function(){
		var obj = $('.recomended_price');
		obj.find('span').mouseenter(function(){
			obj.find('.hint').stop().fadeIn(50);

		});
		obj.find('span').mouseleave(function(){
			obj.find('.hint').css({opacity:1}).stop().fadeOut(150);
		});
	};

	this.initCatalogMainPage = function(){
		var 	page = $('.goods-index'),
			list = page.find('.cat-list'),
			filter = page.find('.cat-filter-search'),
			content = page.find('#updated-area');

		initCatList();
		initTabs();
		initFastSearch();
		initCatToggler();
		initIdeas();
		clearSearch();
		function initCatList(){
			page.on('click','dt', function(){
				$(this).toggleClass('-icon-toggle-empty-down -icon-toggle-empty-up').next().find('dl').slideToggle('fast');
			});
		}

		function initTabs(){
			filter.on('click','a',function(){
				var contentType = $(this).data('room');
				filter.find('li').removeClass('current');
				$(this).parent().addClass('current');
				content.toggleClass('loading');
				$.ajax({
					url:self._options.roomsUrl,
					type: "post",
					dataType: "json",
					data: {roomId:contentType},
					async: true,
					success: function(response) {
						if (response.success) {
							content.html(response.html);
							self.carousel($(".carousel"), 4);
							setTimeout(initIdeas,300);
							page.find('.-expanded').removeClass('.-expanded');
							content.toggleClass('loading');
						}
						if (response.error){
							//window.location.reload();
						}
					},
					error: function() {
						//window.location.reload();
					}
				});
				return false;
			});
		}
		function initFastSearch(){
			var timer = setTimeout('',0);
			var firstDepth = list.find('>dl>dd');
			var input;
			page.on('keyup','.-search input.textInput',function(e){
				clearInterval(timer);
				input =  $(this);
				if(e.keyCode==13)
					_showResult();
				else {
					timer = setTimeout(function(){
						_showResult();
					}, 500);
				}
					
			});

			function _showResult(){
				var item = input.parent(),
					val = input.val(),
					a = list.find('a'),
					cnt= 0;

				a.find(".finded-text").each(function(){
					$(this).parent().text($(this).parent().text());
				});
				if(val.length>1){
					input.next().addClass('-clear-search');
					var rx = new RegExp(val, 'i');
					cnt = a.filter(function() {
						return $(this).text().match(rx);
					}).each(function(n, o){
							var 	$aElem = $(o),
								found = $aElem.text().match(rx);

							$aElem.html(
								$aElem.text().split(rx).join('<span class="finded-text">'+found+'</span>')
							);
							if($aElem.parents('dt:hidden')){
								$aElem.parents('dd').slideDown().prev('dt').show();
								$aElem.parents('dl').slideDown('fast').parent().prev().addClass('-expanded')
							}
						}).length;

					//закрываем открытые подуровни, если в них ничего не нашлось
					firstDepth.each(function(){
						if($(this).find('.finded-text').length==0){
							$(this).slideUp('fast').prev('dt').slideUp();
						}else{

						}
					})
				}else{
					//list.find('.-collapsed').slideUp('fast').parent().prev().removeClass('-expanded');
					list.find('dd,dt').show();
					list.find('.-expanded').removeClass('-expanded');
					list.find('dl dl').slideUp();
					input.next().removeClass('-clear-search');
				}
			}
		}
		function clearSearch(){
			page.on('click','.-clear-search',function(){
				$(this).prev().val('').keyup();
			})
		}
		function initCatToggler(){
			$('.expand-link').click(function(){
				if($(this).hasClass('-expanded')){
					$(this).removeClass('-expanded');
					page.find('dl.-collapsed:visible').slideUp('fast').parent().prev().toggleClass('-expanded');
				}else{
					$(this).addClass('-expanded');
					page.find('dl.-collapsed:hidden').slideDown('fast').parent().prev().toggleClass('-expanded');
				}

				var text = $(this).find('i').text();
				$(this).find('i').text($(this).attr('data-alt'));
				$(this).attr('data-alt',text);
			});
		}
		function initIdeas(){
			var ideas = page.find('.-idea-products');
			var thumbs = ideas.find('.-cat-idea-thumbs>div');
			var covers = ideas.find('.image_container');
			var descriptions = ideas.find('.-idea-info>div');


			_positioningProducts($(covers[0]));

			page.on('click','.-cat-idea-thumbs > div',function(){
				var t = $(this);
				thumbs.removeClass('current');
				t.addClass('current');
				covers.hide().filter('#idea-cover-'+ t.data('id')).show();
				descriptions.hide().filter('#idea-desc-'+ t.data('id')).show();


				var container = covers.filter('#idea-cover-'+ t.data('id'));
				_positioningProducts(container);
			});

			function _positioningProducts(container){
				container.find('.product_label').each(function(){
					var prod = $(this);
					var left = parseFloat(prod.data('left'));
					var top = parseFloat(prod.data('top'));
					if(left<=50)
						prod.find('.product_item').addClass('left');

					if(top>60)
						prod.find('.product_item').css({top:prod.find('.product_item').height()*(-1)+5})
				});
			}
		}
	};

	this.initCatList = function () {
		$('.cat_toggle').on('click', function () {
			var self = $(this),
				ul = self.parent().prev(),
				li = $('li', ul),
				_height = li.outerHeight(true) * 8,
				_margin;
			self.toggleClass('expanded');
			if (self.is('.expanded')) {
				_height = li.size() * li.outerHeight(true);
				_margin = 0;
				self.text('Скрыть').prepend('<i></i>');
			}
			else {
				_margin = -27;
				self.text(self.attr('data-text')).prepend('<i></i>');
			}
			ul.animate({maxHeight:_height}, 400);
			self.parent().animate({marginTop:_margin}, 100);
		});

		$('.-expand-menu').on('click', function() {
			var toggler = $(this),
				list = toggler.prev(),
				item = list.children(),
				span = toggler.children(),
				_height = item.outerHeight(true) * 7;
			list.toggleClass('-menu-expanded');
			toggler.toggleClass('-menu-expanded');
			if (list.is('.-menu-expanded')) {
				_height = item.size() * item.outerHeight(true);
				span.text('Скрыть').prepend('<i></i>');
			}
			else {
				span.text(span.attr('data-text')).prepend('<i></i>');
			}
			list.animate({maxHeight:_height}, 400);
		});
	};

	this.toggleDesc = function(){
		$('.catalog_items_list  div.item').hover(_expandDesc, _expandDesc);

		function _expandDesc(){
			// $('a.item_name, span.manufacturer a, span.manufacturer b', $(this))
			$('a.item_name, span.manufacturer', $(this)).each(function(){
				var p = $(this).data('title'),
					t = $(this).text();
				$(this).text(p).data('title', t).toggleClass('item_name_expanded');
			});
		}
	};

	this.carousel = function(obj,visible){
		var elements = obj.find('>div'),
			w = elements.width()+parseFloat(elements.css('margin-left'))+parseFloat(elements.css('margin-right'));
		if(elements.length>visible)
			obj.after('<i class="prev arrow"></i><i class="next arrow"></i>');
		obj.width(w*elements.length);
		obj.parent().on('click','.arrow',function(){
			if($(this).hasClass('next')){
				if(parseFloat(obj.css('left'))>-1*(w*elements.length-w*visible))
					obj.filter(':not(:animated)').animate({
						'left':'-='+w
					},'fast')
			}else{
				if(parseFloat(obj.css('left'))<0)
					obj.filter(':not(:animated)').animate({
						'left':'+='+w
					},'fast')
			}
		});
	};

	this.addHover = function(){
		$('.catalog_items_list').on('hover','.item',function(){
			$(this).toggleClass('hover');
		});
	};

	this.setOptions = function(options){
		$.extend(true, this._options, options);
	};
}
