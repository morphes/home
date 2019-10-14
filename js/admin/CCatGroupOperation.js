var groupOperation = new CCatGroupOperation();

/**
 * Класс для реализации функций работы с корзиной товаров для групповых операций.
 */
function CCatGroupOperation()
{
	var self = this;

	/**
	 * Инициализирует кнопку добавления товара в корзину
	 *
	 * @param class_btn Имя класса кнопки для добавления
	 */
	this.initAddButtons = function(class_btn){
		$('body').on('click', '.'+class_btn, function(){
			var link = $(this);
			var isAdded = link.hasClass('added_cart');
			var href = link.attr('href');

			$.post(
				href,
				{
					action: (isAdded) ? 'delete' : 'add'
				},
				function(response){
				if (response.success) {
					// Если все ОК, меняем картинку
					if (isAdded) {
						link.find('img').attr('src', '/img/admin/small/to_cart.png');
						link.removeClass('added_cart');
					} else {
						link.find('img').attr('src', '/img/admin/small/in_cart.png');
						link.addClass('added_cart');
					}

					self.showSizeCart();
				} else {
					alert(response.error);
				}
			}, 'json');

			return false;
		});
	};

	this.showSizeCart = function(){
		$.get(
			'/catalog/admin/groupOperation/getSizeCart',
			function(response){
				if (response.success) {
					$('#product_cart').find('.qt').text(response.qt);
				}
			}, 'json'
		);
	};
}