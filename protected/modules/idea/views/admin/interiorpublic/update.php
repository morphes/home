<?php
$cs = Yii::app()->clientScript;
$cssCoreUrl = $cs->getCoreScriptUrl();

$cs->registerCssFile($cssCoreUrl . '/jui/css/base/jquery-ui.css');
$cs->registerCoreScript('jquery.ui');

$cs->registerScriptFile('/js/simple.lightbox.admin.js', CClientScript::POS_HEAD);

/**
 * При загразуке страницы смотрим, что стоит в скритом поле input
 * Если value = 1, то закрываем форму от редактирования, позволяя
 * выбирать значения только из первого селекта.
 */
Yii::app()->getClientScript()->registerScript('disable_mask', "

	var formArch = $('form.arch');

	// Засеряем и болкируем всю форму, кроме выбора типа строения.
	if ($('.disabled_mask').length){
		$('#interiorpublic-form input, textarea, select:not(#Interiorpublic_building_type_id)').attr('disabled', 'disabled');

		$('.disabled_mask').css('height', formArch.height()-130);
	}


	// Сабмит формы после выбора типа строения
	$('#Interiorpublic_building_type_id').change(function(){
		$('#interiorpublic-form').append('<input type=\"hidden\" name=\"change_build_type\" value=\"true\">');
		formArch.submit();
	});

	$('.interior_selector ul li').click(function(){
		// Если есть класс set_input, то следующему input вставляем выбранное значение
		var val = $(this).attr('data-value');
		$(this).parent('ul').next('input').val(val);
	});


", CClientScript::POS_READY);


?>
<script type="text/javascript">
	$(document).ready(function(){

		$('.save_delete').click(function(){
			$.get(
				'/idea/admin/interiorpublic/delete/id/<?php echo $model->id;?>',
				function(data){
					if (data.success) {
						document.location = '/idea/admin/interiropublic/list';
					}
				}, 'json'
			);

			return false;
		});



		/**
		 * загрузка файлов
		 */
		var coverInput = $('.cover_input_clone.hide>div').clone();

		var filesList = [];
		var indexFile = 0;
		var totalLoaded = [];
		var totalSize = 0;
		var files;

		$('.img_input:not(.cover)').live('change',function(evt){

			files = evt.target.files;

			showFiles();
		});

		function showFiles(i)
		{
			if(typeof(i) == 'undefined')
				i = 0;

			if(i < files.length) {
				showFile(i, function(i){
					showFiles(i+1);
				});
			}
		}

		function showFile(i, callback)
		{
			var f = files[i];

			if (!f.type.match('image.*')){
				alert('Файл ' + f.name + ' не является изображением.');
				if(callback != undefined) callback(i);
				return;
			}

			var reader = new FileReader();
			reader.onload = (function(theFile)
			{
				return function(e)
				{
					var th = this;
					var img = new Image();
					img.onload = function()
					{
						var styleOrientation = (img.width > img.height)
								   ? 'height:150px;'
								   : 'width:150px;';

						$('<div class="uploaded"><div class="row" style="margin-bottom: 20px;"><div class="span3"><img src="'+e.target.result +'" style="width:150px; '+styleOrientation+'" /></div><div class="span5"><label>Описание изображения</label><textarea  name="UploadedImage[desc]['+ indexFile +']" id="UploadedImageDesc'+ indexFile +'"  class="textInput img_descript span5" rows="5"></textarea></div><div class="span5"><label>&nbsp;</label><a href="#" class="btn danger del_img" id="'+indexFile+'">Удалить</a></div></div></div>').appendTo('.image_uploaded');

						filesList[indexFile] = theFile;
						indexFile++;
						delete files[i];
						delete th;
						if(callback != undefined) callback(i);
					};

					img.src = e.target.result;
				};
			})(f);
			reader.readAsDataURL(f);
		}


		/*
		 * Отправка формы
		 */
		$('#interiorpublic-submit').click(function(){

			$('#inputFile').remove();

			//var h = $('.shadow_block.padding-18').height();
			//$('.progressbar').height(h+36);
			//$('.progressbar').removeClass('hide');

			$('.progressbar_center').removeClass('hide');
			$('.actions a').hide();

			totalSize = getFilesSize(filesList);
			if(filesList.length == 0) {
				finishUploadSubmit();
			} else {
				/*
				 * Проход по массиву файлов и их отправка
				 */
				sendFiles();
			}

			return false;
		});

		/**
		 * Функция вызывается по завершению загрузки всех фоток аяксом.
		 * Помещает прогресс бар на 100% и сабмит основную форму.
		 */
		function finishUploadSubmit()
		{
			$('.progress').css('width', '100%');
			$('.progress').text('100%');

			$('#interiorpublic-form').submit();
		}


		function sendFiles(i)
		{
			if(typeof(i) == 'undefined')
				i = 0;

			if(i < filesList.length) {
				sendFile(i, '<?php echo $this->createUrl('upload', array('id'=>$model->id)); ?>', function(i){
					sendFiles(i+1);
				});
			}
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
					if (typeof(filesList[cnt]) == 'object') {
						if(this.status == 200) {
							/*
							 * Отправка всей формы в случае успешной отправки последнего файла в очереди
							 */
							if(cnt == filesList.length - 1) {
								finishUploadSubmit();
								return false;
							}
						}
						delete file;
						delete filesList[cnt];
						delete this;
					}
					if (callback != undefined) callback(cnt);
				}
			};

			/*
			 * Отправка файла
			 */
			xhr.open("POST", url);

			file.append('Interiorpublic[image]', filesList[cnt]);
			file.append('Interiorpublic[desc]', $('#UploadedImageDesc'+ cnt).val());

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
				$('.progress').text(total+'%');
			}
		}



		/* ----------
		 *  Обложка
		 * ----------
		 */
		$('.cover_image a').live('click',function(){
			$(this).parents('.cover_image').remove();
			$('.cover_conteiner').html(coverInput).show();

			$('#Interiorpublic_image_id').val('');
			return false;
		});

		$('.img_input.cover').live('change',function(evt){
			// start Showing image preview
			var files = evt.target.files[0];
			$this = $(this);
			var reader = new FileReader();
			reader.onload = (function(theFile) {
				return function(e) {
					$('.cover_conteiner').hide();
					$('<div class="cover_image"><div><img width=200 src="'+e.target.result+'"></div><br class="clear"><div class="del_cover"><i></i><a class="del" href="#">Удалить</a></div></div>').insertAfter('.cover_conteiner');
				}
			})(files);
			reader.readAsDataURL(files);

		});

		/* --------------------------
		 *  Удаление фотографий со страницы
		 * --------------------------
		 */
		$('.image_uploaded').on({
			click:function(){

				if ($(this).attr('id')) {
					delete filesList[this.id];
					$(this).parents('.uploaded').remove();
				} else {
					var img_id = parseInt($(this).attr('data-img_id'));
					if (img_id > 0) {
						$(this).parents('.uploaded').remove();

						$.get(
							'/idea/admin/interiorpublic/deleteimage/id/'+img_id,
							function(response)
							{
								if (response == 'error')
									alert('Ошибка удаления изображения');
							}
						);
					}
				}

				return false;
			}
		}, '.del_img');

		/*-----/---*/





		$('.project_add').on({
			click:function(){
				var ul = $(this).parent();
				var val = $(this).attr('data-value');
				ul.next().val(val)
			}
		},'.drop_down ul li, .interior_selector ul li');

		$(document).ready(function(){
			$('.interior_selector').on({
				click:function(){
					var id = $(this).attr('data-value');
					var ul = $(this).parent();
					var imgConteiner = $('.interior_cover');

					ul.find('li').removeClass('current');
					$(this).addClass('current');

					var src = $(this).attr('data-img-src');

					if (id != 0) {
						imgConteiner.html('<img width="200" src="'+src+'">');
					} else {
						imgConteiner.html("");
					}

				}
			},'li');
		});


		/** -----------
		 *   Соавторы
		 *  -----------
		 */
		$('.add_coautor_link a').click(function(){
			/*var coautorInput = $('.coautor_clone.hide>div').clone();
			coautorInput.insertBefore('.add_coautor_link');*/

			$.ajax({
				url: "/idea/admin/interiorpublic/addcoauthor",
				data: {"interiorpublicId":<?php echo $model->id; ?>},
				dataType: "json",
				type: "post",
				success: function(response)
				{
					if (response.success)
						$(response.data).insertBefore('.add_coautor_link');
					else
						alert(response.error);
				}
			});

			return false;
		});
		$('.project_add_coautor').on({
			click:function(){
				var $this = $(this);

				var coauthorId = $(this).attr('data-value');
				$.ajax({
					url:"/idea/admin/interiorpublic/deletecoauthor",
					data:{"coauthorId":coauthorId},
					dataType:"json",
					type: "post",
					success: function(response){
						if (response.success) {
							$this.parents('.add_coautor').remove();
						}
					}
				});

				return false;
			}
		},'.del_coautor');
		/*-----/---*/

		/* --------------------
		 *  Источники
		 * --------------------
		 */

		$("#source-create-button").click(function(){
			$.post(
				"/content/admin/source/createmultiple",
				{ model_id : "<?php echo $model->id?>", model_name: 'Interiorpublic' },
				function(response){
					if (response.success) {
						$(".source-container").append(response.html);
					}
				},
				"json"
			);
		});

	});

	/**
	 * Удаление источника
	 * @param self
	 * @param sourceId
	 */
	function removeSource(self, sourceId) {
		$.post(
			'/content/admin/source/deletemultiple/id/'+sourceId,
			function(response){
				if (response.success) {
					$(self).parents('tr').remove();
				} else {
					alert('Ошибка удаления');
				}
			}, 'json'
		);
	}

</script>


<?php
$this->breadcrumbs=array(
	'Идеи'=>array('/idea/admin/interiorpublic/list'),
	'Общественные интерьеры' => array('/idea/admin/interiorpublic/list'),
	'Редактирование общественного интерьера'
);
?>

<h1>Редактирование интерьера (обществ.) #<?php echo $model->id;?>
	<?php echo !empty($model->name)? ' - "'.$model->name.'"': '' ;?></h1>


<?php echo CHtml::errorSummary($model, '<div class="error_conteiner service_error"><div class="error_content">', '</div></div> <div class="spacer-18"></div>');?>


<?php
if($model->author_id)
{
	$author = CHtml::link($model->author->login.' ('.$model->author->name.')', $this->createUrl('/member/profile/user/', array('id' => $model->author->id)));
}
else $author = '';
$this->widget('ext.bootstrap.widgets.BootDetailView', array(
	'data'=>$model,
	'attributes'=>array(
		array(
			'label'=>'Автор',
			'type'=>'html',
			'value'=>$author,
		),
		array(
			'label'=>'Дата создания',
			'type'=>'raw',
			'value'=>date('d.m.Y H:i', $model->create_time),
		),
		array(
			'label'=>'Статус',
			'type'=>'html',
			'value'=>"<span class='label success'>".Interiorpublic::getStatusName($model->status)."</span>",
		),
	),
));
?>



<?php
$form = $this->beginWidget('ext.bootstrap.widgets.BootActiveForm', array(
	'id' 			=> 'interiorpublic-form',
	'enableAjaxValidation' 	=> false,
	'stacked'		=> true,
	'htmlOptions' 		=> array(
		'class' => 'relative arch',
		'enctype' => 'multipart/form-data',
	),
));
?>

<?php
// Если тип строения не выбран, то запрещаем редактирование формы
if ($model->object_id == 0 && $model->building_type_id == 0)
	echo '<div class="disabled_mask" style="width: 100%;"></div>';
?>


<?php echo $form->errorSummary($model);?>

<div class="well" style="background-color: #F9F9F9;">
	<?php echo CHtml::tag('h2', array('style' => 'margin-bottom: 20px;'), 'Общая информация', true); ?>

	<div class="clearfix">
		<?php echo CHtml::label('Автор', 'author'); ?>

		<div class="input">
			<?php $this->widget('zii.widgets.jui.CJuiAutoComplete', array(
				'name'        => 'author',
				'sourceUrl'   => '/utility/autocompleteuser',
				'value'       => isset($model->author->name)
					? $model->author->name . " ({$model->author->login})"
					: '',
				'options'     => array(
					'showAnim'  => 'fold',
					'delay'     => 0,
					'autoFocus' => true,
					'select'    => 'js:function(event, ui) {$("#Interiorpublic_author_id").val(ui.item.id); }',
				),
				'htmlOptions' => array('class' => 'span7')
			));?>

			<?php echo $form->hiddenField($model, 'author_id', array('size' => 15)); ?>

			<?php
			Yii::app()->clientScript->registerScript('loginType', '
								$("#author").keydown(function(event){
									if (
										event.keyCode != 27 && event.keyCode != 9 && event.keyCode != 13
										&& event.keyCode != 37 && event.keyCode != 38 && event.keyCode != 39 && event.keyCode != 40
										&& event.keyCode != 35 && event.keyCode != 36
									) {
										$("#Interiorpublic_author_id").val("");
									}
								});
							', CClientScript::POS_READY);
			?>
		</div>
	</div>

	<?php echo $form->dropDownListRow($model, 'building_type_id', array(''=>'')+CHtml::listData($buildingTypes, 'id', 'option_value')); ?>

	<?php echo $form->textFieldRow($model, 'name', array('class'=>'span12')); ?>

	<?php echo $form->textAreaRow($model, 'desc', array('class'=>'span12', 'rows'=>'5')); ?>


	<div class="clearfix <?php if ($model->getError('image_id')) echo 'error';?>">

		<label class="<?php if ($model->getError('image_id')) echo 'error';?>">Обложка идеи <span class="required">*</span></label>

		<div class="cover_conteiner <?php if ($model->getError('image_id')) echo 'error';?>" <?php if ($model->image) echo 'style="display:none;"';?>>
			<?php
			/**
			 * Запоминаем вид инпута для загрузки обложки, чтобы вставить его еще и в
			 * запасной инпут, которым заменается содержимое текущего cover_conteinera при
			 * удалении выбранной фотографии.
			 */
			$imageCoverInput = $form->fileField($model, 'image', array('class' => 'img_input cover', 'size' => 61));
			echo $imageCoverInput;
			?>
		</div>

		<?php if ($model->image) : ?>
		<div class="cover_image">
			<div>
				<?php echo Interior::imageFormater($model->image);?>
			</div>
			<div class="del_cover"><i></i><a class="del" href="#">Удалить</a></div>
		</div>

		<?php endif; ?>

		<?php echo $form->hiddenField($model, 'image_id'); ?>
	</div>


	<div class="row project_add_coautor" style="margin-top: 20px;">

		<div class="span18">
			<?php
			/* -----------
			 *  Соавторы
			 * -----------
			 */
			Yii::app()->getClientScript()->registerCoreScript('jquery-ui');
			foreach ($coauthors as $coauthor) {
				$coauthorError = empty($coauthorErrors[$coauthor->id]) ? array() : $coauthorErrors[$coauthor->id];
				$this->renderPartial('_addCoauthor', array('coauthor' => $coauthor, 'errors' => $coauthorError));
			}
			?>

			<div class="add_coautor_link">
				<i></i>
				<a href="#">Добавить соавтора проекта</a>
			</div>
			<div class="clear"></div>
		</div>
	</div>
</div>

<div class="well" style="background-color: #F9F9F9;">
	<?php echo CHtml::tag('h2', array('style' => 'margin-bottom: 20px;'), 'Источники', true); ?>
	<?php $this->renderPartial('_source', array('sources' => $sources, 'form'=>$form));?>
</div>


<div class="well" style="background-color: #F9F9F9;">
	<?php echo CHtml::tag('h2', array('style' => 'margin-bottom: 20px;'), 'Характеристики', true); ?>


	<?php echo $form->dropDownListRow($model, 'style_id', array('' => '')+CHtml::listData($styles, 'id', 'option_value'), array('class' => 'span7')); ?>

	<div class="row">
		<div class="span7">
			<?php echo $form->dropDownListRow($model, 'color_id', array('' => '')+CHtml::listData($colors, 'id', 'option_value'), array('class' => 'span7'));?>
		</div>

		<div class="span3">
			<?php echo $form->dropDownListRow($addColors[0], "[$model->id][0]color_id", array('' => '')+CHtml::listData($colors, 'id', 'option_value'), array('class' => 'span3'));?>
		</div>

		<div class="span3">
			<?php echo $form->dropDownListRow($addColors[1], "[$model->id][1]color_id", array('' => '')+CHtml::listData($colors, 'id', 'option_value'), array('class' => 'span3'));?>
		</div>
	</div>
</div>


<div class="well" style="background-color: #F9F9F9;">
	<?php echo CHtml::tag('h2', array('style' => 'margin-bottom: 20px;'), 'Изображения', true); ?>

	<div class="image_uploaded">
		<?php
		/* -------------------------------------------
		 *  Вывод уже имеющихся фотографий у проекта
		 * -------------------------------------------
		 */
		foreach($model->images as $img) : ?>
			<div class="uploaded">
				<div class="row" style="margin-bottom: 20px;">
					<div class="span3">
						<?php echo Interior::imageFormater($img->uploadedFile);?>
						<?php echo CHtml::link('скачать оригинал', $this->createUrl('/download/productImgOriginal', array('file_id'=>$img->uploadedFile->id))); ?>
					</div>
					<div class="span5">
						<label>Описание изображения</label>
						<textarea name="UploadedImage[desc][<?php echo $img->uploadedFile->id;?>]"
							  class="textInput img_descript span5"
							  rows="6"
						><?php echo $img->uploadedFile->desc;?></textarea>
					</div>
					<div class="span5">
						<label>&nbsp;</label>
						<?php echo CHtml::link('Удалить', '#', array('class' => 'del_img btn danger', 'data-img_id' => $img->uploadedFile->id));?>
					</div>
				</div>
			</div>
			<?php endforeach; ?>
	</div>
	<div class="clear"></div>
	<div class="image_to_upload">
		<input  name="proj_img[0]" type="file" class="img_input" size="61" multiple="multiple" />
	</div>
</div>


<?php echo $form->dropDownListRow($model, 'status', Interiorpublic::getStatusName(), array(
	'class' => 'interiorpublic_status'
)); ?>

<?php
/* -----------------------------------------------------------------------------
 *  Виджет выбора шаблона сообщения для статуса "Отклонен"
 * -----------------------------------------------------------------------------
 */
$this->widget('application.modules.idea.components.InteriorRejectedMessage.InteriorRejectedMessage', array(
	'selectorForStatus'    => '.interiorpublic_status',
	'selectorMessageField' => '#user_message',
	'authorName'           => $model->author->name,
	'projectName'          => $model->name
));?>

<div class="clearfix">
	<label>Сообщение пользователю</label>
	<div class="input">
		<?php echo CHtml::textArea('user_message', '', array('class'=>'span13','rows' => '15'));?>
	</div>
</div>

<div class="actions">
	<?php echo CHtml::link('Сохранить', '#', array('class'=>'btn primary large', 'id'=>'interiorpublic-submit')); ?>
	<?php echo CHtml::link('Отмена', $this->createUrl('/'.$this->module->id.'/admin/interiorpublic/view', array('id' => $model->id)), array('class'=>'btn default large'));?>

	<div class="progressbar_center hide">
		<div class="bar_conteiner btn large primary" style="padding: 0; padding-right: 2px; width: 100px; margin-left: 20px;">
			<div class="progress btn" style="padding: 9px 0; text-align: center; width: 0%;">0%</div>
		</div>
		<span class="waiting">Подождите, идет загрузка</span>
	</div>
</div>





<?php $this->endWidget(); ?>






<div class="hide cover_input_clone">
	<div>
		<?php echo $imageCoverInput; ?>
	</div>
</div

