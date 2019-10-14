$(document).ready(function () {
	/**
	 * календарь
	 */
	if($("#tender_date").length){
		$("#tender_date").datepicker({
			showOn:"focus",
			buttonImage:"/img/calendar_icon.png",
			buttonImageOnly:true,
			dateFormat:"dd.mm.yy",
			dayNamesMin:["Вс", "Пн", "Вт", "Ср", "Чт", "Пт", "Сб"],
			monthNames:["Январь", "Февраль", "Март", "Апрель", "Май", "Июнь", "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь"],
			nextText:" ",
			prevText:" ",
			firstDay:1,
			onSelect : function(dateText, inst)
			{
				var epoch = $.datepicker.formatDate('@', $(this).datepicker('getDate')) / 1000+10800;
				$('[name="Tender[expire]"]').val(epoch);
			}
		});
		$("#tender_date").next().click(function () {
			$("#tender_date").trigger('focus');
		});
	}
	
	if ($('.image_uploaded').length) {
		/**
		* редактирование описания загруженного(выбранного) файла
		*/
		var description = "";
		$('.image_uploaded').on({
			click:function () {
				var span = $(this);
				var textarea = span.next();
				var h = span.height() + 20;
				var w = span.width() + 20;
				description = span.text();

				span.addClass('hide');
				textarea.removeClass('hide').height(h).width(w).focus().select();
			}
		}, '.file_description span');

		$('.image_uploaded').on({
			focusout:function () {
				fileDescriptionEdit($(this), $(this).val());
			},
			keydown:function (e) {
				if (e.keyCode == 27) {
					fileDescriptionEdit($(this), description);
				}
			}
		}, '.file_description textarea');
		
		function fileDescriptionEdit(textarea, val) {
			var span = textarea.prev();
			var inputval = textarea.val();

			var regexp = /[A-Za-zА-яа-я0-9_]/;

			if (val.length > 0) {
				if (regexp.test(val)) {
					var newValue = val;
					textarea.val(val);
				} else {
					var newValue = description;
					textarea.val(description);
				}

			} else {
				var newValue = (inputval) ? inputval : "Добавить описание";
				textarea.val();
			}
			span.text(newValue).removeClass('hide');
			textarea.addClass('hide');
		}
	}
	
});