/**
 * Объект генерит две кнопки вверх/вниз
 * для перемещения позиции элементов в списке.
 */
var arrowsUpDown = {
	selectId:0,
	mouseX:0,
	mouseY:0,
	urlAction:undefined,
	gridId:undefined,
	/**
	 * Отображает ссылки вверх/вниз
	 */
	showArrows:function () {
		$("div.adminArrows").remove();
		if ($("tr.selected").size() == 1) {
			$("<div class=\"adminArrows\"><div class=\"up btn small success\">выше</div> <div style=\"height:3px\"></div> <div class=\"down btn small danger\">ниже</div></div>").appendTo("body");
		}
	},
	/**
	 * Инициализируем перемещатель положения записей в таблице
	 *
	 * @param gridId Идентификатор виджета gridView, который будем обновлять
	 * @param urlAction Первая часть URL метода, который будет вызываться для перемещения.<br>
	 *         Общий вид ссылки: urlAction/[up|down]/id/[0-9]
	 * @return {Boolean}
	 */
	init:function (gridId, urlAction) {
		if (urlAction === undefined || gridId === undefined) {
			alert('Ошибка инициализации изменятеля позиций!');
			return false;
		} else {
			this.urlAction = urlAction;
			this.gridId = gridId;
		}

		this._initClick();
		this._initGetCursor();
	},
	/**
	 * Выбирает строку с ID, записанным в selectId
	 */
	selectLastElement:function () {
		$("td.elementId:contains(" + this.selectId + ")").parent("tr").trigger("click");
	},
	/**
	 * Получает ID выбранной строки и сохраняет его в selectId
	 */
	getSelectedElement:function () {
		this.selectId = parseInt($('tr.selected').find('.elementId').text());
		return this.selectId;
	},

	moveToCursor:function () {

		var newX = this.mouseX + 30;
		var newY = this.mouseY - 35;

		$("div.adminArrows").css({
			'top':newY + 'px',
			'left':newX + 'px'
		});
	},

	/**
	 * Фукнция запускает отслеживание клика.
	 * При каждом клике записываем положение мыши в переменные.
	 */
	_initGetCursor:function () {
		self = this;

		$(document).on('mousedown', 'table', function (event) {
			self.mouseX = event.clientX;
			self.mouseY = event.clientY;
		});
	},

	_initClick:function () {
		self = this;
		$(".adminArrows .up, .adminArrows .down").live("click", function () {
			var act = ''; // Действие вверх/вниз
			if ($(this).hasClass('up'))
				act = 'up';
			else if ($(this).hasClass('down'))
				act = 'down';
			else
				return false;

			$.fn.yiiGridView.update(self.gridId, {
				type:"POST",
				url:self.urlAction + act + "/id/" + self.getSelectedElement(),
				success:function () {
					$.fn.yiiGridView.update(self.gridId);
				}
			});
		});
	}
};

