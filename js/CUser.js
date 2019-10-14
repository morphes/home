var user = new CUser({});

function CUser(options){
	var self = this;
	this._options = {
		'userId':0
	};

	this.slider = function(){
		/*автоматическая смена слайдов*/
		var intervalID = setInterval(function(){
			if($('.slide_item.current').next().is('.slide_item')){
				var next = $('.slide_item.current').next();
			}else{
				var next = $('.slide_item:eq(0)');
			}
			next.trigger('click','trigger');
		}, 5000);

		$('.slide_item').click(function(e,type){
			if(type===undefined){
				clearInterval(intervalID);
			}
			var item = $(this);
			var picId = item.attr('data-image');
			var imgConteiner = $('.slider_imgs');

			if(!$(this).hasClass('current')){
				$('.slider_control .slide_item').removeClass('current pre_current');
				item.addClass('current');
				item.prev().addClass('pre_current');
				imgConteiner.find('div.visible').stop().animate({opacity:0},900).removeClass('visible');
				imgConteiner.find('div#img_'+picId).stop().animate({opacity:1},700).addClass('visible');

				return false;
			}

		});
		self.aboutToggle();
	};

	this.aboutToggle = function(){
		var p = $('#about p');
		var h = p.height();
		if(h>105){
			p.addClass('closed');
			$('#about .all_elements_link').show();
		}
		$('#about .all_elements_link a').click(function(){

			if(!$(this).hasClass('open')){
				p.removeClass('closed');
				$(this).addClass('open');
				$(this).next().html('&uarr;');
			}else{
				p.addClass('closed');
				$(this).removeClass('open')
				$(this).next().html('&darr;');
			}
			return false;
		})
	};

	this.initComments = function(){
		self._delComment();
		self._editComment();
		self._showRules();
	};
	this.initAnswers = function(){
		self._answerComment();
		self._answerCommentEdit();
		self._answerCommentDel();
	};
	this.markToggler = function(){
		$('.comments form>label').live('click',function(){
			var item = $(this).parents('.review_comment');
			var container = item.find('.review_comment_container');
			var recomend = item.find('.recomended');
			if($(this).hasClass('bad')){
				container.addClass('bad');
				recomend.addClass('disabled').find('input').attr({'disabled':'disabled','checked':false});

			}else{
				container.removeClass('bad');
				recomend.removeClass('disabled').find('input').attr('disabled',false);
			}
		})
	};
	/*удаление комментария/отзыва пользователя*/
	this._delComment = function(){
		$('.reviews').on({
			click:function(){
				var item = $(this).parents('.item');
				var id = item.attr('data-id');
				var url = '/member/review/delete';
				$.ajax({
					async:false,
					url:url,
					data:{'action':'deleteComment', 'userId':self._options.userId, 'reviewId':id},
					dataType:'json',
					type:'post',
					success:function(response){
						if (response.success) {
							item.html("<span class='review_type'>Отзыв удалён</span>");
							item.removeClass('bad, good').addClass('deleted').fadeOut(3000, window.reload());
						}
						if (response.error){
							window.reload();
						}
					},
					error:function(){
						window.reload();
					}
				});
				return false;
			}
		},'.item_tools.review .del');
	};

	/*редактирование комментария/отзыва */
	this._editComment = function(){
		$('.reviews').on({
			click:function(){
				var action = 'edit_comment';
				var item = $(this).parents('.item');
				var itemMark = item.attr('data-mark');
				var recomended = parseInt(item.attr('data-recomended'));
				var body = item.find('.review_body');
				var text = body.find('>.item_text');
				var form = $('.comment_form .comments').clone();

				body.hide();
				item.removeClass('bad good').addClass('edited');

				form.find('textarea').val(br2nl(text.find('p').html()));
				if(itemMark =="good"){
					form.find('.good input').attr('checked', 'checked');
				}else{
					form.find('.bad input').attr('checked', 'checked');
					form.find('.review_comment_container').addClass('bad');
				}
				if(recomended){
					form.find('.recomended input').attr('checked', true);
				}else{
					form.find('.recomended input').attr('checked', false);
				}
				item.append(form);

				self.submitComment(form, action);
				self._cancelEdit();
				//form.remove();
				return false;
			}
		},'.item_tools.review .edit');
	};
	this._showRules = function(){
		$('.review_rules').click(function(){
			$('#popup-rules').modal({
				overlayClose:true
			});
			return false;
		});
	};
	/*добавление ответа на отзыв*/
	this._answerComment = function(){
		$('.reviews').on({
			click:function(){
				var action = 'add_answer';
				var item = $(this).parents('.item');
				var body = item.find('.review_body');
				var form = $('.answer_form .item_answer').clone();

				item.find('.item_tools').hide();


				item.append(form);
				self._cancelEdit();
				self.submitComment(form,action);
				return false;
			}
		},'.item_tools.review .answer');
	};

	/*редактирование ответа на отзыв*/
	this._answerCommentEdit = function(){
		$('.reviews').on({
			click:function(){
				var action = 'edit_answer';
				var item = $(this).parents('.item_answer');
				var body = item.find('.review_body');
				var text = item.find('.item_text');
				var form = $('.answer_form .item_answer').clone();
				form.find('.item_head').remove();
				form.removeClass('item_answer');
				form.find('textarea').val(br2nl(text.find('>p').html()));
				form.find('input.btn_grey').attr('data-action','edit_answer');
				body.hide();

				item.find('.item_head').after(form);

				self._cancelEdit();
				self.submitComment(form,action);

				return false;
			}
		},'.item_answer .edit');
	};

	this._answerCommentDel = function(){
		$('.reviews').on({
			click:function(){

				var item = $(this).parents('.item_answer');
				var id = item.find('.review_body').attr('data-id');
				var url = '/member/review/delete';
				$.ajax({
					async:false,
					url:url,
					data:{'action':'deleteAnswer', 'userId':self._options.userId, 'reviewId':id},
					dataType:'json',
					type:'post',
					success:function(response){
						if (response.success) {
							item.html("<span class='review_type'>Ответ удалён</span>");
							item.addClass('deleted').fadeOut(3000);
						}
						if (response.error){
							window.reload();
						}
					},
					error:function(){
						window.reload();
					}
				});
				return false;
			}
		},'.item_answer .del');
	};

	/*Событие на ссылке "отменить редактирование"*/
	this._cancelEdit = function(){
		$('.cancel_edit').live('click',function(){
			var item = $(this).parents('.item');
			$(this).parents('.comments').remove();
			item.removeClass('edited').addClass(item.attr('data-mark'));
			item.find('.review_body').show();
			item.find('.item_tools').show();
			return false;
		})
	};
	/**
	 * Сохранение отзывов и ответов к ним
	 * @param obj 	 - форма, данные из которой нужно сохраннить
	 * @param action - экшен(сохранить комментарий, сохранить ответ, добавить ответ и тп), исходя из которого выбирается дальнейшее поведение скрипта
	 * @private
	 */
	this.submitComment = function(obj,action){
		obj.find('input.btn_grey').click(function(){
			if(action=="add_answer"){
				var id= obj.parents('.item').attr('data-id'); //id отзыва на который отвечает автор
				var tools = obj.parents('.item').find('.item_tools.review');
				var text = nl2br(obj.find('textarea').val());
				var form = obj.find('.form');
				var url = '/member/review/create';//урл для обработки
				var tools = obj.parents('.item').find('.item_tools.review');


				$.ajax({
					async:false,
					url:url,
					data:{'action':'createAnswer', 'userId':self._options.userId, 'message':text, 'reviewId':id},
					dataType:'json',
					type:'post',
					success:function(response){
						if (response.success) {
							form.remove();
							obj.removeClass('comments');
							tools.remove();
							obj.find('.item_head').after(response.html);
						}
						if (response.error){
							obj.find('.error-title').html(response.message).show();
						}
					},
					error:function(){
						window.reload();
					}
				});

			}
			if(action=="edit_answer"){
				var parent = obj.parents('.item_answer');
				var review = parent.find('.review_body')
				var id =  review.attr('data-id'); //id ответа, который редактирует автор
				var text = nl2br(obj.find('textarea').val());
				var form = obj.find('.form');
				var url = '/member/review/create';//урл для обработки

				$.ajax({
					async:false,
					url:url,
					data:{'action':'updateAnswer', 'userId':self._options.userId, 'message':text, 'reviewId':id},
					dataType:'json',
					type:'post',
					success:function(response){
						if (response.success) {
							obj.remove();
							review.find('p').html(text);
							review.show();
						}
						if (response.error){
							obj.find('.error-title').html(response.message).show();
						}
					},
					error:function(){
						window.reload();
					}
				});

			}
			if(action=="edit_comment"){
				var parent = obj.parents('.item');
				var review = parent.find('.review_body');
				var reviewText = parent.find('.review_body .item_tools').prev('p');
				var id = parent.attr('data-id'); //id ответа, который редактирует автор
				var text = nl2br(obj.find('textarea').val());
				var mark = obj.find('input[name="mark"]:checked').val();
				var recommend = obj.find('input[name="recomended"]:checked').val();
				var url = '/member/review/create';//урл для обработки

				$.ajax({
					async:false,
					url:url,
					data:{'action':'updateReview', 'userId':self._options.userId, 'message':text, 'reviewId':id, 'mark':mark, 'recommend':recommend},
					dataType:'json',
					type:'post',
					success:function(response){
						if (response.success) {
							obj.remove();
							reviewText.html(text);
							parent.removeClass('edited');
							if(mark>0)
								parent.addClass('good').attr('data-mark','good');
							else
								parent.addClass('bad').attr('data-mark','bad');
							if(recommend!==undefined){
								parent.attr('data-recomended','1');
								parent.append('<div class="mark"><i></i><span>Рекомендую!</span></div>');
							}else{
								parent.attr('data-recomended','0');
								parent.find('.mark').remove();
							}
							review.show();
						}
						if (response.error){
							obj.find('.error-title').html(response.message).show();
						}
					},
					error:function(){
						window.reload();
					}
				});

			}

			if(action=="add_comment"){
				var text = obj.find('textarea').val();
				var mark = obj.find('input[name="mark"]:checked').val();
				var recomend = (obj.find('input[name="recomended"]:checked').val()==1) ? 1 : 0;
				var url = '/member/review/create';

				$.ajax({
					async:false,
					url:url,
					data:{'action':'createReview', 'userId':self._options.userId, 'message':text, 'mark':mark, 'recommend':recommend},
					dataType:'json',
					type:'post',
					success:function(response){
						if (response.success) {
							obj.after(response.html);
							obj.html('<div class="shadow_block white padding-18"><span class="review_added">Ваш отзыв добавлен!</span></div>')
						}
						if (response.error){
							obj.find('.error-title').html(response.message).show();
						}
					},
					error:function(){
						window.location.reload();
					}
				});
			}

		});
	};

	this.setOptions = function(options){
		$.extend(true, this._options, options)
	};

	/**
	 * Функция для сортировки проектов в портфолио
	 * @param serviceId - id услуги проектов
	 */
	this.sortPortfolioProjects = function(serviceId){
		$('.sort i.close').click(function(){
			$.ajax({
				async:true,
				url:'/member/profile/axHideFlash',
				dataType:'json',
				type:'post',
				success:function(response){},
				error:function(){}
			});
			$(this).parent().slideUp();
		});

		$( ".user_portfolio" ).sortable({
			dropOnEmpty: false,
			revert: 150,
			tolerance: "pointer",
			update: function( event, ui ) {
				var itemData = ui.item.data(),
				data={'item_id':itemData.id, 'type_id':itemData.type, 'service_id':serviceId, 'position':ui.item.index()+1};
				$.ajax({
					async:false,
					url:'/member/profile/moveItem',
					data:data,
					dataType:'json',
					type:'post',
					success:function(response){},
					error:function(){
						window.location.reload();
					}
				});
			}
		}).disableSelection();
	};

	/**
	 * Функция для сортировки баннеров в ЛК БМ
	 */
	this.sortPortfolioBanners = function(serviceId){
		$('.bm_promo_list > .item').on('change', ':checkbox', function(e){
			var checked = $(this).is(':checked') ? 1 : 0 ,
			    item = $(e.delegateTarget);

			$.ajax({
				async: true,
				url: '/catalog/mall/ajaxHideBanner',
				dataType: 'json',
				data:{active:checked, item_id:item.data().id},
				type: 'post',
				success: function(response){
					if (checked==1) {
						item.removeClass('off');
					} else {
						item.addClass('off');
					}
				},
				error: function(){}
			});
		}).on('click', '.-icon-cross-circle-xs', function(e){
			var item = $(e.delegateTarget);
			CCommon.doAction({
				'yes':function(){
					$.ajax({
						async: true,
						url: '/catalog/mall/ajaxRemoveBanner',
						data:{item_id:item.data().id},
						dataType: 'json',
						type: 'post',
						success: function(response){
							item.fadeOut('fast', function(){
								$(this).remove();
							});
						},
						error: function(){}
					});
				},
				'no':function(){return false;}
			}, 'Удалить баннер?');

			return false;
		});

		$( ".bm_promo_list" ).sortable({
			dropOnEmpty: false,
			revert: 150,
			tolerance: "pointer",
			items: "div.item:not(.new_item)",
			update: function( event, ui ) {
				var itemData = ui.item.data();
				console.log(itemData);
				data={'item_id':itemData.id, 'position':ui.item.index()+1};
				$.ajax({
					async:false,
					url:'/catalog/mall/ajaxMoveItem',
					data:data,
					dataType:'json',
					type:'post',
					success:function(response){},
					error:function(){ window.location.reload(); }
				});
			}
		}).disableSelection();
	};

	/**
	 * Отображение попапа добавления/редактирования баннера
	 * @param id - id баннера для редактирования
	 */
	this.bannerEditForm = function(id) {

		if (typeof id !='undefined') {
			$.ajax({
				async:false,
				url:'/catalog/mall/ajaxGetItem',
				data:{item_id:id},
				dataType:'json',
				type:'post',
				success:function(response){
					if (response.success) {
						$('#popup').html(response.html);
						$('#popup > form').modal({
							overlayClose: true,
							obshow: function(){}
						});
					}
				},
				error:function(){ window.location.reload(); }

			});
		} else {
			$('.banner-edit-form').modal({
				overlayClose: true,
				onShow: function(){}
			});
		}

		return false;
	}

	/*выбор города в услугах*/
	this.selectCity = function(){
		var container = $('.my_city_list'),
			addCity = container.find('.city_add_list');
		_deleteCity();
		_addCity();

		function _deleteCity(){
			var delCity = container.find('li');
			container.on('click', 'li span', function(){
				var data = $(this).data();
				$(this).parent('li').remove();
				$.ajax({
					url:"/member/profile/updatecity/action/delete/type/" + data.locationType + "/id/" + data.locationId,
					async:false,
					dataType:'json',
					success:function (data) {
						if (data.success) {
							$(this).parent('li').remove();
						}
					}
				});
			});

		}

		function _addCity(){
			var selectorContainer = $('#city-select'),
				selectHead = selectorContainer.find('.city-select-header .area');
			addCity.on('click',function(){
				selectHead.text('страну');
				$('.city-select-body').css('left',0);
				$('.city-select-body li').removeClass('current');

				$('#city-select').modal({
					overlayClose:true
				});
				return false;
			});

			selectorContainer.on('click','.city_list li',function(){
				var li = $(this),
					list = li.parents('.city_list'),
					region = selectorContainer.find('#region'),
					city = selectorContainer.find('#city'),
					data = li.data(),
					input = $('#reg_1');
				list.find('li').removeClass('current');
				li.addClass('current');

				switch (list.attr('id')){
					case 'country':
						selectHead.text('регион');
						$.ajax({
							url: "/member/profile/loadchildcitys/for/country/id/" + data.locationId,
							async: false,
							success: function( data ){
								region.html( data );
							}
						});
						break;
					case 'region':
						selectHead.text('город');
						$.ajax({
							url: "/member/profile/loadchildcitys/for/region/id/" + data.locationId,
							async: false,
							success: function( data ){
								$("#city").html( data );
							}
						});
				}

				if (!li.hasClass('select_all') && list.attr('id') != "city") {
					$('.city-select-body').stop().animate({left: "-=378"}, 250);
				}
				var name = list.attr("id");


				if (li.hasClass('select_region')) {

					input.attr('name', "all_cities").val(li.text());
					input.attr('data-location-id', data.locationId);

				} else if (!li.hasClass('select_all')) {

					input.attr('name', name).val(li.text());
					input.attr('data-location-id', data.locationId);
				}
			});
			
			$('.back_to_change').click(function(){
				$(this).addClass('current');
				$('.city-select-body').stop().animate({left:"+=378"},250);
				if($(this).prev().prev().is("#region")){
					selectHead.text('страну');
				}
				if($(this).prev().prev().is("#city")){
					selectHead.text('регион');
				}
				return false;
			});
		}
	};

	this.messageManage = function(){
		var list = $('.messages-list');
		list.on('click','.remove',function(){
			var link = $(this);
			$.ajax({
				url: "/member/message/delete",
				data: "id="+$(this).data('id'),
				async: false,
				success: function(data){
					if(data == "ok"){
						link.parents('.item').remove();
					}
				}
			});
			return false;
		});

		list.on('click','.spam',function(){
			var action;
			if($(this).hasClass('-icon-spam-xs')){
				$(this).removeClass('-icon-spam-xs').text('Это не спам');
				action = 'addMessageToSpam';
			}else{
				action = 'deleteMessageFromSpam';
				$(this).addClass('-icon-spam-xs').text('Это спам');

			}

			$.ajax({
				url: "/member/message/"+action,
				data: "id="+$(this).data('id'),
				async: false

			});

			$(this).parents('.item').toggleClass('-disabled');
			return false;
		});
	};
}
