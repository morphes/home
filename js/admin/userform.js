function formatNumeral(value)
{
	var words=['услуга', 'услуги', 'услуг'];
	var only_word = false;
	var num = value*1;

	var cases = [2, 0, 1, 1, 1, 2];

	// Получаем правильное слово для указанного числа
	var result = words[ (num%100 >4 && num%100< 20)? 2 : cases[min(v = [num%10, 5])] ];
	// Возвращаем вместе с числом или только слово.
	if (only_word)
		return result;
	else
		return num+' '+result;
}

/*минимальное значение в массиве*/
function min (v)  {
	var m= v[0]
	for (var i=0; i <= v.length-1; i++) {
		if (v[i] < m)
			m=v[i];
	}
	return m;
}

$(document).ready(function(){

	/**
	 * Список услуг пользователя в админке
	 */
	if ($('.my_th').length) {
		var i = -1;
		$('h5.myservices').each(function(){
			var chCnt = 0;
			i++;
			var tbl = $(this).next();
			tbl.find('.servise_check').each(function(){
				if($(this).is(':checked'))
					chCnt++;
				i++;
			});
			if(chCnt){
				tbl.show();
			}
			if((i-chCnt)==0){
				$(this).find('input').prop('checked',true)
			}

			//$(this).find('.act_service_count').text('Выбрано '+formatNumeral(chCnt)).show();
		})

		$('.all_servise_check').click(function(){
			var index=this.id.split('_')[1];

			if($(this).is(':checked')){
				$('#list_'+index+' .servise_check').prop('checked', true);
				$('#list_'+index+' tr').addClass('active');
				if($('#list_'+index).parent('div').is(':hidden')){
					$('#list_'+index).parent('div').slideDown();
					$(this).parent().children('.list_status').toggleClass("open");
					$(this).parent().children('.act_service_count').hide();
				}
			}else{
				$('#list_'+index+' .servise_check').prop('checked', false);
				$('#list_'+index+' tr').removeClass('active');
			}
		});
		$('.servise_check').click(function(){
			var index = $(this).parents('table').attr('id');
			index = index.split('_')[1];
			if($(this).is(':checked')){
				$(this).parents('tr').addClass('active');
				var i = 0;
				$('#list_'+index+' .servise_check').each(function(){
					if($(this).is(':checked')){
					}else{
						i++;
					}
				});
				if(i==0){
					$('#check_'+index).prop('checked', true);
				}
			}else{
				$(this).parents('tr').removeClass('active');

				$('#check_'+index).prop('checked', false);
			}
		});

		$('.my_serv_list').each(function(){
			$(this).find('tr').each(function(){
				$(this).find('td.required_field').each(function(){
					if($(this).find('span').text() == "Не указан"){
						$(this).find('span').addClass('required_notice');

					}
				})

			})
		})
	}

	$(".exp_current:not(.disabled)").live('click',function(){

		var flag =$(this).next().is(':visible');
		if(flag){
			$(this).next().hide();
		}else{
			$('.drop_down ul:visible').hide();
			$(this).next().show();
		}

	});

	$('.drop_down ul li').live('click',function(){
		var ul = $(this).parent();
		var favorite_list = $(this).parents('.favorite_list').find('input');

		if(!ul.parent().hasClass('room_selector')){
			ul.prev('span').html($(this).text()+'<i></i>');
			ul.find('li').removeClass('active');
			$(this).addClass('active');
		}

		ul.prev('span.required_notice').removeClass('required_notice');


		$(this).parents('td').children('input').val($(this).attr('data-value'))
		$('.drop_down ul').hide();


	});
	$(document).click(function(e){
		var match = $(e.target).closest(".drop_down .exp_current");
		if (!match.length){
			$('.drop_down ul:visible').hide();
		}
	});

	$(".myservices a,.myservices .list_status").click(function(){
		var index = $(this).parent().children('.all_servise_check').attr('id');
		index = index.split('_')[1];
		if($(this).parent().next("div").is(':visible')){
			var count = 0;
			$('#list_'+index+' .servise_check').each(function(){
				if(!$(this).is(':checked')){
				}else{
					count++;
				}
			});
			//$(this).parent().children('.act_service_count').text('Выбрано '+ formatNumeral(count)).toggle();
		}else{
			//$(this).parent().children('.act_service_count').hide();
		}
		$(this).parent().next("div").slideToggle();
		$(this).parent().children('.list_status').toggleClass("open");
		return false;
	});

	/*выбор ценового сегмента*/
	$('.price_range.drop_down ul li').click(function(){
		var ul = $(this).parent();
		var td = ul.parent();
		var next = td.next();
		if(td.hasClass('required_field')){

			if(next.find('input').val()!=0){
				next.find('span').html('Не указан<i></i>');
				next.find('input').val(0);
			}

			ul.next().val($(this).attr('data-value'));
			initDropDown(td);
		}
	})

	$('h5.myservices').each(function(){
		var tbl = $(this).next();
		tbl.find('tr').each(function(){
			if($(this).find('.price_range.required_field input').val()>0){
				initDropDown($(this).find('.price_range.required_field'));
			}

		});
	})

	function initDropDown(td){
		var next = td.next();
		next.find('ul li').show();
		next.find('ul li').each(function(){

			next.find('span.disabled').animate({opacity:1},200,function(){
				$(this).removeClass('disabled');
			});

			if($(this).attr('data-value') == td.find('input').val()){
				$(this).hide();
			}
			if(td.find('input').val() == 1 && $(this).attr('data-value') == 3){
				$(this).hide();
			}
			if(td.find('input').val() == 3 && $(this).attr('data-value') == 1){
				$(this).hide();
			}
		})
	}
});