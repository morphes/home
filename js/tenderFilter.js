$(document).ready(function(){
	$(".spec_country .dropdown_input").click(function () {
		$(this).find('input').focus().autocomplete("search", "");
	});

	$('.tender_status li').click(function(){
		var val =  $(this).attr('data-value');
		$('#tender_type').val(val);
	})

	$('.elements_on_page li').click(function(){
		$('#pagesize').val($(this).attr('data-value'));
		$('#filter_form').submit();
	})
	$('.sort_elements li').click(function(){
		$('#sorttype').val($(this).attr('data-value'));
		$('#filter_form').submit();
	})

	/*услуги*/
	$('.service_cat').on({
		click:function(){
			var li = $(this);
			var secId = li.attr('data-value');
			$('.tender_s_type').find('.exp_current').html('Не выбрано <i></i>');
			$('#service_id').val('');
			$("#service_cat").val(secId);
			if (parseInt(secId) > 0) {
				getServiseList(secId);
			} else {
				$('.service_id .exp_current').addClass('disabled').css({'opacity':0.5});
			}
			
			$('.tender_s_type').find('.exp_current').html('Не важно <i></i>');
			$('#service_id').val('');
		}
	},"li");

	$('.tender_s_type').on({
		click:function(){
			var li = $(this);
			var secId = li.attr('data-value');

			$("#service_id").val(secId);
		}
	},"li");
	
	// Init filter
	if($('#service_cat').val()){
		var serviseCat = $('.service_cat');
		var serviceCatId = $('#service_cat').val();
		if (serviceCatId > 0) {
			$('.service_id .exp_current.disabled').removeClass('disabled');
		}
		initDropDown(serviseCat,serviceCatId);
	}

	if($('#service_id').val()){
		var serviseIdConteiner = $('.service_id');
		var serviseId = $('#service_id').val();

		initDropDown(serviseIdConteiner,serviseId);
	}
});

/**
 * функция получения списка услуг в выбранной категории
 * @param id - ид категории
 */
function getServiseList(id){
	/* отправка ajax-запроса */
	$.ajax({
		url:"/tenders/tender/servicelist",
		data: {'service_id':id},
		type: "post",
		dataType: "json",
		async: false,
		success: function(response) {
			if (response.success) {
				var html = '<li data-value="0">Не важно</li>'+response.html;
				$('.service_id .drop_down ul').html(html);
				$('.service_id .exp_current.disabled').animate({opacity:1},function(){
					$(this).removeClass('disabled');
				})
			}
		}
	});
}

/**
 * инициализация dropdown в фильтре
 * @param obj - объект внутри которого лежит .drop_down
 * @param id - data-value элемента который выбран
 */
function initDropDown(obj,id){

	var span = obj.find('.exp_current');
	var ul = obj.find('ul');

	ul.find('li').each(function(){
		if($(this).attr('data-value')==id){
			span.html($(this).text()+'<i></i>');
			$(this).addClass('active');
		}
	})
}