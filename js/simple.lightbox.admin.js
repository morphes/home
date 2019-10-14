/*
 * Image preview script 
 * powered by jQuery (http://www.jquery.com)
  */
 
 var imagePreview = {
	 
	xOffset: 150,
	yOffset: 30,
	
	init: function(){
		this._init_mouseenter();
		this._init_mouseout();
		this._init_move();
	},
	
	_init_mouseenter: function(){
		var self = this;
		$("a.preview").live('mouseenter',
			function(e){
				this.t = this.title;
				this.title = "";	
				var c = (this.t != "") ? "<br/>" + this.t : "";
				$("body").append("<p id='preview'><img src='"+ this.href +"' alt='Image preview' />"+ c +"</p>");								 
				$("#preview")
				.css("top",(e.pageY - self.xOffset) + "px")
				.css("left",(e.pageX + self.yOffset) + "px")
				.fadeIn("fast");						
			}
		);
	},
	
	_init_mouseout: function(){
		$("a.preview").live('mouseout',function(){
			this.title = this.t;	
			$("#preview").remove();
		});
	},
	
	_init_move: function(){
		var self = this;
		$("a.preview").live('mousemove', function(e){
			$("#preview")
			.css("top",(e.pageY - self.xOffset) + "px")
			.css("left",(e.pageX + self.yOffset) + "px");
		});
	}
	 
 };


// starting the script on page load
$(document).ready(function(){
	imagePreview.init();
});