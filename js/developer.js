$(function(){
	$('a[href=#]').each(function(index, elem){
		$(elem).html($(elem).html()+' <span style="color: red">!</span>');
		
	});
})