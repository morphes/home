$(document).ready(function(){
	specFilterInit();

	$('.elements_on_page li').click(function(){
		$('#pagesize').val($(this).attr('data-value'));
		$('#filter_form').submit();
	});
	$('.sort_elements li').click(function(){
		$('#sorttype').val($(this).attr('data-value'));
		$('#filter_form').submit();
	});
	
	$('.page_template a').click(function(){
		if ( $(this).hasClass('current') )
			return false;
		$('#filter_form input[name="view_type"]').val($(this).attr('data-value'));
		$('#filter_form').submit();
		return false;
	});
	
	/*фильтр спецов*/

	$('.page_template a').click(function(){
		$('.page_template a').removeClass('current');
		$(this).addClass('current');
		$('#view_type').val($(this).attr('data-value'));
	});
	
	
	$('.specialist_dropdown').focus(function(){
		$(this).parent().next().show();
	});

	$('.specialist_dropdown').focusout(function(){
		$(this).parent().next().fadeOut(150);
		return false;
	});
	
	var serviceContent = '';
	var sphereData = $('.spec_sphere').data();
	var serviceId = sphereData['service_id'];
	var popupCityId = sphereData['city_id'];
	$('.spec_sphere a').click(function(){
		if (serviceContent == '') {
			$.ajax({
				url:"/member/specialist/servicelist",
				data: {'service_id':serviceId, 'city_id':popupCityId},
				type: "post",
				dataType: "json",
				async: false,
				success: function(response) {
					if (response.success) {
						serviceContent = response.html;
					}
				}
			});
		}
		$.fancybox(serviceContent);
	});
	$('.filter_item ul li a').click(function(){
		var checkbox = $(this).prev();
		if(checkbox.prop('checked')){
			checkbox.prop('checked',false);
		}else{
			checkbox.prop('checked',true);
		}
		return false;
	});
	
	// dropdown
	$('#filter_form .spec_type.drop_down li').click(function(){
		$('#filter_form .spec_type #spec_type').val( $(this).attr('data-value') );
	});
});

function specFilterInit()
{
	// country
	var val=$('#filter_form .spec_country input:hidden').val();
	var input = $('#filter_form .spec_country input:visible');
	
	$('#filter_form .spec_country li').each(function(){
		if ( $(this).attr('data-value')==val ){
			input.val($(this).html());
		}
	});
	// city
	val=$('#filter_form .spec_city input:hidden').val();
	input = $('#filter_form .spec_city input:visible');
	
	$('#filter_form .spec_city li').each(function(){
		if ( $(this).attr('data-value')==val ){
			input.val($(this).html());
		}
	});
}
