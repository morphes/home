lib.module('mod.Profile');

var Profile = function() {
	function initSlider() {
		var slider = $('.slider'),
		    images = slider.find('.slider-images > div'),
		    controls = slider.find('.slider-controls > div'),
		    qnt = controls.size();
	
		function _toogleSlide(pos) {
			var current = controls.filter('.current');
			    pos = (typeof pos != 'undefined') ? pos : current.index() + 1;
			    pos = (pos >= qnt) ? 0 : pos;
			    controls
				.removeClass('current')
				.eq(pos)
				.addClass('current');
			    images
			    	.eq(pos)
			    	.animate({'opacity': 1}, 400)
			    	.siblings()
			    	.animate({'opacity': 0}, 400)
		}
		slider.on('click', '.slider-controls div:not(.current) a', function() {
			_toogleSlide($(this).parent().index());
			clearInterval(timer);
			return false;
		});
		var timer = setInterval(_toogleSlide, 3000);
	}

	function requestReview(){
		$('.usercard-review-request-btn').click(function(){
			$('#review-request-form').modal({
				overlayClose:true,
				onShow:function(obj){
					_sendRequest(obj.data);
				}
			})
		});

		function _sendRequest(form){
			form.find('.-button').click(function(){
				var data = form.find('form').serializeArray();
				//ajax-запрос
				//по success:
				form.html('<h2>Запросить отзыв</h2> <p>Запрос отправлен</p>');
				//по ошибке показать блок с ошибками
			});
		}
	}

	return {
		initSlider:initSlider,
		requestReview:requestReview
	}
}();
$(function(){
	Profile.initSlider();
});