var Cmedia = function () {
	var _options = {
		itemId: 0 //ид статьи
	};

	function likeItem() {
		var button = $('.like-button');
		var likeText = 'Мне нравится';
		var unlikeText = 'Уже не нравится';

		button.click(function () {

			if ( $('body').hasClass('touch') )
				unlikeText = likeText;

			var action,
				data = button.data(),
				cnt = parseInt(button.find('span').text());
			if ($(this).hasClass('selected')) {
				action = 'ajaxremoveitem';
			} else {
				action = 'ajaxadditem';
				//   data.itemId = _options.itemId;
			}

			//ajax-запрос
			$.ajax({
				url: '/member/like/' + action,
				data: data,
				async: false,
				type: 'post',
				success: function (response) {
					if (response.success) {
						button.toggleClass('selected');
						if (action == 'ajaxadditem')
							button.find('span').text(cnt + 1)
								.end().children('i').text(unlikeText);

						else
							button.find('span').text(cnt - 1)
								.end().children('i').text(likeText);

					} else {
						alert("Ошибка выполнения операции!\n" + response.error);
					}
				},
				dataType: 'json'
			});

			return false;
		});
		button.hover(function () {

			if ( $('body').hasClass('touch') )
				unlikeText = likeText;

			if ($(this).hasClass('selected'))
				$(this).children('i').text(unlikeText);
			else
				$(this).children('i').text(likeText);
		}, function () {
			$(this).children('i').text(likeText);
		});
	}

	function setOptions(options) {
		$.extend(true, _options, options);
	}

	function initInterestActions(){
		var 	page = $('.interest-content'),
			scroll = true,
			loader = $('#contentLoader');
		$('#interestScroll').on('click', function(){
			var page = 1;
			loader.addClass('-loading');
			$(this).remove();
			_loadItems();
			_initInfinityScroll();
			return false;
		});
		function _initInfinityScroll(){
			var marginBottom = 400;
			$(window).bind('scroll',function() {
				if($(window).scrollTop() > ($(document).height() - $(window).height())-marginBottom) {

					if(scroll==false){
						scroll = true;
						loader.addClass('-loading');
						_loadItems();
					}
				}
			});
		}
		function _loadItems(){
			var next_page_url = $("#next_page_url");
			if(next_page_url.val()==undefined){
				loader.removeClass('-loading');
			}
			$.ajax({
				url: next_page_url.val(),

				dataType: "json",
				success: function(response) {
					loader.removeClass('-loading');
					page.append(response.html);
					next_page_url.remove();
					scroll = false;
				},
				error: function() {
					scroll = false;
				}
			});
		}
	}

	return {
		likeItem: likeItem,
		setOptions: setOptions,
		initInterestActions:initInterestActions
	}
}();