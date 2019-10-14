var bindProducts = new CBindProducts();

/**
 * Класс для реализации возможностей по привязке товаров к фоткам идей.
 * @constructor
 */
function CBindProducts()
{
	var self = this;

	// JQuery селектор для ссылок с превьюшки
	var selectorSmallPreview = '.bind_products';

	// JQuery селектор контейнера, в котором лежит фотка и товарчики
	var selectorPictureWrapper = '.picture_wrapper';

	// JQuery селектор фотографии, на которую добавляем товары
	var selectorImage = '.picture';

	// JQuery селектор списка товаров
	var selectorListProducts = '.products_list';

	// JQuery селектор для блока, в котором показывается превьюшка при наведении на товар в списке
	var selectorProductPreview = '.product_preview';

	// Ссылка, которая открывается в попап окне
	var urlForPopup = '/idea/admin/interior/bindProducts/file_id/:id/model/:model/model_id/:model_id';

	// Ссылка, на которую идет запрос для поиска товара
	var urlSearchProduct = '/idea/admin/interior/ajaxSearchProduct/id/:id';

	// Ссылка, на которую идет запрос для привязывания товара к фото
	var urlBindProduct = '/idea/admin/interior/ajaxBindProduct';

	// Ссылка, на которую идет запрос для отвязывания товара от фото
	var urlUnbindProduct = '/idea/admin/interior/ajaxUnbindProduct';

	// Ссылка, на которую идет запрос для обновления данных по товару
	var urlUpdateProduct = '/idea/admin/interior/ajaxUpdateProduct';

	// Popup для привязывания товаров
	var popup;

	// ID фотографии, к которой привязываем товары
	var fileId = 0;

	// Ширина и высота оригинальной фотографии
	var imageWidth = undefined;
	var imageHeight = undefined;

	var model;
	var modelId;

	/**
	 * Инициализация отркытия попапа с превьюшки
	 * @param model String название модели, которой принадлежит фотография
	 * @param model_id Integer Идентификатор элемента модели model
	 */
	this.initPopup = function(model, model_id){
		$(selectorSmallPreview).click(function(){

			// ID Фотографии
			var file_id = parseInt( $(this).data('file_id') );

			// Параметры для попап окна
			// Параметры оставляем пустыми, чтобы попапчик просто открылся в новом табе
			var params = [
				//'height=' + screen.height,
				//'width=' + screen.width,
				//'fullscreen=yes', // only works in IE, but here for completeness,
			].join(',');

			// Открываем окно
			popup = window.open(
				urlForPopup.replace(':id', file_id).replace(':model', model).replace(':model_id', model_id),
				'_blank',
				params
			);
			popup.moveTo(0,0);

			return false;
		});
	};

	/**
	 * Инициализируем уже добавленные товары к фото
	 */
	this.initExistProd = function(){
		$(selectorPictureWrapper).find('.product').each(function(index, element){
			var $prod = $(this);

			// Навешиваем драгабле
			drag($prod);

			dragOff($prod);

			// Навешиваем действия на иконки
			togglePopup($prod);

			// Иконка закрытия попапчика
			$prod.find('.icon_close').click(function(){
				$prod.removeClass('open');
			});

			// Навешиваем редактирование
			$prod.find('.btn_edit').click(function(){
				toggleEdit($prod);
			});

			// Отвзяываение товара от фото
			$prod.find('.btn_delete').click(function(){
				if (confirm('Удалить?')) {
					var product_id = $(this).data('product_id');
					unbindProd(product_id, $prod);
				} else {
					return false;
				}

			});

			// Навешиваем подсвечивание товара в списке при наведении на метку
			hoverIconProducts();
		});
	};

	/**
	 * Инициализация добавления товаров на фотографию
	 */
	this.initAddingProd = function(param){

		$(selectorImage).click(function(event){

			model = param['model'];
			modelId = param['model_id'];

			$(selectorPictureWrapper).find('.new').remove();

			// Копируем шаблон для нового товара
			var html = $('#new_template').html();

			$prod = $(html);

			// Позиционируем
			$prod.css({
				'top': (event.offsetY - 15) +'px',
				'left': (event.offsetX - 15) +'px'
			});
			$prod.addClass('open').addClass('new');

			// Навешиваем на новый попапчик товара, закрытие попапчика
			$prod.find('.icon_close').click(function(){
				$(this).parents('.product').remove();
			});

			// Навешиваем на иконку привязки товара открытие/скрытие записи
			togglePopup($prod);

			// Навешивает действие на кнопку "НАЙТИ"
			$prod.find('.btn_search').click(function(){
				searchProduct($prod);
			});


			$prod.find('.product_id').keypress(function(event){
				if (event.keyCode == 13) {
					$prod.find('.btn_search').trigger('click');
				}
			});

			// --- ДОБАВЛЯЕМ ПОПАПИК В КОНТЕЙНЕР ---
			$(selectorPictureWrapper).append($prod);



			// Ставим фокус в input для ввода id товара
			$prod.find('input').focus();

			// Навешиваине таскиние мышкой
			drag($prod);


			// Переводим текущие координаты в проценты и записываем в папапчик
			var imageSize = getImageSize();

			var position = $prod.position();
			var perX = position.left * 100 / imageSize['width'];
			var perY = position.top * 100 / imageSize['height'];

			$prod.attr('data-top', perY).attr('data-left', perX);
		});
	};

	/**
	 * Устанавливает ID фотографии, к которой будем прикреплять товары.
	 * @param file_id ID фотографии
	 */
	this.setFileId = function(file_id){
		fileId = parseInt( file_id );
	};

	/**
	 * Инициализируем список товаров
	 */
	this.initList = function(){

		// Определяет ховеры над элементами-товарами в списке
		$(selectorListProducts).on({
			mouseover: function(){
				$(this).addClass('item_select');

				var product_id = $(this).attr('data-product_id');

				var original = $(selectorPictureWrapper).find('.product[data-product_id="'+product_id+'"]');
				original.find('.icon').addClass('icon_glow');
				var prod = original.clone();

				var html = prod.find('.product_img');
				$(selectorProductPreview).html(html).append(prod.find('.product_info'));



			},
			mouseout: function(){
				$(this).removeClass('item_select');

				$(selectorPictureWrapper).find('.icon_glow').removeClass('icon_glow');

				$(selectorProductPreview).html('');
			}
		}, '.prod_item');


		// Инициализация закрытия
		$(selectorListProducts).on('click', '.del_cross', function(){
			if (confirm('Удалить?')) {
				// Получаем ссылку на тег <li>
				var li = $(this).parents('li');

				// Открепляем товар
				var product_id = li.attr('data-product_id');
				$prod = $(selectorPictureWrapper).find('.product[data-product_id='+product_id+']');
				unbindProd(product_id, $prod);

				// Удаляем строку
				li.remove();

				$(selectorProductPreview).html('');
			} else {
				return false;
			}
		});
	};



	/* PROTECTED функции */

	/**
	 * Отправляет запрос на поиск товара
	 * @param $prod Попапчик для добавляемого товара
	 */
	function searchProduct($prod)
	{
		// ID найденного товара
		var product_id = $prod.find('.product_id').val();

		// Очищаем данные по товару при попытке нахожднеия товара
		$prod.find('.found_prod').html('');

		$.get(
			urlSearchProduct.replace(':id', product_id),
			function(data){

				if (data.success) {

					// Копируем шаблон для нового товара
					var html = $('#prod_template').html();
					var $tmpl = $(html);

					$tmpl.find('.product_img').prop('src', data.product['image']);
					$tmpl.find('.product_name').text(data.product['name']);
					$tmpl.find('.product_category').text(data.product['category']);
					$tmpl.find('.product_vendor').text(data.product['vendor']);

					if (data.product['type']) {
						$tmpl.find('.product_type').attr('checked', 1);
					}

					// Навшиваем на кнопку "ОТМЕНИТЬ" закрытие попапчика
					$tmpl.find('.btn_cancel').click(function(){
						$prod.find('.icon_close').trigger('click');
					});

					// Навешиваем на кнопку "ДОБАВИТЬ" прикрепление товара к фото
					$tmpl.find('.btn_ok').click(function(){
						bindProd(data.product['id'], $prod);
					});

					$prod.find('.found_prod').replaceWith($tmpl.find('.found_prod'));

				} else {
					$prod.find('.found_prod').html(data.errorMsg);
				}
			}, 'json'
		);
	}


	/**
	 * Прикрепляет товар к фотографии
	 * @param product_id ID Товара, который нужно прикрепить
	 * @param $product JQuery объект попапчика товара
	 */
	function bindProd(product_id, $prod)
	{
		var params = {'top': $prod.data('top')+'%', 'left': $prod.data('left')+'%'};
		var type = $prod.find('.product_type').attr('checked') == 'checked' ? 1 : 0;

		$.post(
			urlBindProduct,
			{
				file_id: fileId,
				product_id: product_id,
				params: params,
				model: model,
				model_id: modelId,
				type: type
			},
			function(data){
				if (data.success) {
					// Если все удачно прикрепилось, меняем внешний вид попапчика
					var html = $('#bind_prod').html();
					var $tmpl = $(html);

					// Переносим данные из предыдущего попапчика
					$tmpl.find('.product_img').prop('src', $prod.find('.product_img').prop('src'));
					$tmpl.find('.product_name').text($prod.find('.product_name').text());
					$tmpl.find('.product_category').text($prod.find('.product_category').text());
					$tmpl.find('.product_vendor').text($prod.find('.product_vendor').text());
					$tmpl.find('.product_type').attr('checked', type);

					// Меняем на новый вид
					$prod.find('.tmpl').replaceWith($tmpl);

					$prod.removeClass('new');

					$prod.find('.icon_close').unbind('click');
					$prod.find('.icon_close').click(function(){
						$(this).parents('.product').removeClass('open');
					});


					// Навешиваем Отвязываение товара от фото
					$prod.find('.btn_delete').click(function(){
						if (confirm('Удалить?'))
							unbindProd(product_id, $prod);
						else
							return false;
					});

					// Сохраняем id товара
					$prod.attr('data-product_id', product_id);

					dragOff($prod);

					// Навешиваем редактирование
					$prod.find('.btn_edit').click(function(){
						toggleEdit($prod);
					});

					// Вставляем товар в список
					$(selectorListProducts).append($(data.htmlRow));

					$prod.removeClass('open');
				} else {
					alert(data.errorMsg);
				}
			}, 'json'
		);
	}


	/**
	 * Отркрепляет товар от фотографии
	 */
	function unbindProd(product_id, $prod)
	{
		$.post(
			urlUnbindProduct,
			{
				file_id: fileId,
				product_id: product_id
			},
			function(data){
				if (data.success) {
					$prod.remove();
					var pid = $prod.attr('data-product_id');
					$(selectorListProducts).find('li[data-product_id='+pid+']').remove();
				} else {
					alert(data.errorMsg);
				}
			}, 'json'
		);
	}


	/**
	 * Навешивает события открытия/закрытия попапчика товара
	 * @param $prod Объект JQuery контейнера с товаром.
	 */
	function togglePopup($prod)
	{
		$prod.find('.icon').click(function(){
			// Закрываем все открытые попапчики
			if ( ! $prod.hasClass('open'))
				$(selectorPictureWrapper).find('.product.open').removeClass('open');

			$(this).parents('.product').toggleClass('open');
		});
	}

	function drag($prod)
	{
		$prod.draggable({
			scroll: false,
			drag: function(event, ui){

				// Отсуп справа и снизу
				var padding = 28;

				// Ограничиваем попапчик рамками фотографии
				var imageSize = getImageSize();

				if (ui.position.top <= 0)
					ui.position.top = 0;
				else if (ui.position.top >= imageSize['height']-padding)
					ui.position.top = imageSize['height']-padding;

				if (ui.position.left <= 0)
					ui.position.left = 0;
				else if (ui.position.left >= imageSize['width']-padding)
					ui.position.left = imageSize['width']-padding;


				// Переводим текущие координаты в проценты и записываем в папапчик
				var top = ui.position.top;
				var left = ui.position.left;

				if (top < 15)
					top = 15;
				if (left < 15)
					left = 15;

				var perX = left * 100 / imageSize['width'];
				var perY = top * 100 / imageSize['height'];

				$prod.attr('data-top', perY).attr('data-left', perX);
			}

		});
	}

	/**
	 * Включает перетаскивание объекта
	 * @param $prod
	 */
	function dragOn($prod)
	{
		$prod.draggable('enable');
	}

	/**
	 * Отключает перетакскивание объекта
	 * @param $prod
	 */
	function dragOff($prod)
	{
		$prod.draggable('disable');
	}


	/**
	 * Функция отвечает за изменение и сохранение положения объекта
	 * @param $prod
	 */
	function toggleEdit($prod)
	{
		var $btn = $prod.find('.btn_edit');
		if ($btn.hasClass('edit'))
		{
			dragOff($prod);
			$prod.removeClass('move');
			$btn.text('ПЕРЕМЕСТИТЬ').removeClass('primary').removeClass('edit');

			var params = {'top': $prod.attr('data-top')+'%', 'left': $prod.attr('data-left')+'%'};

			$.post(
				urlUpdateProduct,
				{
					file_id: fileId,
					product_id: $prod.data('product_id'),
					params: params,
					type: $prod.find('.product_type').attr('checked') == 'checked' ? 1 : 0
				},
				function(data){
					if (data.success) {
						$prod.find('.icon_close').click();
					} else {
						alert(data.errorMsg);
					}
				}, 'json'
			);
		}
		else
		{
			dragOn($prod);
			$prod.addClass('move');
			$btn.text('СОХРАНИТЬ').addClass('primary').addClass('edit');
		}

	}

	function getImageSize()
	{
		if (imageWidth === undefined) {
			imageWidth = $(selectorImage).width();
			imageHeight = $(selectorImage).height();
		}

		return {width: imageWidth, height: imageHeight};
	}

	function hoverIconProducts()
	{
		// Определяет ховеры над иконками товаров
		$(selectorPictureWrapper).on({
			mouseover: function(){
				$(this).addClass('.icon_glow');

				var $product = $(this).parents('.product');
				var product_id = $product.attr('data-product_id');

				var prod = $product.clone();

				var html = prod.find('.product_img');
				$(selectorProductPreview).html(html).append(prod.find('.product_info'));

				$(selectorListProducts).find('li[data-product_id='+product_id+']').addClass('item_select');
			},
			mouseout: function(){
				$(selectorListProducts).find('.item_select').removeClass('item_select');

				$(selectorProductPreview).html('');
			}
		}, '.product .icon');
	}
}