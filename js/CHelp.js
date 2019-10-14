var help = new CHelp({});

function CHelp(options){
	var self = this;

	this.initSelector = function(){
		$('div.selector > ul').on('click', function(e){
			if (!$(e.target).parent().is(':first-child') && !$(e.target).parent().hasClass('active')) {
				$('.active', $(this)).removeClass('active');
				$(e.target).parent().toggleClass('active');
			}
			$(this).toggleClass('expanded');
		});
		$(document).click(function(e){
			var match = $(e.target).closest("div.selector > ul");
			if (!match.length){
				$('div.selector > ul').removeClass('expanded');
			}
		});
	};

	this.drawTriangle = function(){
		var obj = $('.red_menu li.current');
		var h = obj.height();
		var triangle = $('<i></i>');
		triangle.css({"border-width":(h+11)/2});
		obj.append(triangle);
	};
}
