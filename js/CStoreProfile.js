var store = new CStoreProfile();

function CStoreProfile(){
	var self = this;
	this.initCopyForm = function(){
		var form = $('.products_copy_form');
		var maxCnt;
		var select = form.find('select.copy_to:first'),
			item = form.find('.stores_list_block'),
			li = item.find('li'),
			notStore = li.filter('#not_store'),
			inputs = li.filter(':not(#not_store)').find('input');

		var options = select.find('option');
		maxCnt = options.size();
		/*Смена количества товаров в магазине, справа от селекта*/
		form.on('change','.stores', function(){
			var quant = $(this).find('option:selected').data('products');
			var text = quant ? formatNumeral(quant,['товар','товара','товаров']) : 'Нет товаров';
			$(this).next().text(text);
			li.removeClass('disabled').filter("#store_"+$(this).val()).addClass('disabled');
			li.find('input').prop('disabled',false);
			$("#store_"+$(this).val()+' input').prop({'disabled':true,'checked':false});
			_checkedQuant()

		});

		/*быстрый поиск по списку магзинов*/
		form.find('#store_search').keyup(function(){
			var val = $(this).val(),
				cnt=0;
			if(val.length>0){
				li.filter(':not(#not_store)').hide();
				//item.find('li:contains('+$(this).val()+')').show();
				var rx = new RegExp(val, 'i');
				cnt = li.filter(function() {
					return $(this).text().match(rx);
				}).each(function(n, o){
						n = n+1;
						var $aElem = $(o).show();
					}).length;
				if(cnt == 0){
					li.filter('.no_results').show();
					notStore.hide();
				}
				else{
					li.filter('.no_results').hide();
					notStore.show();
				}
			}else{
				item.find('li:not(.no_results)').show();
			}
			notStore.find('.finded_stores').text('('+cnt+')');
		});

		notStore.find('input').change(function(){
			li.filter(':not(#not_store):visible').find('input:not(:disabled)').prop('checked',$(this).prop('checked'));
			_checkedQuant();
		});

		inputs.change(function(){
			_checkedQuant();
		});

		function _checkedQuant(){
			var cnt = 0;
			inputs.each(function(){
				if($(this).prop('checked') == true)
					cnt++;
			});
			notStore.find('.checked_cnt').text(cnt);
		}
	};

	/*Инициализация формы добавления товара и магазина*/
	this.initForm = function(){
		var container = $('.photofile_input').parents('.option_value');
		var colors = $('.colors_list');
		var form = $('.form');
		var file_counter = 1;

		$('.form .simple').on('change','.simple_input',function(){
			$(this).parent().after($('<div class="input_row"><input type="file" name="Product[file_'+file_counter+']" class="simple_input"/><i class="del"></i></div>'));
			file_counter++;
		});

		/*удаление фотографии*/
		$('.option_value.product_image').on('click', '.uploaded_photo .icons>*', function () {
			var container = $(this).parents('.uploaded_photo');
			var pid = $('#pid').val();
			var id = container.prop('id'); //id файла для удаления
			$.post('/catalog/profile/productDeleteImage', {fid:id, pid:pid}, function (data) {
				var response = $.parseJSON(data);
				if (response.success)
					container.remove();
			})
		});

		/*удаление изображения магазина*/
		$('.option_value.store_image').on('click', '.uploaded_photo .icons>span', function () {
			var container = $(this).parents('.uploaded_photo');
			var sid = $('#sid').val();
			$.get('/catalog/profile/storeDeleteImage', {store_id:sid}, function (data) {
				var response = $.parseJSON(data);
				if (response.success)
					container.remove();
			})
		});

		$('select.required').change(function(){
			$(this).find('option[disabled]').remove();
		});

		/*выбор цвета*/
		colors.on('click','li',function(){
			var self=$(this);
			if (self.hasClass('c_checked')){
				self.removeClass('c_checked');
				self.find('input').prop('checked',false);
			}else{
				self.addClass('c_checked');
				self.find('input').prop('checked',true)
			}
		});

		colors.find('li').each(function(){
			var li = $(this);
			var input = li.find('input');
			if(input.prop('checked')==true){
				li.addClass('c_checked');
			}
		});



		/*переключение между расширенной и краткой формой*/
		$('#form_layout').on('click','span',function(){
			var span = $(this);
			var options = $('.options_row.hide');
			var container = options.parents('.options_section:not(.stores_list_container)');
			if(span.hasClass('short')){
				options.hide();
				optionsSectionToggler();
			}else{
				options.show();
				container.show();
			}
			span.parent().find('span').removeClass('current');
			span.addClass('current')
		});

		function optionsSectionToggler(){
			$('.options_section:not(.stores_list_container)').each(function(){
				if($(this).find('.options_row:not(.hide)').size()==0)
					$(this).hide();

			})
		}
		optionsSectionToggler();

		form.find('.timetable_row input:checkbox').change(function(){
			var lunch = $(this).parent().next()
			lunch.toggleClass('hide');
			lunch.find('inbut').val('');
		});
		self.priceRange();
		self._shopsListChecker();
	};


	/*функция выбора предела цены*/
	this.priceRange = function(){
		$('.range').on('click', function(){
			$('.expanded').removeClass('expanded');
			var combo = $(this).parent(),
				select = combo.find('ul'),
				options = select.children(),
				label = combo.find('span.range'),
				value = combo.find('input.hidden_val'),
				parent = combo.parents('.checked'),
				head = $('.shops_list_head');

			select.toggleClass('expanded');

			options.bind('click', _selectOption);

			function _selectOption() {
				var selected = $(this);
				if (!selected.hasClass('selected')) {
					options.not(this).removeAttr('class');
					label.text(selected.data('label'));
					value.val(selected.data('value')).change();
					if(!parent.hasClass('shops_list_head') && head.hasClass('checked'))
						head.find('.range').html('&nbsp;');
				}
				selected.addClass('active');
				_hideCombo()
			}

			function _hideCombo() {
				if (select.hasClass('expanded')) {
					$('.expanded').toggleClass('expanded');
				}
			}
			combo.find('input.textInput').bind('focus', _hideCombo);
		});

		$(document).click(function(e){
			var match = $(e.target).closest(".adding_product_price");
			if (!match.length){
				$('.expanded').removeClass('expanded');
			}
		});
	};

	/**
	 * добавление нового поля, такого же типа
	 * @param callback - функия
	 * @param maxCnt - Максимальное количество добавленных элементов
	 *
	 */
	this._addRow = function(maxCnt,callback){
		$('.form').on('click','.add_row:not(.disabled)', function(){
			var obj = $(this).prev();
			var copy = obj.clone();

			if(maxCnt){
				if($('.added').size()<=maxCnt)
					obj.after(copy.addClass('added'));
			}else{
				obj.after(copy.addClass('added'));
			}
			if(callback)
				callback(obj);
		});

	};

	/*функция для управления чекбоксами магазинов и выставления одинаковых значений цены и наличия*/
	this._shopsListChecker = function(){
		var container = $('.shops_list_block');
		var checker = container.find('.shops_list_head input[type="checkbox"]');
		var list = container.find('.shops_list_container input[type="checkbox"]');
		var li = list.parents('li');

		checker.change(function(){
			var check = $(this).prop('checked');

			list.prop('checked',check);
			if(check){
				list.parents('li').addClass('checked');
				$(this).parents('.shops_list_head').addClass('checked');
			}else{
				list.parents('li').removeClass('checked');
				$(this).parents('.shops_list_head').removeClass('checked');
			}

		});

		list.change(function(){
			var  i=0;
			if($(this).prop('checked'))
				$(this).parents('li').addClass('checked');
			else
				$(this).parents('li').removeClass('checked');

			list.each(function(){
				if($(this).prop('checked') == false)
					i++;
			});
			if(i==0)
				checker.prop('checked',true);
			else
				checker.prop('checked',false);
		});

		/*выстанавливаем одинаковое значение*/
		container.find('.shops_list_head select').change(function(){
			var val = $(this).val();
			li.find('select option[value='+val+']').prop('selected',true);
		});

		container.find('.shops_list_head').on('click','.adding_product_price li',function(){
			var val = container.find('.shops_list_head .hidden_val').val();
			var text = container.find('.shops_list_head .range').text();
			li.find('.hidden_val').val(val);
			li.find('.range').text(text);
		});

		container.on('change','.adding_product_price select',function(){
			if($(this).val()==3)
				$(this).next().find('input').prop('disabled',true);
			else
				$(this).next().find('input').prop('disabled',false).focus();
		});

		container.find('.shops_list_head input.textInput').keyup(function(){
			var val = $(this).val();
			li.find('input.textInput').val(val);

		});
	};

	/*функция для подгрузки подкатегорий выбранной категории*/
	this.initCategoryChoise = function(){
		$('#left_side').on('click','.depth1 a', function(){
			var li = $(this).parent();
			var ul = li.parent();
			var container = $('.subcategory');
			var id = li.id; //id выбранной категории
			ul.find('li').removeClass('current precurrent');
			li.addClass('current').prev().addClass('precurrent');
			var cid = li.attr('cid');
			container.html('<img style="margin:45% 0 0 45%;opacity:0.5;" src="/img/loaderT.gif">');
			$.post('/catalog/profile/getSubCategories', {cid: cid}, function(data){
				var response = $.parseJSON(data);
				if(response.success){
					container.html(response.html);
					container.scrollTop(0);
				}
				else
					alert(response.message);
			});

			return false;
		});



		$('#latest_cats').on('click','a', function(){
			$('#category_id').val($(this).attr('cid'));
			$('#product-form').submit();
			return false;
		});

		$('#right_side').on('click','.depth2 a, depth3 a', function(){
			var li = $(this).parent();
			if(li.find('ul').length == 0) {
				$('#category_id').val(li.attr('cid'));
				$('#product-form').submit();
			}
			return false;
		});
	};

	this.initProductsFilter = function(){
		var items = $('.products_filter_item');
		var params = $('.filter_params');
		var container = $('.added_products_list');

		$('.notice .close').click(function(){
			$(this).parent().fadeOut();
		});

		$(document).click(function(e){
			var match = $(e.target).closest(".products_filter_item");
			if (!match.length){
				$('.hidden_list:visible').find('.cancel_link').trigger('click');
			}

			var close = $(e.target).closest(".btn_conteiner");
			if (!close.length){
				$('.notice:visible').find('.close').trigger('click');
			}
		});
		self._printCheckedParams(items,params);
		if(container.find('.item.added').length>0)
			self._newProductLayer(container);
		self._productsOnProceesCheck();
		self._addedProductsFilter(items, params, container);
		self._clearFilter(items, params);
		self._productInfinityScroll(items, params, container);
		self._sortList(items, params, container);
	};

	//функция для привязки товаров к магазину
	this.bindProduct = function(){
		var bindList = $('.bind_list');
		bindList.on('change','.bind input',function(){
			$(this).parents('.item').toggleClass('binded');
			$(this).parents('.item').find('.price .textInput').focus();
		});

		bindList.on('change','.price select',function(){
			if($(this).val()==3)
				$(this).next().find('input').prop('disabled',true).val('');
			else
				$(this).next().find('input').prop('disabled',false).focus();
		});

		// .item change even handler (save changed data via ajax)
		bindList.on("keyup change", ".item", function () {
			clearTimeout($.data(this, 'timer'));
			var $this = $(this);
			var wait = setTimeout(function () {
				var enabled = false;

				if ($this.find("input[name=\"binded\"]:first").is(':checked')) enabled = 1; else enabled = 0;

				// collecting item changed data
				var data = {
					store_id: $("#store_id").val(),
					product_id: $this.find("input[name=\"product_id\"]:first").val(),
					price: $this.find("input[name=\"price\"]:first").val(),
					price_type: $this.find("select[name=\"price_type\"]:first").val(),
					url: $this.find("input[name=\"url\"]:first").val(),
					enabled: enabled
				};

				// send ajax request for save collecting data
				$.ajax({
					url: "/catalog/profile/savePrice",
					data: data,
					dataType: "json",
					type: "post",
					success: function (response) {
						// server-side validation error
						if (!response.success) {
							//alert(response.message);
							return false;
						} else {
							$("#onlyStoreProducts").next('span').html(response.productQt);
						}
					},
					error: function () {
						//alert("Ошибка сохранения данных");
					}
				});
			}, 1000);
			$(this).data('timer', wait);
		});
	};

	this.bindProductWithDiscount = function(){
		var bindList = $('.bind_list');

		bindList.on('change','.bind input',function(){
			$(this).parents('.item').toggleClass('binded');
			$(this).parents('.item').find('.price_value').focus();
		});

		bindList.on('click', '.discount span', function(){
			if ($(this).parents('.price').find('.price_value').val() != '') {
				$(this).next().add(this).toggle().focus();
			}
		});

		bindList.on('blur', '.discount :input', function(){
			if ($(this).val() != '') {
				$(this).prev().addClass('on');
			}
			else {
				$(this).prev().removeClass('on');
			}
			$(this).prev().add(this).toggle();
		});

		bindList.on('blur', '.price_value', function(){
			if ($(this).val() == '') {
				$(this).parents('.base').next().find(':input').val('');
				$(this).parents('.base').next().find('.percent').text('% скидки').removeClass('on');
				$(this).parents('.base').next().find('.suggested').text('Цена со скидкой').removeClass('on');
			}
		}).on('keyup change', '.price_value', function(){
			if ($(this).val() != '') {
				$('.percent_value', bindList).trigger('keyup');
			}
			else {
				$(this).trigger('blur');
			}
		});

		bindList.on('keyup change', '.percent_value', function(){
			clearTimeout($.data(this, 'timer'));
			var $this = $(this);
			var wait = setTimeout(function(){
				var price = parseInt($this.parents('.price').find('.price_value').val()),
				    discount = parseFloat($this.val().replace(',', '.')),
				    suggested = (discount < 100)
					    ? Math.round(price * (1 - (discount / 100)))
					    : '' ,
				    f_discount = ($this.val() == '') ? '% скидки' : Math.round(discount) + '%',
				    f_suggested = ($this.val() == '') ? 'Цена со скидкой' : (discount < 100) ? suggested + ' руб.' : 'Цена со скидкой' ; 
				$this.prev().text(f_discount).parent().next().find('.suggested').text(f_suggested).next('.suggested_value').val(suggested);
				if (discount < 100) {
					$this.parent().next().find('.suggested').addClass('on');
				}
				else {
					$this.parent().next().find('.suggested').removeClass('on');
				}
			}, 100);
			$(this).data('timer', wait);
		});

		bindList.on('keyup change', '.suggested_value', function(e){
			clearTimeout($.data(this, 'timer'));
			var self = this, $this = $(self);
			var wait = setTimeout(function(){
				var price = parseInt($this.parents('.price').find('.price_value').val());
				if ($this.val() > price) {
					return $this.val(price);
				}
				var suggested = parseInt($this.val()),
				    discount = (suggested < price)
					    ? parseInt(((1 - (suggested / price)) * 100) * 10000)/10000
					    : '',
				    f_discount = ($this.val() == '') ? '% скидки' : (suggested < price) ? Math.round(discount) + '%' : '% скидки',
				    f_suggested = ($this.val() == '') ? 'Цена со скидкой' : suggested + ' руб.';
			 	$this.prev().text(f_suggested).parent().prev().find('.percent').text(f_discount).next('.percent_value').val(discount);
				if (suggested < price) {
					$this.parent().prev().find('.percent').addClass('on');
				}
				else {
					$this.parent().prev().find('.percent').removeClass('on');
				}
			}, 100);
			$(this).data('timer', wait);
		});

		bindList.on("change", ".item", function (e) {
			clearTimeout($.data(this, 'timer'));
			var $this = $(this);
			var wait = setTimeout(function () {
				/**
				 * Возвращает тип цены. Если чекбокс не выборан
				 * возвращается алтернативное значение
				 * из атрибута чекбокса.
				 *
				 * @returns {*}
				 */
				function _getPriceType(){
					var checkbox = $this.find("input[name=\"price_type\"]:first");
					if (checkbox.is(':checked')) {
						return checkbox.val();
					} else {
						return checkbox.attr('data-value-second');
					}
				}

				var enabled = ($this.find("input[name=\"binded\"]:first").is(':checked')) ? 1 : 0;

				// collecting item changed data
				var data = {
					store_id: $("#store_id").val(),
					product_id: $this.find("input[name=\"product_id\"]:first").val(),
					price: $this.find("input[name=\"price\"]:first").val(),
					price_type: _getPriceType(),
					discount: $this.find("input[name=\"discount\"]:first").val(),
					url: $this.find("input[name=\"url\"]:first").val(),
					enabled: enabled
				};

				// send ajax request for save collecting data
				$.ajax({
					url: "/catalog/profile/savePrice",
					data: data,
					dataType: "json",
					type: "post",
					success: function (response) {
						// server-side validation error
						if (!response.success) {
							alert(response.message);
							return false;
						} else {
							$("#onlyStoreProducts").next('span').html(response.productQt);
						}
					},
					error: function () {
						alert("Ошибка сохранения данных");
					}
				});
			}, 250);
			$(this).data('timer', wait);
		});
	};

	this._printCheckedParams = function(items,params){

		var html = '';
		items.each(function(){
			var item = $(this);
			var list = item.find('input:checked');

			self._changeCheckedParams(item.find('.hidden_list'));

			list.each(function(){
				var li = $(this).parents('li');
				html = html + '<span data-type="'+li.parent().prop('id')+'" data-id="'+li.prop('id')+'">'+li.text()+'<i></i>' +
					'</span>';
			});

			$('.params_list').html(html);

			if(html)
				params.show();
		});
	};

	this._newProductLayer = function(container){
		var timeout = setTimeout(function(){
			container.find('.item.added i.close').click()
		},5000);
		container.find('.item.added i.close').click(function(){
			clearTimeout(timeout);
			$(this).parents('.added_layer').fadeOut();
		})
	};

	/*Функция проверки наличия незавершенного процесса добавления товара*/
	this._productsOnProceesCheck = function () {
		$('#create_product').click(function () {

			if ( $(this).hasClass("store-alert") )
				return false;

			$.post('/catalog/profile/checkProductInProgress', {}, function (response) {
				response = $.parseJSON(response);
				if (response.exists) {
					$("#continue_update").attr('href', response.link);
					$("#exist_product_cat_name").html(response.cat_name);
					$('.notice').fadeIn();
					return false;
				} else {
					document.location.href = '/catalog/profile/productSelectCategory';
					return false;
				}
			});
			return false;
		});
	};

	/**
	 * Инициализация функций фильтра товаров
	 */
	this._addedProductsFilter = function(items, params, container){
		//показываем списки производителей и категорий
		items.find('span').click(function(){
			var item = $(this).parent();
			var checked = '';
			var opened = $(this).next().is(':visible');
			items.find('.hidden_list:visible').find('.cancel_link').trigger('click');
			item.find('ul input:checked').each(function(){
				checked = checked+' '+$(this).parents('li').prop('id');
			});
			item.find('.hidden_list').attr('data-checked',checked);
			if(!opened)
				item.find('.hidden_list').fadeIn(150);
		});

		items.find(".cancel_link").click(function(){
			var item = $(this).parent();
			var li = item.find('li');
			var str = item.attr('data-checked');
			var arr = str.split(' ');

			item.fadeOut(150);
			li.filter(':not(.no_results)').show();
			item.find('.textInput').val('');

			/*отмечаем только те чекбоксы, которые были выделены до открытия*/
			li.find('input').prop('checked',false);
			for (i in arr){
				if(arr[i])
					item.find('li#'+arr[i]+' input').prop('checked',true);
			}
			return false;
		});

		//применяем выбранные в списках чекбоксы
		items.find('.btn_grey').click(function(){
			var item = $(this).parent();
			var html = '';
			var i = 0;
			items.find('input[type="checkbox"]').each(function(){
				if($(this).prop('checked')==true){
					var li = $(this).parents('li');
					html = html + '<span data-type="'+li.parent().prop('id')+'" data-id="'+li.prop('id')+'">'+li.text()+'<i></i>' +
						'</span>';
					i++;
				}
			});

			$('.params_list').html(html);
			if(!html){
				params.hide();
			}else{
				params.show();
			}
			items.find('.hidden_list').fadeOut(150);

			self._changeCheckedParams(item);


			self._formSubmit();

			return false;
		});

		//быстрый поиск по спискам чекбоксов
		items.find('input.textInput').keyup(function(){
			var item = $(this).parent(),
				val = $(this).val(),
				li = item.find('li'),
				cnt=0;
			if(val.length>0){
				item.find('li').hide();
				//item.find('li:contains('+$(this).val()+')').show();
				var rx = new RegExp(val, 'i');
				cnt = li.filter(function() {
					return $(this).text().match(rx);
				}).each(function(n, o){
						n = n+1;
						var $aElem = $(o).show();
					}).length;
				if(cnt == 0)
					li.filter('.no_results').show();
				else{
					li.filter('.no_results').hide();
				}
			}else{
				item.find('li:not(.no_results)').show();
			}
		});

		$('.products_filter .search').click(function(){
			self._formSubmit();
			return false;
		});

		//удаление параметра из списка
		$('.params_list').on('click','i',function(){
			var span = $(this).parent();
			var id = span.data('id');
			var type = span.data('type');
			var item = items.find('.hidden_list #'+type);
			var list = $('.params_list').find('span:not(.clear_filter)');

			span.remove();
			if(list.length==1){
				params.slideUp();
			}

			item.find('li#'+id).find('input').prop('checked',false);

			self._changeCheckedParams(item.parents('.hidden_list'));

			self._formSubmit();
		});
	};

	/*Изменение подписи в зависимости от количества выбранных чекбоксов производителей и категорий*/
	this._changeCheckedParams = function(item){
		var ul = item.find('ul');
		var words='';
		var i = ul.find('input:checked').size();

		if(i>0){
			if(item.find('ul').is('#category'))
				words = ['категория','категории','категорий'];
			else
				words = ['производитель','производителя','производителей'];
			var text = formatNumeral(i,words);
			item.prev().text(text);
		}else{
			if(item.find('ul').is('#category'))
				item.prev().text('Категории');
			else
				item.prev().text('Производители');
		}
	};

	/*функция сброса параметров фильтра*/
	this._clearFilter = function(items, params){
		var filter = $('#products_filter');
		filter.find('.clear_filter').click(function(){
			items.find('input[type="checkbox"]').prop('checked',false);
			filter.find('input[type="text"]').val('');
			params.slideUp();
			params.find('.params_list').html('');
			items.each(function(){
				var item = $(this);
				self._changeCheckedParams(item.find('.hidden_list'));
			});

			self._formSubmit();
		});
	};

	/*инфинити скролл для товаров.
	 *Товары подгружаются при скролле страницы каждый раз, когда значение скролла привышает высоту экрана-narginBottom
	 */
	this._productInfinityScroll = function(items, params, container){
		var scroll = false;
		if(! container.hasClass('bind_list')){
			$(window).bind('scroll',function() {
				var loader = $('.loader');
				var pr_list = $('.products_list');
				var marginBottom = 400;
				if($(window).scrollTop() > ($(document).height() - $(window).height())-marginBottom) {
					if(scroll==false){
						scroll=true;
						var next_page_url = $("#next_page_url");
						if(next_page_url.val() != '0') {
							loader.show();
							$.ajax({
								url: next_page_url.val(),
								data: $('#products_filter').serialize(),
								dataType: "json",
								success: function(response) {
									next_page_url.remove();
									pr_list.append(response.html);

									scroll = false;
									loader.hide()
								},
								error: function() {
									scroll = false;
								}
							});
						}else{
							scroll = false;
						}
					}
				}
			});
		}
	};

	/*Функция сортировки списка товаров*/
	this._sortList = function(items, params, container){
		$('.products_list_header').on('click','a',function(){
			var header = $(this).parents('.products_list_header');
			var span = $(this).parent();
			var sort = $('#sort');
			var order = $('#order');

			if(span.hasClass('sort')){
				if(span.hasClass('asc')){
					span.removeClass('asc').addClass('desc');
					order.val('desc')
				}else if(span.hasClass('desc')){
					span.removeClass('desc').addClass('asc');
					order.val('asc');
				}
			}else{
				header.find('span').removeClass('sort asc desc');
				span.addClass('sort asc');
				sort.val(span.data('fieldname'));
				order.val('asc');
			}
			self._formSubmit();
			return false;
		})
	};


	this._formSubmit = function(){

		var list = $('.products_list');
		var listHead = list.prev().show();
		var data = $('#products_filter').serialize();
		var productQtSelector = $('#productQt');

		// если на странице присутствует pagination, то обновление listview
		// осуществляется через базовый функционал виджета $.fn.yiiListView
		if ($("#update-by-widget").length) {
			$("#products_filter").submit();
		} else {
			$.post('', data, function (response) {
				response = $.parseJSON(response);
				if (!response.success)
					return false;

				if (response.productQt > 0) {
					listHead.show();
					list.html(response.html);
					productQtSelector.html(response.productQt);
				} else {
					listHead.hide();
					list.html('<div class="no_result"> К сожалению, по вашему запросу ничего не найдено. Убедитесь, что все слова написаны без ошибок, или попробуйте использовать более популярные ключевые слова. </div>')
				}
			});
		}
	};

	var currentSection = 0;
	this.initSections = function(){
		var toggler = $('div.toggle_sections');
		$('span', toggler).click(function(e){
			var self = $(this),
				wrap = self.parent().prev().children('.wrap'),
				total = wrap.children().size() - 1;

			if (self.is('.next')) {
				if (currentSection < total) {
					self.prev().removeClass('disabled');
					wrap.animate({left: '-=178'}, 350, function(){});
				}
				else {
					return false;
				}
				currentSection++;
				if (currentSection == total) {
					self.addClass('disabled');
				}
			}
			if (self.is('.prev')) {
				if (currentSection > 0) {
					wrap.animate({left: '+=178'}, 350, function(){});
				}
				else {
					return false;
				}
				currentSection--;
				if (currentSection == 0) {
					self.addClass('disabled');
				}
				else {
					self.next().removeClass('disabled');
				}
			}
		});

	};


	/*инициализация функций для списка магазинов*/
	this.initStoreFilter = function(){
		var container = $('.added_stores_list');
		var header = $('.stores_list_header ');
		var form = $('#stores_filter');
		self._sortStoreList(header,form, container);
	};

	/*Функция сортировки списка товаров*/
	this._sortStoreList = function(header, form, container){
		header.on('click','a',function(){
			var span = $(this).parent();
			var sort = $('#sort');
			var order = $('#order');

			if(span.hasClass('sort')){
				if(span.hasClass('asc')){
					span.removeClass('asc').addClass('desc');
					order.val('desc')
				}else if(span.hasClass('desc')){
					span.removeClass('desc').addClass('asc');
					order.val('asc');
				}
			}else{
				header.find('span').removeClass('sort asc desc');
				span.addClass('sort asc');
				sort.val(span.data('fieldname'));
				order.val('asc');
			}
			self._storeFormSubmit();
			return false;
		})
	};


	this._storeFormSubmit = function(){
		var list = $('.added_stores_list');
		var listHead = list.prev().show();
		var data = $('#stores_filter').serialize();
		var storesQtSelector = $('#storesQt');

		$.post('', data, function(response){

			response = $.parseJSON(response);
			if(!response.success)
				return false;

			if(response.storesQt>0){
				listHead.show();
				list.html(response.html);
				storesQtSelector.html(response.storesQt);
			}else{
				listHead.hide();
				list.html('<div class="no_result"> К сожалению, по вашему запросу ничего не найдено. Убедитесь, что все слова написаны без ошибок, или попробуйте использовать более популярные ключевые слова. </div>')
			}

		});
	};

	this.initStoreList = function () {
		var list = $('.added_stores_list');

		list.on('click', '.item .del', function () {
			var i = $(this);
			doAction({
				'yes': function () {
					var item = i.parents('.item');
					var store_id = i.data('store-id');
					$.post("/catalog/profile/storeDelete", {store_id: store_id}, function (response) {
						response = $.parseJSON(response);
						if (response.success) {
							item.animate({height: 0}, 200, function () {
								item.remove();
							})
						} else {
							alert(response.message);
						}
					});
				},
				'no': function () {
					return false;
				}
			}, "удалить магазин", "После удаления магазин не будет виден на сайте.");
			return false;
		});
	};

	this.initComments = function(){
		var commentsList = $('.product_comments ');
		commentsList.on('click','.answer_part > a',function(){
			var answer = $(this).parent();
			if(answer.hasClass('opened'))
				_closeCommentForm(answer);
			else
				_openCommentForm(answer);
			return false;
		});
		commentsList.on('click','.answer_part .edit',function(){
			var answer = $(this).parents('.answer_part');
			_openCommentForm(answer);
		});
		commentsList.on('click','.answer_part .del',function(){
			var answer = $(this).parents('.answer_part');
			_delComment(answer);
		});
		function _openCommentForm(answer){
			if(answer.hasClass('editable')){
				var text = answer.find('.text').
					text();
				answer.removeClass('editable')
					.find('>span')
					.addClass('hide');

			}

			if(answer.find('.comment_answer').length == 0)
				$('#comment_answer').clone()
					.removeAttr('id')
					.appendTo(answer);
			answer.addClass('opened')
				.find('.comment_answer')
				.removeClass('hide')
				.find('textarea')
				.val(text);
			_addComment(answer);
			_cancelEdit(answer);
		}
		function _closeCommentForm(answer){
			var data = answer.data();

			answer.removeClass('opened')
				.find('.comment_answer')
				.addClass('hide')
				.find('textarea')
				.val('');
			if(answer.data('answerid') != undefined)
				answer.addClass('editable')
					.find('>span')
					.removeClass('hide');
		}
		/**
		 * Добавление комментария
		 * @param answer - контейнер с комментарием
		 * @private data.answerId - id комментария
		 * @private data.commentId - id отзыва, к которому нужно добавить ответ
		 */
		function _addComment(answer){
			answer.find('.btn_grey').on('click',function(){
				var data = answer.data();
				data.message = answer.find('textarea').val();

				if (data.message.length == 0) {
					alert('Необходимо написать ответ!');
					return false;
				}

				$.post($('#answer_create_url').val(), data, function(response) {
					response = $.parseJSON(response);
					if (response.success) {
						answer.addClass('editable')
							.data('answerid',response.answerId)
							.removeClass('opened')
							.html(response.html);
					} else {
						alert(response.message);
					}
				});
				return false;
			});
		}

		/**
		 * Удаление комментария
		 * @param answer - контейнер с комментарием
		 * @private data.answerId - id комментария
		 * @private data.commentId - id отзыва
		 */
		function _delComment(answer) {
			doAction({
				'yes':function () {
					var data = answer.data();
					$.post($('#answer_delete_url').val(), data, function (response) {
						response = $.parseJSON(response);
						if (response.success) {
							answer.removeClass('editable')
								.removeData('answerid')
								.html('<a href="#">Ответить на отзыв</a>');
						} else {
							alert(response.message);
						}
						return false;
					});
				},
				'no':function () {
					return false;
				}
			}, 'Удалить ответ', 'После удаления ответа он не будет виден на сайте')

		}
		function _cancelEdit(answer){
			answer.find('.cancel_link').on('click',function(){
				_closeCommentForm(answer);
				if(answer.data('answerid') != undefined)
					answer.addClass('editable');
				answer.find('>span')
					.removeClass('hide');
				return false;
			})
		}
	};

	this.statDate = function(){

		$(".first-day").datepicker({
			showOn:"focus",
			dateFormat:"dd.mm.y",
			dayNamesMin:["Вс", "Пн", "Вт", "Ср", "Чт", "Пт", "Сб"],
			monthNames:["Январь", "Февраль", "Март", "Апрель", "Май", "Июнь", "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь"],
			nextText:" ",
			prevText:" ",
			firstDay:1,
			onSelect : function(dateText, inst)
			{
				var epoch = $.datepicker.formatDate('@', $(this).datepicker('getDate')) / 1000+10800;
				$(this).prev().val(epoch);
				_updateStat();
			}
		});

		$(".last-day").datepicker({
			showOn:"focus",
			dateFormat:"dd.mm.y",
			dayNamesMin:["Вс", "Пн", "Вт", "Ср", "Чт", "Пт", "Сб"],
			monthNames:["Январь", "Февраль", "Март", "Апрель", "Май", "Июнь", "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь"],
			nextText:" ",
			prevText:" ",
			firstDay:1,
			onSelect : function(dateText, inst)
			{
				var epoch = $.datepicker.formatDate('@', $(this).datepicker('getDate')) / 1000+10800;
				$(this).prev().val(epoch);
				_updateStat();
			}
		});

		$('.first-day, .last-day').next().click(function(){
			$(this).prev().focus();
		});

		function _updateStat(){
			var 	statPeriod = $('.stat-period'),
				statContent = $('.stat-content'),
				firstDay = statPeriod.find('.first-day').prev().val(),
				lastDay = statPeriod.find('.last-day').prev().val(),
				storeId = $('#store_id').val();


			if(firstDay>lastDay){
				statPeriod.addClass('error');
			}else{
				statPeriod.removeClass('error');
				statContent.addClass('-loading');

				$.ajax({
					url: '/catalog/profile/ajaxGetStoreStat/id/' + storeId,
					dataType : "json",
					data: {
						dateFrom: firstDay,
						dateTo: lastDay
					},
					method: 'POST',
					success: function (data) {
						statContent.removeClass('-loading');
						statContent.replaceWith(data.html);
					},
					error: function(data) {
						statContent.removeClass('-loading');
					}
				});
			}
		}
	}
}