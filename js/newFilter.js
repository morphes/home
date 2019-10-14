$(document).ready(function(){
	var keywordsInput = $('.filter_tags input[name="filter-query"]');
	
	/*фильтр*/
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
	
	
	$('.filter_item.drop_down li').click(function(){
		$('#object_type').val($(this).attr('data-rel'));
		$('#filter_form .room_type, .room_style, .room_color').each(function(){
			$(this).children('input:hidden').val('');
		});
		
		$('#filter_form').submit();
	})
	$('.elements_on_page li').click(function(){
		$('#elements_on_page').val($(this).attr('data-value'));
		$('#filter_form').submit();
	})
	$('.sort_elements li').click(function(){
		$('#sort_elements').val($(this).attr('data-value'));
		$('#filter_form').submit();
	})
	
	// Init filter
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
					if(!$(arr[j]).parents('ul').is(':visible')){
						$(arr[j]).parents('ul').show();
						$('.room_type .show_all').text('только основные');
					}
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
			getCount(self);
		});
		
	});
	$('.filter_item input[type="checkbox"]').click(function(){
		$('.btn_conteiner.yellow ').removeClass('hide');
	})
	initTags();
	
});
function checkedItems(cnt, conteiner){
	if(cnt){
		conteiner.find('p span').text('Выбрано '+cnt);
	}else{
		conteiner.find('p span').text('');
	}
}
var hideTimer;
function getCount(obj){

	if (obj == undefined)
		obj = $('.btn_grey');

	// Выпадающие селекты
	if (obj.parent('ul').parent('div').hasClass('drop_down'))
		obj = obj.parent('ul').prev('span');


	clearTimeout(hideTimer);

	var formTop = $('#filter_form').offset().top;
	var top = obj.offset().top-formTop-5;
	var filterHint = $('.filter_hint');

	$.ajax({
		url:"/idea/catalog/ideacounter",
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

function initTags(){
	var input = $("#tags_input");
	var hideinput = $("#tags_list");
	var container = $('.tags_list .filter_items');

	if (hideinput.size() == 0) {
		return false;
	}

	var tmpArr = hideinput.val().split(', ');
	for(i in tmpArr){
		if (tmpArr[i])
			addTagToList(tmpArr[i]);
	}
	input.autocomplete({
		source: '/idea/catalog/tags',
		delay: 200,
		minLength: 3,
		select: function (event, ui) {
			var tagId = ui.item.id;
			var tag = ui.item.label;
			var tagslist = hideinput.val();
			hideinput.val(tagslist+tag+', ');
			addTagToList(tag);
			ui.item.value = '';
			getCount(input);
		},
		open: function() { $('.ui-menu').width(184) }

	});
	function addTagToList(tag){
		tagSlice = (tag.length>18) ? tag.slice( 0, 18 )+'...' : tag;
		container.prepend('<li data-value="'+tag+'" class="checked"><a  href="#">'+tagSlice+'</a><i></i></li>');
		$('.clear_tags').show();
	}
	$('.tags_list .filter_items li i').live('click',function(){
		var parent = $(this).parent();
		var text = hideinput.val().replace(parent.attr('data-value')+', ', '');
		hideinput.val(text);
		parent.remove();
		if(container.find('li').size()==0){
			$('.clear_tags').hide();
		}
		getCount(input);
	})
	$('.clear_tags').click(function(){
		container.find('li').remove();
		hideinput.val('');
		$(this).hide();
		getCount(input);
		return false;
	})
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
