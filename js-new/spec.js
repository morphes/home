var spec = function(){
	function toggleServices(){
		$('.-tinygray-box').on('click','.level1:not(:first-child)>a',function(){
			var li = $(this).parent('li');
			li.find('ul').slideToggle('fast',function(){
				li.addClass('current');
			}).end()
				.siblings('li')
				.find('ul:visible').slideUp('fast');
			li.siblings('li').removeClass('current')
			return false;
		});
	}

	function toggleFilter(){
		var form = $('.spec-filter');

		form.on('focus','input[type="text"]', function(){
			$(this).animate({width:'620px'});
			form.addClass('expanded').find('.-button').fadeIn('fast');
			form.next().hide();
		});

		form.on('change', 'select', function(){
			form.submit();
		});

		$('body').click(function(e){
			var target = $(e.target).closest(form);
			if (!target.length){
				form.find('.-button').fadeOut();
				form.filter('.expanded').find('input[type="text"]').animate({width:'245px'});
				form.removeClass('expanded').next().show();
			}
		})

	}
	return {
		toggleServices:toggleServices,
		toggleFilter:toggleFilter
	}

}();