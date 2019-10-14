var adv = function(){

	function framesToggler(){
		var	togglers 	= $('.frames-toggler .frame'),
			descriptions	= $('.frames-description .frame'),
			images 		= $('.frames-collection .frame'),
			timer		= 5000,
			interval;

		/*добавляем класс hover, что бы на тач-устройствах клик сработал с первого раза*/
		togglers.hover(function(){
				$(this).addClass('hover');
			}, function(){
				$(this).removeClass('hover');
			}
		);

		togglers.click(function(){
			if(!$(this).hasClass('current')){
				var index = $(this).index();
				clearInterval(interval);
				_toggleSlide(index);
			}
		});

		interval = setInterval(function(){
			_toggleSlide();
		},timer);

		function _toggleSlide(index){
			if(index === undefined){
				index = togglers.filter('.current').index() + 1;
				index = (index == 3) ? 0 : index;
			}
			descriptions.filter('.current')
				.fadeOut('fast', function(){
					$(this).removeClass('current');
				})
				.end()
				.eq(index)
				.fadeIn('fast', function(){
					$(this).addClass('current');
				});
			images.filter('.current')
				.fadeOut('fast', function(){
					$(this).removeClass('current');
				})
				.end()
				.eq(index)
				.fadeIn('fast', function(){
					$(this).addClass('current');
				});
			togglers.removeClass('current').eq(index).addClass('current');
		}
	}

	/*функция показа формы заявки на размещение
	* index - массив или объект с номерами услуг которые должны быть автоматически отмеченными.*/
	function showForm(index){
		if(!$.isArray(index)){
			index = $.makeArray(index);
			console.log($.makeArray(index));
		}
		$('.advert-form').modal({
			overlayClose:true,
			onShow: function(obj){
				obj.data.find(':checkbox').prop('checked',false);
				for(i in index){
					obj.data.find(':checkbox').eq(index[i]).prop('checked',true);
				}
			}
		});
		return false;
	}

	/*смена иллюстраций расположения баннеров на страницах*/
	function bannersPositionToggler(){
		var 	menu	 = $('.banner-menu li'),
			citePage = $('.page-preview');

		menu.click(function(){
			var index = $(this).index();

			if(!$(this).hasClass('current')){
				menu.removeClass('current');
				$(this).addClass('current');

				citePage.filter(':not(.-hidden)').fadeOut('fast',function(){
					$(this).addClass('-hidden')
						.find('img, .balloon').hide();
					citePage.eq(index).removeClass('-hidden')
						.show()
						.find('img').fadeIn(function(){
							$(this).siblings('.balloon').fadeIn();
						});
				})
			}
		});

		menu.eq(0).click();
	}

	function advantages(){
		$(window).load(function(){
			$('.frame-1').fadeIn('normal', function(){
				$('.frame-2').fadeIn('normal', function(){
					$('.img-preview').animate({opacity: 1}, 'normal', function(){
						$('.frame-3').fadeIn('normal');
					});
				})
			});
		});
	}

	function sendQuestion(){
		var form = $('#form');
		form.on('click', '.-button-skyblue', function(){

			var data = form.children('.question-form').serializeArray();

			$.ajax({
				type: "POST",
				url: "/content/advertising/addquestionajax",
				async: false,
				data: data,
				dataType:"json",
				success:function(response){
					if(response.error.length==0)
					{
						form.find('> *').toggleClass('-hidden');
					}
					else{
						$.each(response.error, function(n){
							form.find("."+n).addClass('-error');
						})
					}
				}
			});
			return false;
		});
		form.on('focus','.-error', function(){$(this).removeClass('-error')});

		form.on('click', 'a', function(e){
			form.find('input, textarea').val('').end()
				.find('> *')
				.toggleClass('-hidden');

			e.preventDefault();
		});
	}

	function showExample(ex){
		$('#ex-'+ex).modal({
			overlayClose:true,
			onShow: function(obj){
			//	obj.data.find('img').attr('src',img);
				obj.data.tinyscrollbar();
			}
		});
	}

	function initProAccount(){
		$('.stat-table').find('.show-conditions').click(function(){
			$(this).next().slideToggle('fast');
		});

		var input = $('.blue-box').find('input[type="text"]');
		var data = [
			 {label:'Ксения Елисеева',itemId:'1',itemImg:'/img-new/tmp/_1.jpg'},
			 {label:'Владимир Зотов',itemId:'2',itemImg:'/img-new/tmp/_2.jpg'},
			 {label:'Алиса Баронова',itemId:'3',itemImg:'/img-new/tmp/_3.jpg'},
			 {label:'Ксения Елисеева',itemId:'4',itemImg:'/img-new/tmp/_5.jpg'}
		 ];
		$.widget( "custom.catcomplete", $.ui.autocomplete, {
			_renderMenu: function( ul, items ) {
				var that = this;
				$.each( items, function( index, item ) {
					return $( "<li>" ).data( "item.autocomplete", item ).append( "<img class='-col-wrap' src="+item.itemImg+" alt=''><a  class='-col-wrap'>" + item.label + "</a>" ).appendTo( ul );
					that._renderItem( ul, item );
				});
			}
		});

		input.catcomplete({
			minLength: 3,
			delay: 100,
			appendTo:'.user-autocomplete',
			source: "/utility/acSpecialist",// Если надо тестить, вставляем data
			select:function(event,ui){
				$(".pay").attr("data-id", ui.item.itemId);
			}
		});
	}
	return {
		framesToggler:framesToggler,
		showForm:showForm,
		bannersPositionToggler:bannersPositionToggler,
		advantages:advantages,
		sendQuestion:sendQuestion,
		showExample:showExample,
		initProAccount:initProAccount
	}
}();