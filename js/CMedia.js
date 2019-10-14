var media = new CMedia();

function CMedia(options){
	var self = this;
	this._options = {
		'eventId':0,
		'email':''
	};

	this.slider = function(){
		self._textValign();
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


	};



	this._textValign = function(){
		$('.slider_control .slide_item a').each(function(){
			var h = $(this).height();
			if(h<35){
				$(this).css({top:'12px'});
			}
		})
	};

	this.cityToggle = function(){
		$('.show_all').click(function(){
			if($(this).prev().is(':visible')){
				$(this).prev().slideUp();
				$(this).next().html('&darr;');
			}else{
				$(this).prev().slideDown();
				$(this).next().html('&uarr;');
			}
			return false;
		});
	};
	/**
	 * навешиваем клик на табы
	 */
	this._clickTab = function(){
		$('.knowledge_tabs li a').click(function(){
			var a = $(this);
			var li = a.parent();
			var container = a.parents('.knowledge_tabs');
			var id = a.attr('data-id');

			container.find('li').removeClass('current');
			li.addClass('current');

			setHash("tab/"+id);
			self._loadTabsContent(id);
			
			return false;
		})

	};
	/**
	 * инициализация табов, загрузка таба указанного в хэше в виде #tab/n, n - id тематики
	 */
	this.initTabs = function(){
		self._clickTab();

		var hash = getHash();
		var id = $('.knowledge_tabs li:first a').attr('data-id');

		if(hash){
			var hashArr = hash.split("/");
			var i = 0;

			for (i;i<=hashArr.length;i++){
				if(hashArr[i] == 'tab'){
					var link = $('.knowledge_tabs li a[data-id = '+hashArr[i+1]+']');
					if(link.is('a')){
						link.trigger('click');
					}
					else{
						setHash("tab/"+id);
						link = $('.knowledge_tabs li:first a');
						link.trigger('click');
					}
				}
			}
		}else{

			setHash("tab/"+id);
			var link = $('.knowledge_tabs li a[data-id = '+id+']');
			link.trigger('click');
		}

	};
	/**
	 * загрузка контента в контейнер таба
	 * @param id - id тематики
	 */
	this._loadTabsContent = function(id){
		var container =$('.knowledge_tabs_content');
		container.html('<img style="margin:50px 45%;" src="/img/loader.gif">');

		$.post(
			'/media/default/ajaxLastItems',
			{id: id},
			function(response){
				if (response.success)
					container.html(response.html);
			}, 'json'
		);
	};

	this.commentScroll = function(){
		$('.right_block .comments_quant').click(function(){
			var comments = $('#comments');
			var destination = comments.offset().top - 12;
			$("html:not(:animated),body:not(:animated)").animate({scrollTop:destination}, 500);
			return false;
		});
	};

	this.InitFilter = function()
	{
		var form = $('#filter_form');
		form.find('.checkbox-list').each(function(){
			var $this = $(this);
			var input = $this.children('input:hidden');

			var arr = $this.find('input[type="checkbox"]');
			var tmpArr = input.val().split(', ');
			var i,j,k=0;

			for (i=0; i<tmpArr.length; i++) {
				if (tmpArr[i]=='')
					continue;
				for (j=0; j<arr.length; j++){
					if ( arr[j].value==tmpArr[i] ){
						arr[j].checked=true;
					}
				}
				k++;
			}
			arr.click(function(){
				if(this.checked){
					this.checked = true;
				} else {
					this.checked = false;
				}
				var val = '';
				var cnt = 0;
				arr.filter(':checked').each(function(){
					val += this.value+', ';
					cnt++;
				});
				input.val(val);
				self._getCount($(this));
			});
		});
		$('.page_template a').click(function(){
			self._pageSettings(form,'viewtype',$(this).data('value'),true);
		});
		$('.elements_on_page ul li').click(function(){
			self._pageSettings(form,'pagesize',$(this).data('value'),true);
		});
		$('.sort_elements span').click(function(){
			var parent = $(this).parent();
			var sortDirect = ( (parent.hasClass('asc') && parent.hasClass('current')) || ( !parent.hasClass('current') && !parent.hasClass('sort_date') ) ) ? 1 : 2;
			self._pageSettings(form, 'sortdirect', sortDirect, false);
			self._pageSettings(form,'sort',$(this).data('value'),true);
			return false;
		});

		$("#date_from").datepicker({
			showOn:"focus",
			buttonImage:"/img/calendar_icon.png",
			buttonImageOnly:true,
			dateFormat:"dd.mm.y",
			dayNamesMin:["Вс", "Пн", "Вт", "Ср", "Чт", "Пт", "Сб"],
			monthNames:["Январь", "Февраль", "Март", "Апрель", "Май", "Июнь", "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь"],
			nextText:" ",
			prevText:" ",
			firstDay:1,
			onSelect : function(dateText, inst)
			{
				var epoch = $.datepicker.formatDate('@', $(this).datepicker('getDate')) / 1000+10800;
				$('#start_time').val(epoch);
				var data = $(this).datepicker('getDate');
				var endDate = $('#end_time');
				if(endDate.val()!=='' && epoch>endDate.val()){
					endDate.val(epoch+75000);
					$("#date_to").datepicker('setDate',data).datepicker('option',{'minDate':data});
				}
				self._getCount($(this));
			}
		});
		$("#date_to").datepicker({
			showOn:"focus",
			buttonImage:"/img/calendar_icon.png",
			buttonImageOnly:true,
			minDate: $('#date_from').datepicker('getDate'),
			dateFormat:"dd.mm.y",
			dayNamesMin:["Вс", "Пн", "Вт", "Ср", "Чт", "Пт", "Сб"],
			monthNames:["Январь", "Февраль", "Март", "Апрель", "Май", "Июнь", "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь"],
			nextText:" ",
			prevText:" ",
			firstDay:1,
			onSelect : function(dateText, inst)
			{
				var epoch = $.datepicker.formatDate('@', $(this).datepicker('getDate')) / 1000+85500;
				$('#end_time').val(epoch);
				self._getCount($(this));
			}
		});
		self._filterToggler();
		$('.filter_hint').on('click', 'a', function(){ $('#filter_form').submit(); return false });
	};
	this._filterToggler = function(){
		$('.filter_name span').on('click',function(){
			var span = $(this);
			var parent = span.parent();

			parent.next('div').slideToggle();
			span.prev().toggleClass('opened');
		});
	};

	this.visitEventInit = function(){
		self._showHint($('.visit_event'));
		self._visitEvent();
		self._refuseEvent();
	};

	var hideTimer;
	this._getCount = function(obj){
		var form = $('#filter_form');
		var formTop = form.offset().top;
		var filterHint = $('.filter_hint');
		var top = obj.offset().top-formTop-5;
		clearTimeout(hideTimer);

		$.ajax({
			url:"/media/event/index",
			data: form.serialize(),
			type: "post",
			dataType: "json",
			async: false,
			success: function(response) {
				if (response.success) {
					$(".filter_hint").html(response.html);
				} else {
					window.reload();
				}
			},
			error: function(response) {
				window.reload();
			}
		});

		if (filterHint.is(':visible'))
			filterHint.fadeIn().animate({'top': top}, 200);
		else
			filterHint.css({'top':top}).fadeIn();
		// Запускаем таймер скрытия
		hideTimer = setTimeout(function(){ filterHint.fadeOut(); }, 4000);
	};

	/*функция показывает/скрывает всплывающие подсказки*/
	this._showHint = function(obj){
		obj.find('>span').mouseenter(function(){
			$(this).find('.hint:not(.hide)').stop().fadeIn(50);

		});

		obj.find('>span').mouseleave(function(){
			obj.find('.hint').css({opacity:1}).stop().fadeOut(150);
		});
	};

	/*подписаться на мероприятие*/
	this._visitEvent = function(){
		var self=this;
		$('.visit_event').on('click','.link:not(.disable)',function(){
			var a = $(this);
			var parent = a.parents('.tools');
			var visitors = parent.find('.visitors span');
			if(!parent.hasClass('active')){
				$.ajax({
					url:"/media/event/bindvisit",
					data: {'eventId':self._options.eventId, 'action':'create' },
					type: "post",
					dataType: "json",
					async: false,
					success: function(response) {
						if (response.success) {
							a.parent().addClass('active');
							a.find('span').text('Я иду!');
							a.find('.hint').removeClass('hide');
							visitors.text(parseInt(visitors.text())+1);
						} else {
							window.reload();
						}
					},
					error: function(response) {
						window.reload();
					}
				});
			}
			return false;
		})
	};
	/*отказаться от мероприятия*/
	this._refuseEvent = function(){
		var self=this;
		$('.visit_event').on('click','.refuse',function(){
			var a = $(this);
			var parent = a.parents('.tools');
			var visitors = parent.find('.visitors span');
			$.ajax({
				url:"/media/event/bindvisit",
				data: {'eventId':self._options.eventId, 'action':'delete' },
				type: "post",
				dataType: "json",
				async: false,
				success: function(response) {
					if (response.success) {
						parent.removeClass('active');
						parent.find('.link span').text('Я пойду!');
						parent.find('.link .hint').addClass('hide').fadeOut(100);
						visitors.text(parseInt(visitors.text())-1);
					} else {
						window.reload();
					}
				},
				error: function(response) {
					window.reload();
				}
			});
			return false;
		})
	};

	this.remindEventInit = function(){
		self._remindEvent();
		self._showHint($('.remind'));
	};

	this._remindEvent = function(){
		var container = $('.remind');
		container.on('click','.link',function(){
			var a = $(this);
			var parent = a.parents('.tools');
			parent.addClass('form').html('<input class="textInput" value="'+self._options.email+'" /><input type="submit" class="btn_grey" value="Ok">');
			self._remindEventForm(container);
		});
	};

	/*функция обрабатывает события на элементах формы добавления напоминания*/
	this._remindEventForm = function(container){
		var input = container.find('.textInput');
		var button = container.find('.btn_grey');

		container.on('click', '.btn_grey',function(){
			self._addRemindValidate(input.val(),input);
		});
		input.focus(function(){
			input.removeClass('error');
		});
		container.on('keydown', '.textInput', function(e){
			if(e.keyCode==13){
				self._addRemindValidate(input.val(),input);
			}
			if(e.keyCode==27){
				container.html('<i></i><span class="link">Напомнить мне</span>');
			}
		});
	};

	/*функция валидирует введеный email, если email соответствует шаблону вызывается функция добавления записи в бд, иначе добавляется сласс 'error'*/
	this._addRemindValidate = function(val,obj){
		var filter  = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;

		if(filter.test(val)){
			self._addRemind(obj.parents('.tools'), val);
		}else{
			obj.addClass('error');
		}
	};


	/*функция добавления адреса в бд*/
	this._addRemind = function(obj, val){
		var self=this;
		$.ajax({
			url:"/media/event/bindnotify",
			data: {'eventId':self._options.eventId, 'email':val },
			type: "post",
			dataType: "json",
			async: false,
			success: function(response) {
				if (response.success) {
					obj.removeClass('form').html('<i></i><span class="mail_adr">'+val+'<div class="hint"><i></i>За 2 дня до меропрятия мы отправим вам напоминание на этот электронный адрес <a class="refuse" href="#">Отказаться</a></div></span><s></s>');
				} else {
					obj.addClass('error');
				}
			},
			error: function(response) {
				obj.addClass('error');
			}
		});
	};

	this.showMap = function(obj){
		$('.show_map').click(function(){

			$.fancybox('<div id="map" style="width:650px;height:400px"></div>',{padding:0});
			$('#fancybox-outer').css({border:0,padding:"15px"});
			$('#fancybox-wrap').css({top:$(document).scrollTop()+50});

			var coordinates = $(this).attr('data-coordinates').split(',');
			var myMap,myPlacemark;

			myMap = new ymaps.Map ("map", {
				center: coordinates,
				zoom: 15,
				behaviors: ['default', 'scrollZoom']
			});
			myMap.controls.add(new ymaps.control.ZoomControl());
			myPlacemark = new ymaps.Placemark(coordinates, {
				content: '',
				balloonContent: ''
			});

			myMap.geoObjects.add(myPlacemark);
		});

	};

	this.setOptions = function(options){
		$.extend(true, this._options, options)
	};

	/*функция, которая вставляет ссылку в ссылку на мероприятие, это сделанно для того что бы поисковик ее не индексировал*/
	this.ShowLink = function(){
		var obj = $('#event_link');
		obj.attr('href',obj.attr('data-url'));
	};

	/**
	 * Функция изменяющая параметры списка элементов(вид, количество на странице)
	 * @param form - формa, в которой находится инпут
	 * @param input - имя инпута, строка
	 * @param value - значение, которое нужно записать
	 * @param submit - флаг отправки формы, по умолчанию false
	 * @private
	 */
	this._pageSettings = function(form,input,value,submit){

		if(submit == undefined){
			submit = false;
		}

		form.find('input[name='+input+']').val(value);

		if(submit != false){
			form.submit();
		}

		return false;
	}
}