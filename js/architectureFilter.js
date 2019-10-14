$(document).ready(function(){
	var keywordsInput = $('.filter_tags input[name="filter-query"]');

	/*фильтр*/
	$('.architecture_filter').on({
		click: function(){
			var ul=$(this).parent();
			var val = $(this).attr('data-value');
			ul.next().val(val);
		}
	},' .drop_down li');

	$('.filter_item li:not(.parent) a').click(function(){
		if($(this).prev().is(':checked')){
			$(this).prev().prop('checked', false);
		}else{
			$(this).prev().prop('checked', true);
		}
		var Class = $(this).parents('.filter_item').attr('class');
		Class = Class.replace('filter_item', '');	
		var self = $('.'+Class);
		var arr = self.find('ul li input[type="checkbox"]');
		chk($(this).prev(),arr,self)
		return false;
	});
	
	$('.filter_item .parent a').click(function(){
		var level2 = $(this).parent().children('ul');
		var $this = $(this).children();
		if(!level2.is(':visible')){
			level2.slideDown();
			$(this).parent().addClass('opened');
		}else{
			level2.slideUp();
			$(this).parent().removeClass('opened');
		}
		return false;
	});
	$('.room_type .show_all').click(function(){
		if($('.hide_types').is(':visible')){
			$('.hide_types').slideUp();
			var i = 0;
			$('.hide_types li').each(function(){
				i++;
			})
			$(this).text('еще '+i+' помещений');
		}else{
			$('.hide_types').slideDown();
			var text = $(this).text();
			$(this).text('только основные');
		}
		return false;
	});
	$('.room_style .show_all').click(function(){
		if($(this).hasClass('alreadyOpened')){
			$('.room_style .level2:visible').slideUp();
			$(this).text('развернуть список').removeClass('alreadyOpened');
		}else{
			$('.room_style .level2:hidden').slideDown();
			$(this).text('свернуть список').addClass('alreadyOpened');
		}
		return false;
	});

	$('.all_tags textarea, .some_tags textarea').change(function(){
		keywordsInput.val($(this).val());
		getCount($(this));
	});

	$('.tags_list a').click(function(){

		var tagarea = $(this).parent().parent().find('textarea');
		var tagareatext = tagarea.val();
		if($(this).hasClass('checked')){
			$(this).removeClass('checked');		
			tagareatext = tagareatext.replace(', '+$(this).text(), '');	
			tagareatext = tagareatext.replace($(this).text()+', ', '');	
			tagareatext = tagareatext.replace($(this).text(), '');
			tagarea.val(tagareatext);	
		}else{
			$(this).addClass('checked');	
			if(tagareatext){
				tagarea.val(tagareatext+', '+$(this).text());
			}else{
				tagarea.val($(this).text());
			}
		}
		keywordsInput.val(tagarea.val());
		getCount($(this));
		return false;
	});
	$('.insert_tags, .close_tag_list').click(function(){
		var text = keywordsInput.val();
		$('.some_tags textarea').val(text);
		initTags();
		$('.all_tags').addClass('hide');
		
		return false;
	});
	$('.filter_tags .show_all').click(function(){
		var text = keywordsInput.val();
		$('.all_tags textarea').val(text);
		initTags();
		$('.all_tags').removeClass('hide');
		return false;
	});
	
	$('.filter_item .parent input.check_all').click(function(){
		level2 = $(this).parents('.parent').children('ul');

		if($(this).is(':checked')){
			level2.children('li').children('input').prop('checked', true);
			if(!level2.is(':visible')){
				level2.slideDown();
				$(this).parent().addClass('opened');
			}
		}else{
			$(this).prop('checked', false);
			level2.children('li').children('input').prop('checked', false);
		}
	});
	$('.level2 li input').click(function(){
		if($(this).is(':checked')){
			var count=0;
			$(this).parents('.level2').children('li').children('input').each(function(){
				if(!$(this).is(':checked')){
					count++;
				}
			});
			if(count==0){
				$(this).parents('.parent').children('.check_all').prop('checked', true);
			}
		}else{
			$(this).parents('.parent').children('.check_all').prop('checked', false);
		}
	});
	
	

	$('.elements_on_page li').click(function(){
		$('#elements_on_page').val($(this).attr('data-value'));
		$('#filter_form').submit();
	})
	$('.sort_elements li').click(function(){
		$('#sort_elements').val($(this).attr('data-value'));
		$('#filter_form').submit();
	})
	
	// Init filter

	$('#filter_form .additional_room').each(function(){
		var self = $(this);
		var input = self.children('input:hidden');
		
		var arr = self.find('input[type="checkbox"]');
		var tmpArr = input.val().split(', ');
		var i,j,k=0;
				
		for (i=0; i<tmpArr.length; i++) {
			if (tmpArr[i]=='')
				continue;
			for (j=0; j<arr.length; j++){
				if ( arr[j].value==tmpArr[i] ){
					arr[j].checked=true;
				}
			}
			k++;
		}
		checkedItems(k,self);
		arr.click(function(){

			if (this.checked){
				this.checked = true;
			} else {
				this.checked = false;
			}
			var val = '';
			var cnt = 0;
			arr.filter(':checked').each(function(){
				val += this.value+', ';
				cnt++;
			});
			checkedItems(cnt,self);
			input.val(val);
			getCount($(this));
		});
	});

	$('#filter_form .room_type').each(function(){
		var self = $(this);
		var input = self.children('input:hidden');

		var arr = self.find('input[type="checkbox"]');
		var tmpArr = input.val().split(', ');
		var i,j,k=0;

		for (i=0; i<tmpArr.length; i++) {
			if (tmpArr[i]=='')
				continue;
			for (j=0; j<arr.length; j++){
				if ( arr[j].value==tmpArr[i] ){
					arr[j].checked=true;
				}
			}
			k++;
		}
		checkedItems(k,self);
		arr.click(function(){

			if (this.checked){
				this.checked = true;
			} else {
				this.checked = false;
			}
			var val = '';
			var cnt = 0;
			arr.filter(':checked').each(function(){
				val += this.value+', ';
				cnt++;
			});
			checkedItems(cnt,self);
			input.val(val);
			getCount($(this));
		});
	});
	
	$('#filter_form .room_style').each(function(){
		var self = $(this);
		var input = self.children('input:hidden');
		
		var arr = self.find('input[type="checkbox"]');
		var tmpArr = input.val().split(', ');
		var i,j,k=0;
				
		for (i=0; i<tmpArr.length; i++) {
			if (tmpArr[i]=='')
				continue;
			for (j=0; j<arr.length; j++){
				
				var block = $(arr[j]).parents('.parent');
				if ( arr[j].value==tmpArr[i] ){
					arr[j].checked=true;
					block.children('ul.level2').show();
					if (!block.hasClass('opened'))
						block.addClass('opened');
				}
			}
			k++;
		}
		checkedItems(k,self);
		
		$('.room_style li.parent').each(function(){
			var check_all=0;
			$(this).find('li input').each(function(){
				if(!$(this).is(':checked')){
					check_all++;
				}
			});
			if(check_all){
				$(this).find('input.check_all').prop('checked', false);
			}else{
				$(this).find('input.check_all').prop('checked', true);
			}
		})
		arr.click(function(){
			if (this.checked){
				this.checked = true;
			} else {
				this.checked = false;
			}
			var val = '';
			var cnt = 0;
			arr.filter('li:not(.parent) input:checked').each(function(){
				if (this.value!='')
					val += this.value+', '
					cnt++;
			});
			checkedItems(cnt,self);
			input.val(val);
			getCount($(this));
		});
	});
	
	$('#filter_form .room_color').each(function(){
		var self = $(this);
		var input = self.children('input:hidden');
		
		var arr = self.find('.colors_list li');
		var tmpArr = input.val().split(', ');
		var container = self.find('.checked_color');
		var i,j, name='';
		for (i=0; i<tmpArr.length; i++) {
			if (tmpArr[i]=='')
				continue;
			for (j=0; j<arr.length; j++){
				var item = $(arr[j]);
				name = item.find('p').html();
				
				if ( name == tmpArr[i] ){
					item.addClass('c_checked');
					var span = $('<span></span>').attr( 'id', 'c'+item.attr('id') ).html(' '+name);
					container.append( span );
				}
			}
		}
		
		arr.click(function(){
			var self=$(this);
			if (self.hasClass('c_checked'))
				self.removeClass('c_checked');
			else
				self.addClass('c_checked');
			
			container.empty();
			var name='';
			arr.filter('.c_checked').each(function(){
				name = $(this).find('p').html();
				var span = $('<span></span>').attr( 'id', 'c'+$(this).attr('id') ).html(' '+name);
				container.append(span);
			});
			var colors = $('.checked_color').text();
			colors = colors.replace(/\s/g,', ');
			colors = colors.replace(/,/,'');
			colors = colors.replace(/\s/,'');
			$('#colors_input').val(colors);
			getCount($(this));
		});
		
	});
	$('.filter_item input[type="checkbox"]').click(function(){
		$('.btn_conteiner.yellow ').removeClass('hide');
	});
	initTags();

	$('#object_type').change(function(){
		$('#filter_from').submit();
	});
	
})
function checkedItems(cnt, conteiner){
	if(cnt){
		conteiner.find('p span').text('Выбрано '+cnt);
	}else{
		conteiner.find('p span').text('');
	}
}

var hideTimer;
function getCount(obj){

	// Выпадающие селекты
	if (obj.parent('ul').parent('div').hasClass('drop_down'))
		obj = obj.parent('ul').prev('span');

	clearTimeout(hideTimer);

	var formTop = $('#filter_form').offset().top;
	var top = obj.offset().top-formTop-5;
	var filterHint = $('.filter_hint');

	$.ajax({
		url:"/idea/catalog/architecturecounter",
		data: $('#filter_form').serialize(),
		type: "post",
		dataType: "json",
		success: function(response) {
			if (response.text) {
				$("#filter_form .btn_grey").html(response.text);
				filterHint.find('a').html(response.textHint);
				if (filterHint.is(':visible'))
					filterHint.fadeIn().animate({'top': top}, 200);
				else
					filterHint.css({'top':top}).fadeIn();

				// Запускаем таймер скрытия
				hideTimer = setTimeout(function(){ filterHint.fadeOut(); }, 4000)
			}
		}
	});


}

$('.filter_hint').live('mouseenter', function(){
	clearTimeout(hideTimer);
});

$('.filter_hint').live('mouseleave', function(){
	var $this = $(this);
	hideTimer = setTimeout(function(){ $this.fadeOut(); }, 3000)
});

function initTags(){
//	var dataInput = $('.filter_tags input[name="filter-query"]');
//	var text = dataInput.val();
//	text = text.replace(/\s*,\s*/ig, ', ');
//
//	var area1 = $('.all_tags textarea');
//	var area2 = $('.some_tags textarea');
//	
//	area1.val(text);
//	area2.val(text);
//	
//	var tmpArr = text.split(', ');
//	
//	if (tmpArr) {
//		$('.tags_list_small a, .tags_list_big a').each(function(){
//			if ($.inArray(this.innerHTML, tmpArr)!=-1)
//				$(this).addClass('checked');
//		});
//	}
}

function formSend(){
	$("#filter_form [name='page']").val(1);
	$("#filter_form [name='filter']").val(1);
	$("#filter_form").submit();
	return false;
}

function chk(obj,arr,cont){
	var input = cont.children('input:hidden');
	if (obj.checked){
		obj.checked = true;
	} else {
		obj.checked = false;
	}
	var val = '';
	var cnt = 0;
	arr.filter(':checked').each(function(){
		if ($(this).val()!='')
			val += $(this).val()+', '
			cnt++;
	});
	checkedItems(cnt,cont);
	input.val(val);
	getCount(obj);
}
