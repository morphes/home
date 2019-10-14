var profile = function(){
	var _options = {
		'userId':0,
		'userLogin':''
	};
	function reviewImages(){
		var page = $('.reviews');
		page.on('mouseenter','.rewiew-item-photos div',function(){
			var 	src = $(this).find('img:eq(0)').data('src'),
				parent = $(this);
			if(parent.find('.-absolute').size() == 0 && src){
				parent.append('<img class="-absolute -hidden" src="'+src+' ">')
			}
			parent.find('.-hidden:not(:animated)').fadeIn();
		}).on('mouseleave','.rewiew-item-photos div',function(){
				var parent = $(this);
				parent.find('.-hidden').fadeOut('fast');
			});
	}
	function reviewActions(){
		var page = $('.reviews');
		page.on('click','.review-item span.-icon-cross-circle-xs',function(){
			_removeReview($(this).parents('.review-item'));
		});

		function _removeReview(item){
			var id = item.data('id');
			var userId = item.data('userId');
			CCommon.doAction({
				'yes':function(){
					$.ajax({
						async:false,
						url:'/member/review/delete',
						data:{'reviewId':id},
						dataType:'json',
						type:'post',
						success:function(response){
							window.reload();
						},
						error:function(){
							window.reload();
						}
					});
					//ajax-запрос на удаление

				},
				'no':function(){return false;}
			},'Удалить отзыв?');
		}


	}


	function answerActions() {
		var page = $('.reviews');
		page.on('click', '.review-answer span.-acronym', function () {
			_answerReview($(this).parent());
		});
		page.on('click', '.review-answer .-icon-pencil-xs', function () {
			_editAnswer($(this).parents('.review-answer'));
		});
		page.on('click', '.review-answer .-icon-cross-circle-xs', function () {
			_removeAnswer($(this).parents('.review-answer'));
		});

		function _answerReview(obj) {
			var form = $('#answer-form').clone();
			obj.append(form.html());
			obj.find('span.-acronym').removeClass('-acronym -red').addClass('-semibold');
			obj.find('button').click(function () {
				_saveAnswer(obj);
				return false;
			});
			obj.find('form a').click(function () {
				_closeAnswerForm(obj);
				return false;
			});
		}

		function _saveAnswer(obj, editFlag) {

			var id, data, url, dataItem;




			//Запрос на обновление текста сообщения
			if (editFlag) {
				id = obj.data('id');
				data = obj.find('form').serializeArray();
				data[0].id = id;
				url = '/member/review/EditAnswerAjax';
				dataItem = {'item': {'id': data[0].id, 'message': data[0].value}};

			}

			else {

				id = obj.parents('.review-item').data('id');
				data = obj.find('form').serializeArray();
				data[0].id = id;
				url = '/member/review/CreateAnswerAjax';
				dataItem = {'item': {'parrentId': data[0].id, 'message': data[0].value}};

			}

			$.ajax({
				async: false,
				url: url,
				data: dataItem ,
				dataType: 'json',
				type: 'post',
				success: function (response) {
					window.reload();
				},
				error: function () {
					window.reload();
				}
			});
		}

		function _closeAnswerForm(obj){
			obj.find('form').remove();
			obj.find('span.-semibold').removeClass('.-semibold').addClass('-acronym -red')
				.end()
				.find('.-col-wrap, .answer-actions, .answer-item-post').show();
		}

		function _editAnswer(obj){
			var form = $('#answer-form').clone();
			obj.append(form.html());
			var text = obj.find('.answer-item-post').text();
			text = text.replace(/\t/g,'');
			obj.find('form textarea').val(text);
			obj.find('.-col-wrap, .answer-actions, .answer-item-post').hide();
			obj.find('button').click(function(){
				_saveAnswer(obj, true);
				return false;
			});
			obj.find('form a').click(function(){
				_closeAnswerForm(obj);
				return false;
			});
		}
		function _removeAnswer(obj){
			var id = obj.data('id');
			CCommon.doAction({
				'yes':function(){
					$.ajax({
						async:false,
						url:'/member/review/delete',
						data:{'reviewId':id},
						dataType:'json',
						type:'post',
						success:function(response){
							window.reload();
						},
						error:function(){
							window.reload();
						}
					});
					obj.fadeOut(function(){
						obj.remove();
					})
				},
				'no':function(){return false;}
			},'Удалить ответ?');
		}
	}

	function favoriteAcrions(){
		var page = $('.favorite-page');
		page.find('.share-button').click(function(){
			$('#popup-copylink').modal({
				overlayClose:true
			})
		});
		page.find('.create-list').click(function(){
			$('#popup-create-list').modal({
				overlayClose:true
			})
		});
		page.find('#popup-create-list button').click(function(){
			var newValue = $(this).parent().find('input[type="text"]').val();
			$.get(
				'/member/favorite/create/name/'+newValue,
				function(response){
					if (response.success) {
						window.reload();
					} else {
						alert("Ошибка создания списка!\n"+response.html);
					}
				}, 'json'
			);
		});
		page.find('.portfolio_head .-icon-cross-circle-xs').click(function(){
			CCommon.doAction({
				'yes':function(){
					$.get(
						'/member/favorite/delete/id/'+id_group,
						function(response){
							if (response.success) {
								elementLi.remove();
								document.location = '/users/'+_options.userLogin+'/favorite';
							}
							else
								alert('Ошибка удаления');
						}, 'json'
					);
				},
				'no':function(){return false;}
			},'Удалить список?');
		});
	}

	function statisticActions (){
		var 	page = $('.profile.stat'),
			city = page.find('.city-selector'),
			firstDay = page.find(".first-day"),
			lastDay = page.find(".last-day");

		firstDay.datepicker({
			showOn:"focus",
			dateFormat:"dd.mm.y",
			dayNamesMin:["Вс", "Пн", "Вт", "Ср", "Чт", "Пт", "Сб"],
			monthNames:["Январь", "Февраль", "Март", "Апрель", "Май", "Июнь", "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь"],
			nextText:" ",
			prevText:" ",
			minDate: new Date(2013,06,01),
			firstDay:1,
			onSelect : function(dateText, inst)
			{
				var epoch = $.datepicker.formatDate('@', $(this).datepicker('getDate')) / 1000+10800;
				$(this).prev().val(epoch);
				_updateStat();
			}
		});

		lastDay.datepicker({
			showOn:"focus",
			dateFormat:"dd.mm.y",
			dayNamesMin:["Вс", "Пн", "Вт", "Ср", "Чт", "Пт", "Сб"],
			monthNames:["Январь", "Февраль", "Март", "Апрель", "Май", "Июнь", "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь"],
			nextText:" ",
			prevText:" ",
			firstDay:1,
			minDate: new Date(2013,06,01),
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


		page.on('change','.city-selector',function(){
			var 	table = $(this).siblings('table:eq(0)').find('tbody');
			table.addClass('-loading');
			_loadStatContent(table,'city');
		});

		function _updateStat(){
			var 	statPeriod = $('.stat-period'),
				statContent = $('.stat-content'),
				firstDay = statPeriod.find('.first-day').prev().val(),
				lastDay = statPeriod.find('.last-day').prev().val();

			if(firstDay>lastDay){
				statPeriod.addClass('error');
			}else{
				statPeriod.removeClass('error');
				statContent.addClass('-loading');
				_loadStatContent(statContent,'time');
			}
		}
		function _loadStatContent(section, filter){
			city = page.find('.city-selector');
			$.ajax({
				async: false,
				url: '/member/profile/statistic',
				data: {city:city.val(),
					timeFrom:firstDay.prev().val(),
					timeTo:lastDay.prev().val(),
					filter:filter
				},
				dataType: 'json',
				type: 'get',
				success: function (response) {
					section.removeClass('-loading').html(response.html);

				},
				error: function () {
					window.location.reload();
				}
			});
		}
	}




	function setOptions(options){
		$.extend(true, _options, options);
	}

	return {
		reviewImages:reviewImages,
		reviewActions:reviewActions,
		favoriteAcrions:favoriteAcrions,
		setOptions:setOptions,
		answerActions:answerActions,
		statisticActions:statisticActions,
	}

}();