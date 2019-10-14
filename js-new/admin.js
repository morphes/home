CAdmin = function(){
	function adminWidget(){

		var	widget = $('.admin-widget'),
			visible = localStorage.adminWidget;

		if(visible == 1){
			widget.removeClass('hidden')
				.find('>span').toggleClass('-pointer-left -pointer-right');
		}
		widget.find('>span').on('click', function(){
			visible = localStorage.adminWidget;
			if(visible==1){
				widget.animate({'left':'-=195px'},100);
				localStorage.adminWidget = 0;
			}else{
				widget.animate({'left':'0'},100);
				localStorage.adminWidget = 1;
			}
			$(this).toggleClass('-pointer-left -pointer-right');
		});
	}

	return {
		adminWidget:adminWidget
	}
}();

$(function(){
	CAdmin.adminWidget();
});