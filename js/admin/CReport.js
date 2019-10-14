function CReport(options){
	var self = this;
	var cityCnt=0;
	var vendorCnt=0;
	var storeCnt=0;
	var contractorCnt=0;
	this._options = {
		nodeList:{}, // Список выбранных листьев категорий
		cityHand:3, // значение ключа вручную
		serviceHand:4, // значение ключа вручную
		typeId:1 // Тип отчета
	};

	this.init = function(){
		self.setOptions(options);
		$('#generate').click(function(){
			if (!confirm('Сгенерировать отчет?'))
				return false;

			var response = self._send();
			if (response.success) {
				$('#report-list').prepend(response.html);
				self.updateReports();
			}
			return false;
		});

		// remove report
		$('#report-list').on('click', '.report_delete', function(){
			if ($(this).hasClass('disabled'))
				return false;

			var current=this;
			$.ajax({
				url:'/admin/report/delete',
				data:{type:self._options.typeId, id:$(current).data('id')},
				dataType:'json',
				type:'post',
				success:function(response){
					$('#report_'+$(current).data('id')).remove();
				},
				error:function(response){ /*window.location.reload();*/console.log(response); }
			});
			return false;
		});

		setTimeout(self.updateReports, 2000);
	},
	this._send = function(data){
		if (typeof data=='undefined')
			data={};

		var formData = $('#report').serializeArray();
		for(i in formData){
			data[formData[i].name] = formData[i].value;
		}

		var result=false;
		$.ajax({
			url:'/admin/report/create',
			data:$.extend({type:self._options.typeId}, data, self._options.nodeList),
			dataType:'json',
			async: false,
			type:'post',
			success:function(response){ result=response; },
			error: function(){ window.location.reload(); }
		});

		return result;

	},
	this.updateReports = function(){

		// Собираем список id задач, для которых нужно обновить состояние
		var idList = [];
		$('#report-list tbody tr.for_update').each(function(index, element){
			var task_id = $(element).data('id');
			idList.push(task_id);
		});
		if (idList.length > 0) {
			$.ajax({
				url:'/admin/report/updatelist',
				data:{idList:idList, type:self._options.typeId},
				dataType:'json',
				async:false,
				type:'post',
				success:function(response){
					if (response.success) {
						var reports = response.reports;
						for (var i in reports) {
							$('#report_'+i).replaceWith(reports[i])
						}
						setTimeout(self.updateReports, 2000);
					}
				},
				error:function(response){ window.location.reload(); }
			});
		}

	},
	this.cityInit = function(){
		$('#cities').change(function(){
			var parent = $(this).parents('.clearfix');
			var select = parent.find('select');
			var conteiner = parent.find('.select_block');
			if(select.val()==self._options.cityHand){
				conteiner.removeClass('hide');
			}else{
				conteiner.addClass('hide');
			}
		});

		$('#city_list').on('click', 'li a',function(){
			var id = this.id;
			$(this).parent().remove();
			return false;
		});
	},
	this.citySelect = function(ui){
		var cityId = ui.item.id;
		var cityName = ui.item.label;

		var html='<li><a href="#">[x]</a>'+cityName+'<input type="hidden" name="city['+cityCnt+']" value="'+cityId+'" /></li>';
		$('#city_list').append(html);
		cityCnt++;
		ui.item.value = '';
	}

	this.vendorInit = function(){
		$('#vendor_list').on('click', 'li a',function(){
			var id = this.id;
			$(this).parent().remove();
			return false;
		});
	},
	this.vendorSelect = function(ui){
		var vendorId = ui.item.id;
		var vendorName = ui.item.label;

		var html='<li><a href="#">[x]</a>'+vendorName+'<input type="hidden" name="vendor['+vendorCnt+']" value="'+vendorId+'" /></li>';
		$('#vendor_list').append(html);
		vendorCnt++;
		ui.item.value = '';
	}

	this.storeInit = function(){
		$('#store_list').on('click', 'li a',function(){
			var id = this.id;
			$(this).parent().remove();
			return false;
		});
	},
	this.storeSelect = function(ui){
		var storeId = ui.item.id;
		var storeName = ui.item.label;

		var html='<li><a href="#">[x]</a>'+storeName+'<input type="hidden" name="store['+storeCnt+']" value="'+storeId+'" /></li>';
		$('#store_list').append(html);
		storeCnt++;
		ui.item.value = '';
	}

	this.contractorInit = function(){
		$('#contractor_list').on('click', 'li a',function(){
			var id = this.id;
			$(this).parent().remove();
			return false;
		});
	},
	this.contractorSelect = function(ui){
		var contractorId = ui.item.id;
		var contractorName = ui.item.label;

		var html='<li><a href="#">[x]</a>'+contractorName+'<input type="hidden" name="contractor['+contractorCnt+']" value="'+contractorId+'" /></li>';
		$('#contractor_list').append(html);
		contractorCnt++;
		ui.item.value = '';
	},
 	this.specialistInit = function(){
		 $('#services').change(function(){
			 var parent = $(this).parents('.clearfix');
			 var select = parent.find('select');
			 var conteiner = parent.find('.select_block');
			 if(select.val()==self._options.serviceHand){
				 	conteiner.removeClass('hide');
				 }else{
				 	conteiner.addClass('hide');
				 }
		 });

		 $('.level_1>li a').click(function(){
			 var ul = $(this).parent().children('ul');
			 if(!ul.is(':visible')){
				ul.slideDown();
			 }else{
				ul.slideUp();
			 }
		 return false;
		 });
		 $('.level_1>li>input').change(function(){
			 var input = $(this);
			 var li = $(this).parent();
			 var ul = $(this).parent().children('ul');
			 if(input.prop('checked')==true){
			 li.find('.level_2 input').prop('checked',true);
			 if(!ul.is(':visible')){ ul.slideDown(); }
		 }else{
			 li.find('.level_2 input').prop('checked',false);
			 if(!ul.is(':visible')){ ul.slideUp(); }
		 }
		 })
		 $('.level_2 input').change(function(){
			 var input = $(this);
			 var li = $(this).parents('.level_1 li');
			 var ul = input.parents('.level_2');
			 var i = 0;
			 if(input.prop('checked')==true){
				 ul.find('input').each(function(){
				 if(!$(this).prop('checked')){
				 i++;
				 }
				 });
			 if(i==0){ li.find('input').attr('checked',true); }
			 }else{
				li.find('>input').prop('checked',false);
			 }
		 })
	}



	this.setNodeList = function(nodeList){
		self._options.nodeList = nodeList;
	}


	this.setOptions = function(options){
		$.extend(true, this._options, options)
	}

	this.init();
}