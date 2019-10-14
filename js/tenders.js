$(document).ready(function () {


	$('.compare_offer input:radio').change(function(){
		if($(this).val() == 1){
			$('.compare_offer.hide').show();
		}else{
			$('.compare_offer.hide').hide();
		}
	})

	/**
	 * редактирование описания загруженного(выбранного) файла
	 */
	var description = "";
	$('.image_uploaded').on({
		click:function () {
			var span = $(this);
			var textarea = span.next();
			var h = span.height() + 10;
			description = span.text();

			span.addClass('hide');
			textarea.removeClass('hide').height(h).focus().select();
		}
	}, '.uploaded_files_description span');

	$('.image_uploaded').on({
		focusout:function () {
			fileDescriptionEdit($(this), $(this).val());
		},
		keydown:function (e) {
			if (e.keyCode == 27) {
				fileDescriptionEdit($(this), description);
			}
		}
	}, '.uploaded_files_description textarea');

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

	/**
	 * расчет количества оставшихся знаков в поле описания тендера
	 * char_cnt - количество символов
	 * */
	if($('#tender_descript').length){

		var char_cnt = 3000;
		var chars_conteiner = $('#tender_descript').prev().find('span');
		var char = parseInt(chars_conteiner.text());
		chars_conteiner.text(char_cnt - $('#tender_descript').val().length);

		$('#tender_descript').keyup(function () {
			chars_conteiner.text(char_cnt - $(this).val().length);
		});
	}



	/**
	 * закрытие тендера
	 */
	$('.tender_list').on({
		click: function () {
			var item = $(this).parents('.item');
			var id = item.attr('id');
			var respContainer = $(this).parents('.respond');
			doAction({
				'yes':function(){
					respContainer.html('<div class="loading" style="height:' + 25 + 'px"></div>');
					$.ajax({
						url:"/tenders/profile/tenderclose/id/"+id,
						type: "post",
						dataType: "json",
						async: true,
						success: function(response) {
							if (response.success) {
								respContainer.html(response.html);
								item.addClass('closed');
							}
							if (response.error){
								window.reload();
							}
						},
						error: function() {
							window.reload();
						}
					});
				},
				no: function(){

				}
			}, 'Закрыть заказ?');
			return false;
		}
	},'.close_tender');


	$('.tender_list').on({
		click: function () {
			var checkbox = $(this);
			var respond = $('.shadow_block.tender_respond').clone();
			if(!checkbox.is(':checked')){
				checkbox.next().fadeOut(100);
				checkbox.removeClass('checked');
				if(checkbox.is('.added')){
					tenderAction($(this),"del")
				}
			}else{
				if(!checkbox.next().is('.tender_respond'))
					respond.insertAfter(checkbox);
				checkbox.addClass('checked');
				checkbox.next().fadeIn();
			}
		}
	},'.respond input[type="checkbox"]');

	/**
	 * добавление отклика на тендер со страницы списка тендеров
	 */

	$('.tender_list').on({
		click: function () {
			var respBox = $(this).parents('.tender_respond');
			var commentConteiner = respBox.find('textarea');
			var commentVal = commentConteiner.val();
			var button = $('.btn_conteiner.tender_add_button');
			var error = validateComment(commentVal,commentConteiner);
			if(!error){
				tenderAction($(this),"add");
			}
		}
	},'.btn_grey');


	/**
	 * удаление отклика
	 */
	$('.tender_list').on({
		click: function () {
			tenderAction($(this),"del");
			
		}
	},'.reject_tender');


	/**
	 * функция добавления/удаления отзыва к тендеру.
	 * id		 - id тендера
	 * message	 - Комментарий к отклику
	 * budjet	 - ориентировочная стоимость
	 * action	 - действие(добавить(по умолчанию)/удалить)
	 */
	function tenderAction(obj,action){
		if(action===undefined){
			action = "add";
		}
		var respBox = obj.parents('.tender_respond');
		var respContainer = obj.parents('.respond');
		var cost = respBox.find('input').val();        //ориентировочная стоимость
		var message = respBox.find('textarea').val();    //Комментарий к отклику
		var id = obj.parents('.item').attr('id');    // id тендера
		var quatConteiner = obj.parents('.item').find('.response');

		respContainer.html('<div class="loading" style="height:' + 25 + 'px"></div>');
		if(action=="add"){
			html = '<a class="reject_tender" href="#">Отказаться от участия</a><br><a class="my_respond" href="/tenders/'+id+'/">Мой отклик</a>';

		}else{
			html = '<input class="" type="checkbox">';
		}
		
		$.ajax({
			url:"/tenders/profile/tenderresponse/id/"+id,
			data: {'action':action, 'cost':cost, 'content':message},
			type: "post",
			dataType: "json",
			async: true,
			success: function(response) {
				if (response.success) {
					respContainer.html(html);
					if (action=='add')
						 quantCounter(quatConteiner,'plus');
					else 
						 quantCounter(quatConteiner,'minus');
				}
				if (response.error){
					window.reload();
				}
			},
			error: function() {
				window.reload();
			}
		});
	}


	/**
	 * обработка табов, загрузка всех, открытых и закрытых тендеров
	 * action = all	   - все;
	 * action = opened - открытые;
	 * action = closed - закрытые;
	 */
	$('#tabs').on({
		click:function(){

			var action = $(this).attr('data-value');
			var conteiner = $('.tender_list');
			var section = $('.menu_level2 li.current').attr('data-value');

			$('#tabs span').removeClass('current');
			$(this).parent().addClass('current');

			conteiner.html('<div class="loading" style="height:25px;margin-top:40px"></div>');
			
			$.ajax({
				url:"/tenders/profile/tenderlist",
				data: {'action':action, 'section':section},
				type: "post",
				dataType: "json",
				async: true,
				success: function(response) {
					if (response.success) {
						conteiner.html(response.html);
					}
					if (response.error){
						window.reload();
					}
				},
				error: function() {
					window.reload();
				}
			});
			
			return false;
		}
	}," span:not(.current) a");


	/**
	 * Добавление отклика к тендеру на странице тендера
	 */
	 $('.tender_add_button').click(function () {
		var self = $(this);	
		if(self.children().hasClass('-guest')){
			$('#popup-message-guest').modal({
				overlayClose:true
			});

		}else{
			$('.tender_respond.tender_page').slideDown();
			return false;
		}
		if(self.is('.red')){
			var span = $('.responds span');
			var id = self.attr('data-value'); //id тендера
			/*аякс*/
			doAction({
				'yes':function(){
					span.html('<img style="margin-left:20px;" src="/img/loader.gif">');
			
					$.ajax({
						url:"/tenders/profile/tenderclose/id/"+id,
						type: "post",
						dataType: "json",
						async: true,
						success: function(response) {
							if (response.success) {
								span.addClass('allready_respond').text('Заказ закрыт');
								span.parent().addClass('tender_closed');
								$('#left_side .edit_tender').remove();
								self.remove();
							}
							if (response.error){
								window.reload();
							}
						},
						error: function() {
							window.reload();
						}
					});
				},
				no: function(){

				}
			}, 'Закрыть заказ?');
		}
		return false;

	});

	$('.tender_page').on('click','.btn_grey',function(){
		var self = $(this);
		
		var respBox = self.parents('.tender_respond');
		var commentConteiner = respBox.find('textarea');
		var commentVal = respBox.find('textarea').val();
		var budgetVal = respBox.find('input.textInput').val();
		var button = $('.btn_conteiner.tender_add_button');

		var error = validateComment(commentVal,commentConteiner);


		if(!error){
			var span = $('.responds span');
			var comments = $('.tender_comments');
			var h5 = comments.find('h5');
			var id = button.attr('data-value'); //id тендера

			/*аякс*/
			span.html('<img style="margin-left:20px;" src="/img/loader.gif">');
			
			$.ajax({
				url:"/tenders/profile/tenderresponse/id/"+id,
				data: {'cost':budgetVal,'content':commentVal,'action':'add','html':true},
				type: "post",
				dataType: "json",
				async: true,
				success: function(response) {
					if (response.success) {
						span.addClass('allready_respond').text('Вы откликнулись');
						if (response.html)
							comments.append(response.html);
						if (response.count)
							h5.html(response.count);
						comments.removeClass('hide');
						
						respBox.slideUp();
						button.addClass('hide');
					}
					if (response.error){
						location.reload();
					}
				},
				error: function() {
					location.reload();
				}
			});
			
		}

	});

	/*удаление своего коммента на странице тендера*/
	$('.tender_comments').on({
		click:function(){
			var button = $('.btn_conteiner.tender_add_button');
			var span = $('.responds span');
			
			var conteiner = $(this).parent();
			var item = conteiner.parents('.tender_comment');
			var id = item.attr('data-value');
			conteiner.html('<img style="margin-top:10px;" src="/img/loader.gif">');
			
			$.ajax({
				url:"/tenders/profile/tenderresponse/id/"+id,
				data: {'action':'del', 'html':true},
				type: "post",
				dataType: "json",
				async: true,
				success: function(response) {
					if (response.success) {
						item.remove();
						$('.tender_comments h5').text(response.count);
						if (!$.isEmptyObject(button)) { // for closed tenders
							span.removeClass('allready_respond').text(response.countFull);
							button.removeClass('hide');
						}
						if(!$('.tender_comments h5').next().is('.tender_comment')){
							$('.tender_comments').addClass('hide');
						}
					}
					if (response.error){
						location.reload();
					}
				},
				error: function() {
					location.reload();
				}
			});
			return false;
		}
	},'.del_comment');


	/**
	 * редактирование своего комментария
	 */
	var description = "";
	$('.tender_comments').on({
		click:function () {
			var span = $(this);
			var textarea = span.next();
			var h = span.height() + 10;
			description = span.text();
			span.addClass('hide');
			textarea.removeClass('hide').height(h).focus().select();
		}
	}, 'span.can_edit');
	
	$('.tender_comments').on({
		focusout:function () {
			fileDescriptionEdit($(this), $(this).val());
		},
		keydown:function (e) {
			if (e.keyCode == 27) {
				fileDescriptionEdit($(this), description);
			}
		}
	}, '.tender_comment_text textarea');

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
		if(span.parent().is('.tender_comment_text')){
			var id = span.parents('.tender_comment').attr('data-value');
			$.ajax({
				url:"/tenders/profile/tenderresponse/id/"+id,
				data: {'action':'edit', 'content':textarea.val()},
				type: "post",
				dataType: "json",
				async: true,
				success: function(response) {
					if (response.success) {}
					if (response.error){
						location.reload();
					}
				},
				error: function() {
					location.reload();
				}
			});
		}
		span.text(newValue).removeClass('hide');
		textarea.addClass('hide');
	}

	$('.tender_comments').on({
		click:function(){
			var a = $(this);
			var hiden =  $(this).prev();
			if(hiden.is(':visible')){
				hiden.hide();
				hiden.prev().show();
				a.text('раскрыть');
			}else{
				hiden.show();
				hiden.prev().hide();
				a.text('скрыть');
			}
			return false;
		}
	},'.show_more')
});

function validateComment(commentVal,obj){
	var errors =false;
	$('.validate_error_text').remove();
	if(!commentVal.length>0){
		obj.addClass('validate_error');
		obj.after("<span class='validate_error_text'>Введите комментарий</span>");
		errors = true;
	}else{
		obj.removeClass('validate_error');
		obj.next("span.validate_error_text").remove();
		errors =false;
	}

	return errors;
}

var tender = new CTender();

function CTender(){
	var self = this;

	this.changeTenderDate = function(){
		var container = $('.tender_close');
		var span = container.find('.exp_current');
		var curDate = span.html();

		container.find('a').click(function(){
			if(span.hasClass('disabled')){
				span.removeClass('disabled');
				$(this).html('Не изменять дату<i></i>');
			}else{
				span.addClass('disabled').html(curDate);
				$(this).text('Изменить дату');
				container.find('input').val('');
			}

			return false;
		});
	};


	this.removeErrorClass = function(){
		$('.textInput.error').focus(function(){
			$(this).removeClass('error')
		})
	}
}
