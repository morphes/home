var //modelId,
	catalog = function(){
		var _options = {};

	function initFilter(){
		var 	filter = $('.page-sidebar'),
			form = filter.find('form');

		_priceChange();
		_inputActions();
		_submiter();
		_showHiddenInputs();
		_pageSettings();

		//_colorSelector();
		function _priceChange(){
			var 	priceBlock = filter.find('.price'),
				range = priceBlock.find('.range'),
				data = range.data();


			priceBlock.find('input').focusout(function(){
				_priceRange();
			});
			_priceRange();

			function _priceRange(){
				var 	priceFrom = priceBlock.find('input:eq(0)'),
					priceTo = priceBlock.find('input:eq(1)'),
					priceFromVal = priceFrom.val(),
					priceToVal = priceTo.val();

				priceFromVal = (priceFromVal < data.minprice) ? data.minprice : priceFromVal;
				priceToVal = (priceToVal > data.maxprice) ? data.maxprice : priceToVal;

				range.slider({
					range: true,
					min: data.minprice,
					max: data.maxprice,
					values: [priceFromVal, priceToVal],
					step:1,
					slide: function(event, ui) {
						priceFrom.val(ui.values[0]).change();
						priceTo.val(ui.values[1]).change();
					},
					stop: function(event, ui){
						//self._updateQuantity($("#price_from"));
					}
				});

				priceFrom.val(range.slider( "values", 0 ));
				priceTo.val(range.slider( "values", 1 ));

			}


		}

		function _colorSelector(){
			filter.find('.color-selector').on('change', 'input', function(){
				$(this).find('span').toggleClass('checked').end().find('input').prop('checked', true).change();

			});
		}

		function _inputActions (){
			filter.on('change','input, select',function(){
				var action = $(this).data('action');
				switch (action){
					case 'submit':
						form.submit();
						break;
					case 'showButton':
						$(this).parents('.sidebar-block').find('.submit-filter').fadeIn().css('display','inline-block');
						break;
					default :
						break;
				}
			});
		}

		function _submiter(){
			filter.on('click', '.submit-filter',function(){
				form.submit();
			});
		}

		function _showHiddenInputs(){
			$('.show-all').click(function(){
				$(this).parent().find('label.-hidden, label.-visible').toggleClass('-hidden -visible');
			});
		}

		function _pageSettings(){
			var 	layout = form.find('#layout'),
				itemsQnt = form.find('#items-qnt'),
				sortField = form.find('#sort-field'),
				sortOrder = form.find('#sort-order');

			$('.layout-icons span:not(.current)').click(function(){
				layout.val($(this).data('layout')).change();
			});
			//записываем в куки количество элементов на страницу
			$('.items-qnt select').change(function(){
				CCommon.setCookie("product_filter_pagesize", $(this).val(), {expires:31*24*60*60, path:"/"});
				form.submit();
			});
			//записываем в куки поле и порядок сортировки
			$('.sorting .-sort a').click(function(){
				var data = $(this).data();

				CCommon.setCookie("product_filter_sort", data.sort,{"expires":1800,"path":"\/"});
				form.submit();
				return false;
			});
		}

	}

	function initBreadCrumbs (){
		var breadcrumbs = $('.-breadcrumbs');
		breadcrumbs.on('click','i', function(){
			var i = $(this);
			var li = i.parent();
			var id = i.data('id');

			if(i.hasClass('-icon-toggle-up')){
				_hideSubmenu();
			}else{
				if(!i.next().size() > 0){

					$.ajax({
						url: "/catalog2/category/ajaxLoadChilds",
						dataType: "json",
						async: false,
						data: {category_id: id},
						success: function (response) {
							i.after(response.html);

							li.addClass('opened');
							i.removeClass('load');
						},
						error: function (response) {
							console.log(response);
						}
					});

					/*i.after('<div class="-breadcrumbs-submenu">'+
						'<div class="-col-3 submenu_section">'+
						'<ul class="-menu-block -small">'+
						'<li class="current"><a href="/catalog">Товары</a></li>'+
						'<li><a href="/idea">Идеи</a></li>'+
						'<li><a href="/specialist">Специалисты</a></li>'+
						'<li><a href="/tenders/list">Заказы</a></li>'+
						'<li><a href="/journal">Журнал</a></li>'+
						'<li><a href="/forum">Форум</a></li>'+
						'</ul>'+
						'</div>'+
						'</div>');*/
					_showSubmenu(i);
				}else{
					_showSubmenu(i);
				}
			}

		});

		function _showSubmenu(i){
			_hideSubmenu();
			i.removeClass('-icon-toggle-down').addClass('-icon-toggle-up')
				.parent().addClass('opened');
		}
		function _hideSubmenu(){
			var i = breadcrumbs.find('i');
			i.each(function(){
				i.removeClass('-icon-toggle-up').addClass('-icon-toggle-down')
					.parent().removeClass('opened');
			});
		}
		$(document).click(function(e){
			var submenu = $(e.target).closest(".-breadcrumbs > li.parent");
			if (!submenu.length){
				_hideSubmenu();
			}
		});
	}

	function setItemHeight(){
		$('.catalog-items-row').each(function(){
			var	items = $(this).find('.-col-3'),
				height = 0;
			items.each(function(){
				if($(this).height() > height){
					height = $(this).height();
				}
			});
			items.height(height);
		});
	}

	function hideBmBlock(){
		$('.bm-link i').click(function(){
			$(this).parents('.bm-link').slideUp('fast',function(){
				/*тут скрипт который делает так что бы плашка больше не показывалась*/
				$(this).remove();
			});
		});
	}

	function initProductPage(){
		var page = $('.goods-item');
		page.on('click','.review button',function(){
			var 	review = $(this).parents('.review'),
				data = review.find('form').serializeArray(),
				errorMessage = review.find('.-error-list ol'),
				str='';

			var modelId = _options.modelId;
			review.addClass('-loading');
			errorMessage.parent().hide();
			$.ajax({
				type: "POST",
				async: false,
				data: data,
				url: '/catalog2/product/CreateFeedback/id/'+modelId,
				dataType: "json",
				success: function(response) {
					if(response.success==true) {
						review.removeClass('-loading').html(response.html);
					} else {
						review.removeClass('-loading');
						for (key in response.errors) {
							str += '<li>'+response.errors[key] + '</li>';
						}
						errorMessage.html(str);
						errorMessage.parent().show();
					}

				},
				error: function() {
						window.reload();
				}
			});

			//ajax запрос, по success:
			//review.removeClass('-loading').html(response);
			return false;
		});

		page.on('click', '.product-desc .toggle-desc', function(e){
			var self = $(this),
				desc = self.prev(),
				visible = $('span:first', desc),
				hidden = $('span:last', desc),
				d = hidden.is(':visible') ? 'none' : 'inline',
				o = hidden.is(':visible') ? 0 : 1 ;

			visible.toggleClass('visible');

			if (!hidden.is(':visible')) {
				hidden.css({display: d}).animate({opacity: o}, 'normal');
			}
			else {
				hidden.animate({opacity: o}, 'fast', function(){
					$(this).css({display: d});
				});
			}
		});

		$('.online-stores-list').on('click', '.toggle-stores-list', function(e){
			$('.-hidden', $(e.delegateTarget)).fadeToggle();
		});

		page.on('click','.popup-city-list', function(){
			$('.city-list').modal({
				overlayClose: true,
				persist: true,
				onOpen: function(dialog){
					_customPopupShow(dialog);
				},
				onClose: function(dialog){
					_customPopupHide(dialog);
				}
			});
		});

		page.on('click', '.toggle-city-list', function(){
			$.modal.close();
			setTimeout(function(){
				$('.city-list').modal({
					overlayClose: true,
					onOpen: function(dialog){
						_customPopupShow(dialog);
					},
					onClose: function(dialog){
						_customPopupHide(dialog);
					}
				});
			}, 400);
		});

		page.on('click','.popup-city-stores', function(){
			$('.city-stores').modal({
				overlayClose: true,
				persist: true,
				minHeight: $(window).height() - 200,
				maxHeight: $(window).height() - 200,
				autoResize: true,
				onOpen: function(dialog){
					_customPopupShow(dialog, function(popup){
						var height = popup.data.height() - $('h1', popup.data).outerHeight();
						$('.map, .addresses', popup.data).height(height);
					});
				},
				onClose: function(dialog){
					_customPopupHide(dialog);
				}
			});
		});

		page.on('click','.toggle-specs', function(){
			var toggler = $(this),
				specs = $('.specs');
			max = specs.hasClass('expanded') ? 135 : Math.max.apply(Math, $('div', specs).map(function(){ return $(this).height(); }).get());
			specs.animate({height: max}, 200, function(){
				$(this).toggleClass('expanded');
			});
		});

		function _customPopupShow(popup, callback) {
			popup.overlay.fadeIn(200, function(){
				popup.container.fadeIn(100, function(){
					popup.data.fadeIn(100, function(){
						if (typeof callback === 'function') {
							callback(popup);
						}
					});
				});
			});
		}
		function _customPopupHide(popup, callback) {
			popup.data.fadeOut(100, function(){
				popup.container.fadeOut(100, function(){
					popup.overlay.fadeOut(100, function(){
						$.modal.close();
					});
				});
			});
		}
	}

	function photoPopup(){
		$('.preview').click(function(){
			var index = $(this).find('img').attr('id').replace('originPhoto_','');
			var modelId = _options.modelId,
				popup = $('.photogallery-view');

			if(popup.find('div').size() == 0){
				$.ajax({
					type: "POST",
					async: false,
					data: {'item':{'modelId':modelId}},
					url: '/catalog2/product/getGalleryAjax',
					dataType: "json",
					success: function(response) {
						popup.append(response.html);
						_viewPhoto(index);
					},
					error: function() {
						//	window.reload();
					}
				});
			}else{
				_viewPhoto(index);
			}


		});

		function _viewPhoto(index){
			$('body').css({'overflow':'hidden'});
			var summ = $('.summary').clone();
			//console.log(summ)
			$('.photogallery-view').modal({
				overlayClose: true,
				maxHeight:'90%',
				maxWidth:'95%',
				onShow: function(obj){

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
					imgContainer.width(obj.data.width()-300);
					imgInfo.find('.summary').append(summ.html());
					imgInfo.find('.summary .social').remove();
					imgInfo.find('.viewport').height(obj.data.height()-90);
					imgInfo.tinyscrollbar();


					var gal = gallery.carousel({
						vertical:'middle',
						preview:preview,
						control:control,
						verticalMargin:60,
						afterStop:function(e,l){
							//'прелоад' изображений в попапе
							gal[0].loadImages();
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

		function setOptions (options){
			$.extend(true, _options, options)
		};

	return {
		initFilter:initFilter,
		initBreadCrumbs:initBreadCrumbs,
		setItemHeight:setItemHeight,
		hideBmBlock:hideBmBlock,
		initProductPage:initProductPage,
		photoPopup:photoPopup,
		setOptions:setOptions
	}
}();
