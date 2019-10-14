var forum = new CForum();

function CForum(){
	var self = this;

	this.initComments = function(){
		self.addFiles($('.file_input_conteiner.first'));
		self.quoteComment();
		self.editComment();
		self.delComment();
		self.CommentLike();
		self.pageScroll();
		self.commentsFilter();
		self._alertEmpty($('.topic_answer_form .forum_answer_container'));
	};

	this.drowTriangle = function(){
		var obj = $('.red_menu li.current');
		var h = obj.height();
		var triangle = $('<i></i>');
		triangle.css({"border-width":(h+13)/2});
		obj.append(triangle);
	};

	this.showRules = function(){
		$('.forum_rules').on('click',function(){
			$('#popup-agreement').modal({
				overlayClose:true,
				onShow:function(){
					$('#forum-rules').tinyscrollbar();
				}
			});
			return false;
		});
		$('.forum_umenu li a.user_is_guest').on('click',function(){
			$('#popup-message-guest').modal({
				overlayClose:true
			});
			return false;
		});
	};

	this.tabsInit = function(){
		$('.forum_tabs li span').click(function(){
			var li = $(this).parent();
			var id = li.attr('data-id');
			var ul = li.parent();
			var container = $('.f_middle');
			if(!li.is('.current')){
				ul.find('li').removeClass('current');
				li.addClass('current');
				container.find('.topics_list').css({top:0}).addClass('hide');
				container.find('#tab_'+id).removeClass('hide');
				//self.slideInit();
			}
		});
	};

	this.slideInit = function(){
		var container = $('.topics_list.main:visible');
		var block = $('.main_topics_list_container');
		var arr = new Array();
		var h,blockH=0,height = 0;
		var cnt = 0;
		var next = 7;
		var prev = -1;
		container.find('.item').each(function(i){
			h=h+$(this).height();
			if(i<7){
				blockH = blockH + ($(this).height()+29);
			}


			height = height + ($(this).height()+29);
			arr[i] = height;

			cnt++;
		});
		block.height(blockH);
		if(cnt>6){
			$('.prev_topic,.next_topic').show();
			block.addClass('padding');
		}else{
			$('.prev_topic,.next_topic').hide();
			block.removeClass('padding');
		}
		$('.prev_topic').click(function(){
			if(prev > 0){
				container.stop().animate({top:-arr[prev-1]});
				next--;
				prev--;
			}else{
				container.stop().animate({top:0});
				if(prev==0){
					next--;
					prev--;
				}
			}

		});
		$('.next_topic').click(function(){
			if(next < cnt){
				container.stop().animate({top:-(arr[next]-blockH)});
				next++;
				prev++;
			}
		});
	};

	this.showSubscribe = function(){
		$('.forum_subscribe span').click(function(){
			showPopup('popup-subscribe');
			return false;
		});
		$('#popup-subscribe .cancel_link').click(function(){
			closePopup($('#popup-subscribe'));
			return false;
		});
	};

	this.pageScroll = function(){
		$('.forum_theme .author>span').click(function(){
			var comments = $('.topic_answer_form');
			var destination = comments.offset().top - 12;
			$("html:not(:animated),body:not(:animated)").animate({scrollTop:destination}, 500);
			comments.find('textarea').focus();
			return false;
		});
	};

	this.commentsFilter = function(){
		$('.expert_answers').click(function(){
			var a = $(this);
			if(a.hasClass('with_filter')){
				$('.forum_comments .item').show();
				a.removeClass('with_filter').text('Показать ответы экспертов');
			}else{
				$('.forum_comments .item:not(.expert)').hide();
				a.addClass('with_filter').text('Показать все ответы');
			}
		});
	};

	this.addFiles = function(obj){
		var fileInput = obj.find('.file_input');
		var container = fileInput.parents('.file_input_conteiner');
		var cnt = 0;

		fileInput.MultiFile({
			'accept':'jpg|jpeg|png|bmp|zip',
			'max':10,
			'STRING':{'remove':'[x]', 'denied':'Данный тип файла запрещен к загрузке', 'duplicate':'Уже выбран'},
			afterFileAppend:function (element, value, master_element) {
				var selector = master_element.list.selector;

				$(selector).appendTo(obj.find('.fileslist'));
			}
		});
	};

	this.hoverFileSelector = function(){
		var fileInput = $('.file_input');
		var container = fileInput.parents('.file_input_conteiner');
		container.on('mouseenter','.file_input',function(){
			fileInput.parents('.file_input_conteiner').find('.file_select span').addClass('hover');
		});
		container.on('mouseleave','.file_input',function(){
			fileInput.parents('.file_input_conteiner').find('.file_select span').removeClass('hover');
		});
	};

	this.topicSearch = function(){
		var input = $('#ForumTopic_name');
		var container = $('.similar_topics_container');
		var wrap = container.find('.topic_wrapper');
		var parent = container.parents('.similar_topics');
		var timer = setTimeout('',100);
		input.keyup(function(){
			var val = input.val();

			if (val.length < 3)
				return;

			clearInterval(timer);

			parent.find('.loader').html('<img src="/img/loader2.gif"/>');
			timer = setTimeout(function(){
				/*ajax запрос, возвращающий список похожих тем, где val - введенная фраза:*/
				$.post(
					'/social/forum/ajaxSimilarTopic/value/',
					{value:val},
					function(response) {
						if (response.qnt > 0) {
							wrap.find('.overview').html(response.html);
							parent.find('span.st_link').removeClass('disabled');
							parent.find('.loader').text(response.qnt);

							self._showSimilar();
							$('#scrollbar').tinyscrollbar();
						} else {
							parent.find('.loader').text('');
							parent.find('span.st_link').addClass('disabled');
						}
					}, 'json'
				);
			},300)
		});
	};

	this._showSimilar = function(){
		var container = $('.similar_topics_container');
		$(document).click(function(e){
			var submenu = $(e.target).closest(".similar_topics");
			if (!submenu.length){
				$('.similar_topics_container:visible').css({'visibility':'hidden'});
}
		});

		$('.adding_block').on('click','.similar_topics > span:not(.disabled)',function(){
			container.css({'visibility':'visible'}).addClass('opened');
			container.find('.topic_wrapper').list();
		});

		$(".similar_topics span.opened, .similar_topics_container .close").click(function(){
			container.css({'visibility':'hidden'});
		})
	};

	this.quoteComment = function(){
		$('.forum_comments').on({
			click:function(){
				var txt = '';
				var parent = $(this).parents('.item');
				var author = parent.find('.item_head a.author_name').text();
				var time = parent.find('.item_head span.post_date').text();
				if (txt = window.getSelection) // Not IE, используем метод getSelection
					txt = window.getSelection().toString();
				else // IE, используем объект selection
					txt = document.selection.createRange().text;

				if(txt.length==0){
					txt=parent.find('.item_text').clone();
					txt.find('.quote_text').remove();
					txt = txt.html();
					txt = txt.replace(/\t/g,'');
				}
				txt = txt.replace(/(<br\/?>)*/g,'');
				$('.topic_answer_form textarea').val('[quote="'+author+' '+time+'"]'+txt+'[/quote]\r\n').focus();
				return false;
			}
		},'.item_tools .quote');
	};

	this.CommentLike = function(){
		$('.forum_comments').on({
			click:function(){
				var parent = $(this).parents('.item');
				var id = parent.attr('id');
				var i = $(this);
				var counterContainer = i.parent().find('span');
				var counter = parseInt(counterContainer.text());

				//ajax запрос, по success:
				$.post(
					'/social/forumAnswerLike/add',
					{
						'answerId': id
					},
					function(response){
						if ( ! response.success)
							alert(response.errorMsg);
					}, 'json'
				);
				i.addClass('voted');
				counter=counter+1;
				counterContainer.text('+'+counter);
				if(counter>0){
					counterContainer.addClass('good')
				}
				return false;
			}
		},'.likes .like:not(.voted)');
	};

	this.editComment = function(){
		$('.forum_comments').on({
			click:function(){
				var parent = $(this).parents('.item');
				var id = parent.attr('id');
				var text = parent.find('.item_text');
				var textCopy = parent.find('.item_text').clone();
				//var message = text.find('.quote_text').remove();
				var form = $('.forum_form_clone .forum_answer_container').clone();
				var quote = '';
				var files = parent.find('.item_files li');

				textCopy.find('.quote_text').each(function(i, el){
					var author = $(this).find('span').text().replace(/\n/g,'');
					quote = textCopy.find('.quote_text p').html();
					//quote = quote.replace(/<br\/?>*/g,'');
					quote = '[quote="'+author+'"]'+quote+'[/quote]\r\n';
					$(el).replaceWith(quote);
				});
				var message = textCopy.html().replace(/(\t)*/g,'');
				message = message.replace(/<br\/?>*/g,'');

				form.find('.cancel_edit').removeClass('hide');
				form.find('textarea').val(message);

				text.hide();
				files.hide();

				form.find('form').append('<input name="item_id" type="hidden" value="'+id+'">');
				text.after(form);
				self.addFiles(form.find('.file_input_conteiner'));

				if(files.length>0){
					files.each(function(){
						form.find('.fileslist').append('<div><a href="#" class="old_file" data-file="'+$(this).attr('data-file')+'">[x]</a> <span>'+$(this).find('a').text()+'</span></div>');
					})
				}

				self._markFileForDelete(form);

				self._alertEmpty(form);

				self._cancelEdit();
				return false;
			}
		},'.item_tools .edit');
	};

	/**
	 * Навешивает на кнопку "ответить" предупреждение в случае если пытаются сохранить пустой ответ
	 * @param form Контейнер, в котором находится форма отправки
	 */
	this._alertEmpty = function(form)
	{
		form.on('click', '.add_topic', function(){
			var answer = form.find('textarea').val();

			if (answer === '') {
				alert('Ответ не может быть пустым');
				return false;
			}

			if ( ! $(this).hasClass('disabled')) {
				form.submit();
				$(this).addClass('disabled');
			} else {
				return false;
			}
		});
	};

	/**
	 * Навешивает события по удалению уже существующих файлов в ответет.
	 * Файлы физически не удаляются, а сохраняетюся их id в форму, чтобы
	 * при сабмите удалить.
	 * Это сделано для того, чтобы у пользователя была возможность нажать "Отмена"
	 * @param form Форма в которой лежит список ранее добавленных пользователем файлов.
	 */
	this._markFileForDelete = function(form)
	{
		form.find('.old_file').click(function(){
			var fileID = $(this).data('file');

			form.find('form').append('<input type="hidden" name="ForumAnswer[forDelete][]" value="'+fileID+'">');
			$(this).parent('div').remove();

			return false;
		});
	};

	this.delComment = function(){
		$('.forum_comments').on({
			click:function(){
				var parent = $(this).parents('.item');
				var text = parent.find('.item_text');
				var tools = parent.find('.item_tools');

				text.html('<div class="deleted_message">Сообщение было удалено.</div>');

				$.ajax({
					url:'/social/forum/delAnswer/id/' + parent.attr('id'),
					type:'GET',
					async:true,
					dataType:'json',
					success:function (response) {
						if ( ! response.success) {
							alert(response.errorMsg);
						}
					}
				});

				tools.hide();
				return false;
			}
		},'.item_tools .delete');
	};

	/*Событие на ссылке "отменить редактирование"*/
	this._cancelEdit = function(){
		$('.cancel_edit').live('click',function(){
			var item = $(this).parents('.item');
			item.find('.forum_answer_container').remove();
			item.find('.item_text').show();
			item.find('.item_tools').show();
			item.find('.item_files li').show();
			return false;
		})
	};

	/**
	 * Навешивает события по смене типа и направления сортировки
	 */
	this.sortSettings = function($options)
	{
		var $form = $('form.filter_sort');

		// Кликаем на ссылки типов сортировки.
		$('.sort_elements div.sort').click(function(){
			/*
			 * Если тип сортировки, который хранится в скрытом диве и тип на который
			 * мы кликнули не совпадает, то нужно поменять на текущий (на который кликнули).
			 * Иначе, если мы кликаем по тому же самому типу сортировки, нужно сменить направление
			 * в котором происходит сортировка данных.
			 */

			// Скрытый input — тип сортировки
			var $sorttype = $form.find('input[name=sorttype]');

			var $curtype = $(this).data('sort-type');
			if ($curtype != $sorttype.val())
			{
				$sorttype.val($curtype);
			} else {
				// Скрытый Input — направление сортировки
				var $sortdirect = $form.find('input[name=sortdirect]');

				if ($sortdirect.val() == $options.sortdirect['up'])
					$sortdirect.val($options.sortdirect['down']);
				else
					$sortdirect.val($options.sortdirect['up']);
			}

			$form.submit();
		});
	};

	/**
	 * Навешивает события смены кол-ва элементов на странице
	 */
	this.pageSettings = function()
	{
		var $form = $('form.filter_sort');

		// Меняем кол-во элементов на странице
		$('.elements_on_page ul li').click(function(){
			var pageSize = $(this).data('value');

			$form.find('input[name=pagesize]').val(pageSize);

			$form.submit();
		});
	};

	/**
	 * Удаляет топик, созданного гостем
	 */
	this.delTopicGuest = function()
	{
		$('.forum_theme .del a').click(function(){
			var $link = $(this);
			// ID топика
			var id = $link.data('id');
			// ID раздела
			var sectionId = $link.data('section');

			$.get(
				'/social/forum/delTopicGuest/id/'+id+'/section_id/'+sectionId,
				function(response){
					if (response.success)
						$link.parents('.forum_theme').replaceWith('<div class="deleted_message">Тема была удалена.</div>');
					else
						alert(response.errorMsg);
				}, 'json'
			);

			return false;
		});
	}
}