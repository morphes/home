<?php // TODO: move to assets ?>
<script type="text/javascript">
	/**
	* загрузка файлов
	*/
	var filesList = [];
	var indexFile = 0;

	var totalLoaded = [];
	var totalSize = 0;
	var files;
	
	function submitForm(){
		$('#photo_loader').css({'display':'block'});
		$('#inputFile').remove();
		var h = $('.shadow_block.padding-18').height();
		$('.progressbar').height(h+36);
		$('.progressbar').removeClass('hide');
		$('.progressbar_center').removeClass('hide');
		$('.proj_form_actions').addClass('hide');

		totalSize = getFilesSize(filesList);
		if(filesList.length == 0) {
			$('.progress').css('width', '100%');
			$('.progress > span').text('100%');
			$('#tender-form').submit();
		} else {
			/*
			* Проход по массиву файлов и их отправка
			*/
			sendFiles();
		}
		return true;
	};

	function sendFiles(i)
	{
		if(typeof(i) == 'undefined')
			i = 0;

		if(i < filesList.length) {
			sendFile(i, '<?php echo Yii::app()->controller->createUrl('upload', array('tid'=>$tender->id)); ?>', function(i){
				sendFiles(i+1);
			});
		}
		return true;
	}
	/*
	* Размер массива файлов для загрузки 
	*/
	function getFilesSize(files)
	{
		var total = 0;
		for(var i = 0; i < files.length; i++){
			if(typeof files[i] != "undefined")
				total+=files[i].size;
		}
		return total;
	}

	/*
		* Отправка файла по протоколу XmlHttp
		*/
	function sendFile(cnt, url, callback)
	{

		var xhr = new XMLHttpRequest();
		totalLoaded[cnt] = 0;

		/*
			* Подключение обработчика события процесса загрузки (для прогрессбара)
			*/
		if ( xhr )
			xhr.upload.addEventListener("progress", function(e){ updateProgress(e, cnt) }, false);

		var file = new FormData();

		/*
		* Событие, вызванное по итогу отправки очередного файла
		*/
		xhr.onreadystatechange = function(){
			if(this.readyState == 4) {
				if(this.status == 200) {
					/*
					* Отправка всей формы в случае успешной отправки последнего файла в очереди
					*/
					if(cnt == filesList.length - 1) {
						$('.progress').css('width', '100%');
						$('.progress > span').text('100%');
						$('#tender-form').submit();
						return false;
					}
				}
				delete file;
				delete filesList[cnt];
				delete this;
				if(callback != undefined) callback(cnt);
			}
		}
		
		if (typeof (filesList[cnt]) == 'undefined' ) {
			if(cnt == filesList.length - 1) {
				$('.progress').css('width', '100%');
				$('.progress > span').text('100%');
				$('#tender-form').submit();
				return false;
			}
			if(callback != undefined) callback(cnt);
			return true;
		}

		/*
		* Отправка файла
		*/
		xhr.open("POST", url);

		file.append('UploadedFile['+filesList[cnt].name+']', filesList[cnt]);
		file.append('UploadedFile[desc]', $('#UploadedFileDesc_'+ cnt).val());
		file.append('UploadedFile[name]', filesList[cnt].name);

		xhr.send(file);
	}

	/*
		* Функция отрисовки прогрессбара
		*/
	function updateProgress(e, cnt){
		if (e.lengthComputable) {
			totalLoaded[cnt] = e.loaded;

			var loaded = 0;
			for(var i = 1; i < totalLoaded.length; i++){
				loaded+=totalLoaded[i];
			}

			var total = parseInt((loaded / totalSize) * 100);
			$('.progress').css('width', total+'%');
			$('.progress > span').text(total+'%');
		}
	}
	
	$(document).ready(function(){
		
		
		$('.file_input').live('change',function(evt){
			$(this).prev().text("Загрузить файл");
			// start Showing image preview
			var files = evt.target.files;
			// Подмена input для загрузки в Opera и загрузки тех же файлов в Chrome
			var input = $('<input></input>').addClass('file_input').attr({'id':'inputFile', 'name':'proj_img[0]', 'type':'file', 'size':'60', 'multiple':'multiple'});
			var parent = $(evt.target).parent();

			$(evt.target).remove();
			parent.append(input);
			var divClass = '';
			$this = $(this);
			var fileQt = files.length;
			for (var i = 0; i < fileQt; i++) {
				f= files[i];

				var reader = new FileReader();
				reader.onload = (function(theFile) {
					return function(e) {
						if (theFile.type.match('image.*'))
							divClass = 'image';
						if (theFile.type.match('wordprocessingml.*') || f.type.match('msword.*'))
							divClass = 'doc';
						if (theFile.type.match('spreadsheetml.*') || f.type.match('excel.*'))
							divClass = 'xls';

						var size = parseFloat((theFile.size/1024).toFixed(1));
						var size_unit = 'Кб';
						if (size>1000){
							size = parseFloat((size/1024).toFixed(1));
							var size_unit = 'Мб'
						}


						filesList[indexFile] = theFile;
						$('<div class="uploaded_files"><div class="uploaded_files_name '+divClass+'"><i></i><a>'+theFile.name+'</a></div><div class="uploaded_files_description"><span>Добавить описание</span><textarea id="UploadedFileDesc_'+indexFile+'" class="textInput hide"></textarea></div><div class="uploaded_files_filesize">'+size+' '+size_unit+'</div><div class="uploaded_files_del"><i id="'+indexFile+'"></i></div><div class="clear"></div></div>').appendTo('.image_uploaded');
						indexFile++;
					};
				})(f);
				reader.readAsDataURL(f);

			}
			// end Showing image preview
		});

		$('.uploaded_files_del i').live('click',function(){
			var self = $(this);
			
			var loadedFileId = self.attr('data-value');
			var id = this.id;
			
			if(loadedFileId){
				$.ajax({
					url:"/tenders/tender/removefile",
					data: {'file_id':loadedFileId},
					type: "post",
					dataType: "json",
					async: false,
					success: function(response) {
						if (response.success) {
							self.parents('.uploaded_files').remove();
						}
						if (response.error) {
							location.reload();
						}
					},
					error: function(){
						location.reload();
					}
				});
			}else{
				delete filesList[id];
				self.parents('.uploaded_files').remove();
			}
		});
	});
</script>

<?php echo CHtml::form('', 'post', array('id'=>'tender-form')); ?>
<div class="tender_add_form">
	<div class="tander_name">
		<h3 class="subhead">Наименование <span class="required">*</span></h3>
		<div class="adding_block">
			<?php $class = $tender->hasErrors('name') ? 'textInput textInput-placeholder error' : 'textInput'; ?>
			<?php echo CHtml::activeTextField($tender, 'name', array('id'=>'', 'class'=>$class, 'placeholder'=>'Наименование')); ?>
		</div>
	</div>

	<div class="tender_city">
		<h3 class="subhead">Город</h3>
		<div class="adding_block">
			<div class="dropdown_input">
				<?php $class = $tender->hasErrors('city_id') ? 'textInput city error' : 'textInput city'; ?>
				<?php
				$this->widget('application.components.widgets.EAutoComplete', array(
					'valueName'	=> $cityName,
					'sourceUrl'	=> '/utility/autocompletecity',
					'value'		=> $tender->city_id,
					'options'	=> array(
						'showAnim'  => 'fold',
						//'open'      => 'js:function( event, ui ) { $(".ui-autocomplete").width(194); }',
						'minLength' => 3
					),
					'htmlOptions'	=> array('id'=>'city_id', 'name'=>'Tender[city_id]', 'class'=>$class, 'placeholder'=>'Начните вводить название города'),
					'cssFile' => null,
				));
				?>
			</div>
		</div>
	</div>

	<div class="clear"></div>

	<h3 class="subhead">Подробное описание работ <span class="required">*</span></h3>
	<div class="adding_block tender_text">
		<span class="char_stay">Осталось <span>3000</span> знаков</span>
		<?php echo CHtml::activeTextArea($tender, 'desc', array('id'=>'tender_descript', 'class'=>'textInput', 'maxlength'=>'3000', 'placeholder'=>'Опишите подробно ваш заказ')); ?>

		<div class="image_uploaded">
		<?php foreach ($files as $file) { ?>
			<div class="uploaded_files">
				<div class="uploaded_files_name image">
					<i></i>
					<?php echo CHtml::link($file['name'].'.'.$file['ext'], Yii::app()->controller->createUrl('/download/tenderfile/', array('id'=>$file['id']))); ?>
				</div>
				<div class="uploaded_files_description">
					<?php echo CHtml::tag('span', array(), empty($file['desc']) ? 'Добавить описание' : $file['desc'] ); ?>
					<?php echo CHtml::textArea('File[desc]['.$file['id'].']', $file['desc'], array('class'=>'textInput hide', 'maxlength'=>255)); ?>
				</div>
				<div class="uploaded_files_filesize">
					<?php echo CFormatterEx::formatFileSize($file['size']); ?>
				</div>
				<div class="uploaded_files_del">
					<?php echo CHtml::tag('i', array('data-value'=>$file['id']), ''); ?>
				</div>

				<div class="clear"></div>
			</div>
		<?php } ?>
		</div>

		<div class="file_input_conteiner">
			<div id="attach_file_wrap">
				<input name="" type="file" class="file_input" id="attach_file" size="5"/>
			</div>

			<div class="file_select">
				<i></i>
				<span>Прикрепить файл</span>
			</div>
			<div class="clear"></div>
		</div>
	</div>

	<h3 class="subhead">Бюджет заказа</h3>
	<div class="adding_block">
		<label class="compare_offer">
			<?php echo CHtml::radioButton('Tender[cost_flag]', ($tender->cost_flag==Tender::COST_COMPARE), array('class'=>'textInput', 'value'=>Tender::COST_COMPARE)); ?>
			Не указывать</label>
		<label class="compare_offer">
			<?php echo CHtml::radioButton('Tender[cost_flag]', ($tender->cost_flag==Tender::COST_EXECT), array('class'=>'textInput', 'value'=>Tender::COST_EXECT)); ?>
			Указать</label>
		<label class="compare_offer <?php if ($tender->cost_flag==Tender::COST_COMPARE) echo 'hide'; ?>"><span class="budget"><?php
			$class = $tender->hasErrors('cost') ? 'textInput error' : 'textInput';
			echo CHtml::textField('Tender[cost]', $tender->cost, array('class'=>$class, 'maxlength'=>10));
		?></span> рублей</label>
	</div>

	<h3 class="subhead">Закрыть заказ через</h3>
	<div class="adding_block tender_close drop_down">
		<?php if ($tender->status == Tender::STATUS_MAKING) : ?>
		<span class="exp_current"><?php echo $expireLabel; ?><i></i></span>
		<ul class="set_input">
			<li class="current" data-value="3">3 дня</li>
			<li data-value="7">7 дней</li>
			<li data-value="14">14 дней</li>
			<li data-value="30">30 дней</li>
		</ul>
		<input type="hidden" value="3"  name="time">

		<?php else : ?>

		<span class="exp_current disabled"><?php echo $expireLabel; ?><i></i></span>
		<ul class="set_input">
			<li data-value="3">3 дня</li>
			<li data-value="7">7 дней</li>
			<li data-value="14">14 дней</li>
			<li data-value="30">30 дней</li>
		</ul>
		<input type="hidden" value=""  name="time">

		<a href="#">Изменить дату</a>
		<script type="text/javascript">
			tender.changeTenderDate();
			tender.removeErrorClass();
		</script>

		<?php endif; ?>
	</div>

	<?php if (Yii::app()->getUser()->getIsGuest()) : ?>
	      <div class="user_fields">
		      <div class="field">
			      <label>Ваш e-mail <span class="required">*</span></label>
			      <?php $class = $tender->hasErrors('email') ? 'textInput error' : 'textInput'; ?>
			      <?php echo CHtml::textField('Tender[email]', $tender->email, array('class'=>$class)); ?>
		      </div>
		      <div class="field">
			      <label>Имя или название компании <span class="required">*</span></label>
			      <?php $class = $tender->hasErrors('author_name') ? 'textInput error' : 'textInput'; ?>
			      <?php echo CHtml::textField('Tender[author_name]', $tender->author_name, array('class'=>$class)); ?>
		      </div>
		      <div class="clear"></div>
	      </div>
	<?php endif; ?>
	<input onclick="submitForm();" type="button" value="Разместить заказ" class="btn_grey add_topic"/>
	<img src="/img/load.gif"  id="photo_loader"/>
</div>
<?php echo CHtml::endForm(); ?>
