var folders = function(){
	var checkedFile;
	var imageSource=1;
	function initFoldersActions(){
		var page = $('.folders');
		var coverForm = $('.cover-form');
		var uploadSelector='#image-upload';
		var currentFolder;

		_dropFolder();
		page.on('click', '.folder-owner a', function(){
			var folder = $(this).parents('.folder');
			currentFolder = folder;
			if ($(this).hasClass('-icon-pencil-xs')) {
				_editFolder(folder);
			} 
			else if ($(this).hasClass('-icon-cross-circle-xs')) {
				_deleteFolder(folder);
			}
			else {
				_editCover(folder);
			}
			return false;
		});
		coverForm
			.on('change', 'input:radio', function(e){
				e.stopImmediatePropagation();
				$(e.delegateTarget).find('.area').toggleClass('-hidden');
				if (this.value == 2) { // select image
					imageSource=2;
					var top = ($(window).height() - 520) / 2;
					$(e.delegateTarget).parents('.simplemodal-container').animate({top: top, height: '520px'}, 'fast');
					$('#cover-form').tinyscrollbar();
				}
				else { // image upload
					imageSource=1;
					var top = ($(window).height() - 199) / 2;
					$(e.delegateTarget).parents('.simplemodal-container').animate({top: top, height: '159px'}, 'fast');
				} 
			})
			.on('click', '.overview .-col-3', function(e){
				e.stopImmediatePropagation();
				$(this).siblings('.selected').andSelf().toggleClass('selected');

				if ($(this).hasClass('selected')) {
					checkedFile=$(this).data('id');
				} else {
					checkedFile=0;
				}
			})
			.on('click', 'a.-red', function(){
				$('.modalCloseImg').trigger('click');
			})
			.on('click', '.-button-skyblue', function(){
				// здесь сабмитим данные
				var ajaxOptions={};
				var folder = currentFolder;
				if (imageSource==2) { // select image
					ajaxOptions['data'] = {image_id:checkedFile, folder_id:folder.data('id')};
				} else if(imageSource==1) {
					var data = new FormData();
					data.append('folder_id', folder.data('id'));
					var input = $(uploadSelector).get(0);
					if (input.files[0]!=undefined) {
						data.append('CatFolders[file]', input.files[0]);
					}
					ajaxOptions = {processData: false, contentType: false, data:data};
				} else {
					alert('Invalid source type!');
					return false;
				}

				var defOptions = {
					type:'post',
					async:false,
					url:'/catalog/folders/ajaxSetCover',
					dataType:'json',
					success:function(response){
						if (response.success) {
							window.location.reload();
						} else {
							alert('Произошла ошибка, попробуйте позже.');
						}
					},
					error:function(){ alert('Произошла ошибка, попробуйте позже.'); }
				};

				$.extend(true, defOptions, ajaxOptions);
				$.ajax(defOptions);
				return false;
			});

		function _editFolder(folder){
			var 	id = folder.data('id'),
				container = folder.find('.folder-picture'),
				name = container.find('span');
			//сохраняем изначальную высоту элемента
			folder.height(folder.height());
			name.hide();
			folder.find('> p').hide();
			if(folder.find('.edit-container').size() == 0){
				container.after('<div class="-gutter-top-dbl edit-container"><input type="text" class="-gutter-right-hf" value="'+ name.text() +'"><a class="-icon-cross-circle-xs"></a></div>');
				folder.find('.edit-container input')
					.focus()
					.select()
					.keyup(function(e){
						if(e.keyCode == 13){
							_saveFolder(folder);
						} else if(e.keyCode == 27) {
							_cancelEdit(folder);
						}
						return false;
					})
					.end()
					.on('click','.edit-container a', function(){
						_cancelEdit(folder);
					});
			}

		}

		function _deleteFolder(folder){
			var id = folder.data('id');
			CCommon.doAction({
				'yes':function(){
					$.ajax({
						type: "POST",
						url: "/catalog/folders/ajaxDelFolder",
						async: false,
						data: {'item':{'id':id}},
						dataType:"json",
						success:function(response){
							if(response.success==true)
							{
								folder.fadeOut('fast',function(){
									folder.remove();
								})
							}
							else{
								alert('Произошла ошибка, попробуйте позже.');
							}
						}
					});
				},
				no: function(){
				}
			}, 'Удалить папку?')
		}

		function _cancelEdit(folder){
			folder.find('.edit-container').remove()
				.end()
				.find('> p, span').show();
		}

		function _saveFolder(folder){
			var id = folder.data('id');
			var name = folder.find('.edit-container input').val();
			if(name.length == 0)
			{
				folder.find('.edit-container input').addClass('-error');

			}
			else
			{
				$.ajax({
					type: "POST",
					url: "/catalog/folders/ajaxUpdateFolder",
					async: false,
					data: {'item':{'id':id,'name':name}},
					dataType:"json",
					success:function(response){
						if(response.success==true)
						{
							folder.find('.edit-container').remove()
								.end()
								.find('.folder-picture span').show().text(name)
								.end()
								.find('> p').show();
						}
						else{
							alert('Произошла ошибка, попробуйте позже.');
						}
					}
				});
			}
			return false;

		}

		//
		function _dropFolder(){
			$( ".folders-list" ).sortable({
				dropOnEmpty: false,
				revert: 150,
				tolerance: "pointer",
				items: ".folder:not(.folder-template)",
				update: function( event, ui ) {
					var itemData = ui.item.data(),
						data={'item_id':itemData.id, 'type_id':itemData.type,  'position':ui.item.index()+1};
					/*$.ajax({
						async:false,
						url:'/member/profile/moveItem',
						data:data,
						dataType:'json',
						type:'post',
						success:function(response){},
						error:function(){
							window.location.reload();
						}
					});*/
				}
			}).disableSelection();
		}

		function _editCover(folder) {
			var id=$(folder).data('id');
			$.ajax({
				async:false,
				url:'/catalog/folders/ajaxGetEditForm',
				data:{folder_id:id},
				dataType:'json',
				type:'post',
				success:function(response){
					if (response.success) {
						$('.cover-form').html(response.html);
						$('.cover-form').modal({
							overlayClose: true,
							onshow: function(){},
							onClose: function(){checkedFile=0; $.modal.close();}
						});
					}
				},
				error:function(){ window.location.reload(); }
			});
		}
	}

	function initFolderProductsActions(){
		var 	page = $('.folder-content'),
			scroll = true;
		page.on('click','.folder-owner a', function(){
			var product = $(this).parents('.-col-4');
			if($(this).hasClass('-icon-cross-circle-xs')){
				_deleteProduct(product);
			}
			return false;
		});

		function _deleteProduct(product){
			var id = product.data('id');
			CCommon.doAction({
				'yes':function(){

					$.ajax({
						type: "POST",
						url: "/catalog/folders/ajaxDelItem",
						async: false,
						data: {'item':{'id':id}},
						dataType:"json",
						success:function(response) {
							if(response.success==true)
							{
								product.fadeOut('fast',function(){
									product.remove();
								})
							}
							else{
								alert("Ошибка удаления товара.");
							}
						}
					});



				},
				no: function(){
				}
			}, 'Удалить этот товар из папки?')
		}

		$('#scroll').on('click', function(){
			//ajax-запрос, по success:
			var page = 1;
			$(this).parent().remove();
			_loadProducts();
			_initInfinityScroll();
			return false;
		});
		function _initInfinityScroll(){
			var marginBottom = 400;
			$(window).bind('scroll',function() {
				if($(window).scrollTop() > ($(document).height() - $(window).height())-marginBottom) {

					if(scroll==false){
						scroll = true;
						_loadProducts();
					}
				}
			});
		}
		function _loadProducts(){
			var next_page_url = $("#next_page_url");
			$.ajax({
				url: next_page_url.val(),
				//data: $('#products_filter').serialize(),
				dataType: "json",
				success: function(response) {
					page.append(response.html);
					next_page_url.remove();
					scroll = false;
				},
				error: function() {
					scroll = false;
				}
			});
		}
	}


	
	function addProductToFolder(){
		var 	body = $('body'),
			popup = $('#popup-folder');

		body.on('click', '.folder-icon',function(){
			var folder = $(this);

			popup.modal({
				overlayClose:true,
				onShow: function(c){
					popup = c.data;
					_listOptions(folder);
				},
				onClose: function(){
					_closeFolder();
					$.modal.close();
				}
			});
			folder.attr('data-folder-id', popup.find('select').val())
				.data('group-folder', popup.find('select').val());

			popup.find('.-button').off('click')
				.on('click',function(){
					_addToFolder(folder);
					return false;
				});
		});

		function _closeFolder(folder){
			popup.find('input[type="text"]').val('');
			popup.find('select').focus().change();
		}

		function _listOptions(folder){
			popup.find('select, input[type="text"]').off('focus').on('focus',function(){
				$(this).prev().find('input').prop('checked',true);
			});
			popup.find('select, input[type="text"]').off('change').on('change',function(){
				if ($(this).attr('type') == 'text')
					folder.attr('data-folder-id', 'new').data('folder-id','new');
				else
					folder.attr('data-folder-id', $(this).val()).data('folder-id', $(this).val());
			});
		}

		function _addToFolder(folder){
			var data = folder.data();
			var flagReloadPage = false;

			if (data.folderId == 'new') {
				flagReloadPage = true;
				/* Создаем список */
				$.ajax({
					type: "POST",
					url: "/catalog/folders/ajaxAddFolder",
					async: false,
					data: {'item':{'name':popup.find('input[type="text"]').val()}},
					dataType:"json",
					success:function (response) {
						if (response.success) {
							folderId = response.id;
							/*
							 После удачного создания Группы пишем ее в дату,
							 чтобы затем добавить элемент в эту группу так, какбдто
							 он уже был ранее.
							 */
							data.folderId = folderId;

						} else {
							alert("Ошибка создания списка!\n" + response.html);
						}
					}
				});
			}

			if (parseInt(data.folderId) >= 0)
			{
				// добавляем в избранное
				$.ajax({
					type: "POST",
					url: "/catalog/folders/ajaxAddItem",
					async: false,
					data: {'item':{'folderId':data.folderId,'modelId':data.id}},
					dataType:"json",
					success:function(response) {
						if(response.success==true)
						{
							_closeFolder(folder);
							$.modal.close();
							if(flagReloadPage){
								location.reload();
							}
						}
						else{
							if(response.errors == 'Duplicate'){
								alert("Ошибка. Дубликат товара.");
							}
							else{
								alert("Ошибка добавления товара в папку");
							}

						}
					}
				});
			}
		}
	}

	function addFolder(){
		var folder = $('.folder.folder-template');
		folder.on('click', '#toggleAlbumForm, #toggleFormBack',function(){
			switch (this.id) {
				case 'toggleAlbumForm':
					_showForm(folder);

					break;
				case 'toggleFormBack':
					_hideForm(folder);
					break;
			}
		});

		folder.find('.-button-skyblue').click(function () {
			_saveForm(folder);

		});

		folder.find('input').keyup(function (e) {
			if (e.keyCode == 13) {
				_saveForm(folder);
			} else if (e.keyCode == 27) {
				_hideForm(folder);
			}
		});

		function _showForm(folder) {
			folder.find('#toggleAlbumForm').removeClass('-skyblue -pseudolink').addClass('-gray')
				.find('i').html('<i>Альбом пуст</i>');
			folder.find('.-gutter-top-dbl').removeClass('-hidden').find('input').val('').focus();
		}

		function _hideForm(folder) {
			folder.find('#toggleAlbumForm').removeClass('-gray').addClass('-skyblue -pseudolink')
				.find('i').html('<i>Создать альбом</i>');
			folder.find('.-gutter-top-dbl').addClass('-hidden')
		}

		function _saveForm(folder) {
			var name = folder.find('input').val();
			//ajax запрос, по success:

			$.ajax({
				type: "POST",
				url: "/catalog/folders/ajaxAddFolder",
				async: false,
				data: {'item': {'name': name}},
				dataType: "json",
				success: function (response) {
					if (response.success == true) {
						folder.before(response.htmlItem);
						_hideForm(folder)
					}
					else {
						alert('Произошла ошибка, попробуйте позже');
					}

				}
			});


		}
	}

	function copyLink() {
		var page = $('.folders');
		page.on('click', '.-icon-link', function () {
			var url = $(this).data('url');
			$('#popup-copylink').modal({
				overlayClose: true,
				onShow: function (obj) {
					obj.data.find('input').val(url).select();
				}
			});
			return false;
		});
	}

	function sortItems() {

		$(".folder-content").sortable({
			dropOnEmpty: false,
			revert: 150,
			tolerance: "pointer",
			items: 'div.-col-4:not(.last)',
			update: function (event, ui) {
				var itemId = ui.item.data().id, itemIndex = ui.item.index();
				console.log(itemId);
				console.log(itemIndex)

				$.ajax({
					async: false,
					url: '/catalog/folders/moveItemAjax',
					data: {'item': {'itemId': itemId, 'itemPosition': itemIndex }},
					dataType: 'json',
					type: 'post',
					success: function (response) {
					},
					error: function () {
						window.location.reload();
					}
				});
			}
		}).disableSelection();
	}

	function productDiscount(){
		var 	page = $('.folders');

		page.on('click', '.discount-link', function(){
			var	popup = $('#discount-popup'),
				id = $(this).data('product-id');

			$.ajax({
				async: false,
				url: '/catalog/folders/getStoresByProductAjax',
				data: {'item': {'productId': id}},
				dataType: 'json',
				type: 'post',
				success: function (response) {
					popup.html(response.html);
					var height = popup.find('.-tinygray-bg').size() > 1 ? 500 : 290;
					popup.find('.viewport').height(height);
					popup.modal({
						overlayClose: true,
						focus:false,
						onShow: function (obj) {
							obj.data.find('.list-inner').tinyscrollbar();
							_discountActions(obj.data);
						}
					});
				},
				error: function () {
					//window.location.reload();
				}
			});
		});
		function _discountActions(obj){
			var 	discountTypes = obj.find('.discount-type'),
				inputs = discountTypes.find('input[type="text"]');

			$(".first-day").datepicker({
				showOn:"focus",
				dateFormat:"dd.mm.yy",
				dayNamesMin:["Вс", "Пн", "Вт", "Ср", "Чт", "Пт", "Сб"],
				monthNames:["Январь", "Февраль", "Март", "Апрель", "Май", "Июнь", "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь"],
				nextText:" ",
				prevText:" ",
				firstDay:1,
				onSelect : function(dateText, inst)
				{
					var epoch = $.datepicker.formatDate('@', $(this).datepicker('getDate')) / 1000+10800;
					$(this).prev().val(epoch);
					_updateStat($(this).parent());
				}
			});

			$(".last-day").datepicker({
				showOn:"focus",
				dateFormat:"dd.mm.yy",
				dayNamesMin:["Вс", "Пн", "Вт", "Ср", "Чт", "Пт", "Сб"],
				monthNames:["Январь", "Февраль", "Март", "Апрель", "Май", "Июнь", "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь"],
				nextText:" ",
				prevText:" ",
				firstDay:1,
				onSelect : function(dateText, inst)
				{
					var epoch = $.datepicker.formatDate('@', $(this).datepicker('getDate')) / 1000+10800;
					$(this).prev().val(epoch);
					_updateStat($(this).parent());
				}
			});

			$('.first-day, .last-day').next().click(function(){
				$(this).prev().focus();
			});

				inputs.focus(function(){
				//	inputs.not(this).val('');
				//	$(this).prev().prop('checked',true);
				});

			inputs.filter('.discount').on('keyup change', function(e){
				clearTimeout($.data(this, 'timer'));
				var 	$this = $(this),
					parent = $this.parents('.discount-type');

				var wait = setTimeout(function(){
					var price = parseInt(inputs.filter('.suggested_value').val()),
						originalPrice = parseInt(inputs.filter('.suggested_value').next('.-hidden').text()),
						discount = parseFloat($this.val().replace(',', '.')),
						suggested = (discount <= 100)
							? Math.round(originalPrice - originalPrice * (discount / 100))
							: '' ;
					parent.find('.suggested_value').val(suggested);
				}, 100);
				$(this).data('timer', wait);
			});

			inputs.filter('.suggested_value').on('keyup change', function(e){
				clearTimeout($.data(this, 'timer'));
				var 	$this = $(this),
					parent = $this.parents('.discount-type');
				console.log(parent)
				var wait = setTimeout(function(){
					var price = parseInt(inputs.filter('.suggested_value').val()),
						originalPrice = parseInt(inputs.filter('.suggested_value').next('.-hidden').text()),
						discount = parseFloat($this.val().replace(',', '.')),
						tmp = originalPrice - discount,
						suggested = Math.round(tmp/(originalPrice / 100));
					suggested = isNaN(suggested) ? '' : suggested;
					parent.find('.discount').val(suggested);
				}, 100);
				$(this).data('timer', wait);
			});



			obj.find('button').click(function(){
				var data = obj.find('form').serializeArray();

				$.ajax({
					async: false,
					url: '/catalog/folders/AddDiscountAjax',
					data: data,
					dataType: 'json',
					type: 'post',
					success: function (response) {
						window.location.reload();
					},
					error: function () {
						window.location.reload();
					}
				});


			});
			function _updateStat(obj){
				var 	firstDay = obj.find('.first-day').prev().val(),
					lastDay = obj.find('.last-day').prev().val();

				if(firstDay>lastDay){
					obj.find('.last-day').addClass('error');
				}else{
					obj.find('.last-day').removeClass('error');
				}
			}
		}
	}

	return {
		initFoldersActions: initFoldersActions,
		initFolderProductsActions: initFolderProductsActions,
		addProductToFolder: addProductToFolder,
		addFolder: addFolder,
		copyLink: copyLink,
		sortItems: sortItems,
		productDiscount:productDiscount
	}

}();