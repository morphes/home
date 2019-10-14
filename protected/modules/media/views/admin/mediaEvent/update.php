<?php
$this->breadcrumbs=array(
	'Медиа типы событий'=>array('index'),
	'Редактирование',
);
?>

<h1>Редактирование типа событий #<?php echo $model->id; ?></h1>

<?php
/** @var $form BootActiveForm */
$form=$this->beginWidget('ext.bootstrap.widgets.BootActiveForm',array(
	'id'=>'media-theme-form',
	'enableAjaxValidation'=>false,
	'htmlOptions'=>array('class'=>'form-stacked'),
));
?>

<?php echo $form->errorSummary($model); ?>

<div class="clearfix <?php if ($model->getError('themes')) echo 'error';?>">
	<label class="required <?php if ($model->getError('themes')) echo 'error';?>">Тематики <span class="required">*</span></label>
	<div class="input">
		<?php echo CHtml::activeCheckBoxList($model, 'themes', MediaTheme::getThemes(), array('class' => 'span7 theme_input')); ?>
	</div>
</div>

<?php echo $form->textFieldRow($model,'name',array('class'=>'span5')); ?>

<div class="clearfix">
	<?php echo $form->labelEx($model, 'image_id'); ?>
	<div class="input">
		<div id="img-container"><?php if (!is_null($model->image_id)) {
			$previewFile = UploadedFile::model()->findByPk($model->image_id);
			if (!is_null($previewFile))
				echo CHtml::image('/'.$previewFile->getPreviewName(MediaEvent::$preview['crop_80']));
		} ?></div>
		<?php $this->widget('ext.FileUpload.FileUpload', array(
			'url'=> $this->createUrl('upload', array('eid'=>$model->id)),
			'postParams'=>array(),
			'config'=> array(
				'fileName' => 'MediaEvent[file]',
				'onSuccess'=>'js:function(response){ $("#img-container").html(response.html); }',
			),
			'htmlOptions'=>array('size'=>61, 'accept'=>'image', 'class'=>'img_input', 'multiple'=>false),
		)); ?>
	</div>
</div>

<?php echo $form->dropDownListRow($model, 'event_type', array(''=>'')+CHtml::listData($eventTypes, 'id', 'name') ); ?>

<?php echo $form->checkBoxRow($model, 'is_online'); ?>

<?php echo $form->dropDownListRow($model, 'whom_interest', array(
	'' => '',
	MediaEvent::WHOM_SPEC => 'Специалистам',
	MediaEvent::WHOM_USER => 'Владельцам квартир',
	MediaEvent::WHOM_SPEC_USER => 'Специалистам + владельцам квартир'
), array('class' => 'span7'));?>

<?php echo $form->textFieldRow($model,'meta_desc',array('class'=>'span7','maxlength'=>255)); ?>

<div class="clearfix">
	<label><?php echo $model->getAttributeLabel('content'); ?> <span class="require">*</span></label>
	<div class="input">
		<?php
		$this->widget('application.extensions.cleditor.ECLEditor', array(
			'model'=>$model,
			'attribute'=>'content', //Model attribute name. Nome do atributo do modelo.
			'options'=>array(
				'controls'	=> 'loadimage addgallery fpreviewtext style | bold italic underline strikethrough | removeformat | bullets numbering | undo redo | link unlink | pastetext | source',
				'width'		=> '730',
				'height'	=> 550,
				'useCSS'	=> true,
				'settings'	=> array(
					'addgallery' => array(
						// ID модели, который редактируем
						'model_id' => $model->id,
						// URL для получения номера галереи
						'urlGetNumber' => '/media/admin/mediaEvent/getNumberOfGallery',
						// URL для сохранения описаний фоток
						'urlSaveDesc' => '/media/admin/mediaEvent/saveImageDescription',
						// URL для получения всех фотографий галереи
						'urlGetImages' => '/media/admin/mediaEvent/getAllGalleryImages',
						// URL загрузки изображения
						'urlUploadImage' => '/media/admin/mediaEvent/uploadImageGallery/id/'.$model->id,
						//
						'urlDeleteImage' => '/media/admin/mediaEvent/deleteImageGallery',
						// URL Для получения списка всех галерей, загруженных ранее
						'urlGetListGallery' => '/media/admin/mediaEvent/getListGallery',
					),
					'selgallery' => array('model_id' => $model->id),
				)
			),
			'value'=>'<div></div>', //If you want pass a value for the widget. I think you will.
		));
		?>
	</div>
</div>

<?php echo $form->textAreaRow($model, 'organizer', array('style'=>'width:500px;height:150px'))?>

<div class="clearfix">
	<label><?php echo $model->getAttributeLabel('start_time'); ?></label>
	<div class="input">
		<?php
		$this->widget('application.extensions.timepicker.EJuiDateTimePicker', array(
			'value'=>empty($model->start_time) ? '' : date('d.m.Y H:i', $model->start_time),
			'htmlOptions' => array('name'=>'', 'id'=>'date1', 'class'=>'span5'),
			'options' => array(
				'autoLanguage' => false,
				'dateFormat' => 'dd.mm.yy',
				'timeFormat' => 'hh:mm',
				'changeMonth' => true,
				'changeYear' => true,
				'timeOnlyTitle' => 'Выберите время',
				'timeText' => 'Время',
				'hourText' => 'Часы',
				'minuteText' => 'Минуты',
				'secondText' => 'Секунды',
				'closeText' => 'Закрыть',
				'onSelect'=>'js:function(){
					var epoch = $.datepicker.formatDate("@", $(this).datepicker("getDate")) / 1000+10800;
					$("#start_time").val(epoch);
				}',
			),
		));
		echo CHtml::hiddenField('MediaEvent[start_time]', $model->start_time, array('id'=>'start_time'));
		?>
	</div>
</div>

<div class="clearfix">
	<label><?php echo $model->getAttributeLabel('end_time'); ?></label>
	<div class="input">
		<?php
		$this->widget('application.extensions.timepicker.EJuiDateTimePicker', array(
			'value'=>empty($model->end_time) ? '' : date('d.m.Y H:i', $model->end_time),
			'htmlOptions' => array('name'=>'', 'id'=>'date2', 'class'=>'span5'),
			'options' => array(
				'autoLanguage' => false,
				'dateFormat' => 'dd.mm.yy',
				'timeFormat' => 'hh:mm',
				'changeMonth' => true,
				'changeYear' => true,
				'timeOnlyTitle' => 'Выберите время',
				'timeText' => 'Время',
				'hourText' => 'Часы',
				'minuteText' => 'Минуты',
				'secondText' => 'Секунды',
				'closeText' => 'Закрыть',
				'onSelect'=>'js:function(){
					var epoch = $.datepicker.formatDate("@", $(this).datepicker("getDate")) / 1000+10800;
					$("#end_time").val(epoch);
				}',
			),
		));
		echo CHtml::hiddenField('MediaEvent[end_time]', $model->end_time, array('id'=>'end_time'));
		?>
		<span class="btn" onclick="$('#end_time').val(''); $('#date2').val(''); return false; ">Очистить</span>
	</div>
</div>

<div class="clearfix">
	<?php echo $form->labelEx($model, 'place'); ?>
	<div class="input">
		<div class="input">
			<table class="bordered-table span14" id="place-table">
				<tr>
					<th>Город</th>
					<th>Название места проведения</th>
					<th>Адрес</th>
					<th>Время проведения</th>
					<th  style="width: 62px;"></th>
				</tr>
				<?php
				/** @var $place MediaEventPlace */
				foreach ($places as $place) {
					$this->renderPartial('_placeItem', array('place'=>$place));
				}
				?>

			</table>

			<?php echo Chtml::button('+ еще место проведения', array('class'=>'btn primary', 'id'=>'place-create-button'))?>
		</div>
	</div>
</div>

<?php echo $form->textFieldRow($model, 'cost', array('class'=>'span5')); ?>
<?php echo $form->textFieldRow($model, 'site', array('class'=>'span5')); ?>

<div class="clearfix">
	<label><?php echo $model->getAttributeLabel('public_time'); ?></label>
	<div class="input">
		<?php
		$this->widget('application.extensions.timepicker.EJuiDateTimePicker', array(
			'value'=>empty($model->public_time) ? '' : date('Y m d H:i', $model->public_time),
			'htmlOptions' => array('name'=>'', 'id'=>'date3', 'class'=>'span5'),
			'options' => array(
				'autoLanguage' => false,
				'dateFormat' => 'yy mm dd',
				'timeFormat' => 'hh:mm',
				'changeMonth' => true,
				'changeYear' => true,
				'timeOnlyTitle' => 'Выберите время',
				'timeText' => 'Время',
				'hourText' => 'Часы',
				'minuteText' => 'Минуты',
				'secondText' => 'Секунды',
				'closeText' => 'Закрыть',
				'onSelect'=>'js:function(dateText, inst){
					var epoch = $.datepicker.formatDate("@", $(this).datepicker("getDate")) / 1000+10800;
					$("#public_time").val(epoch);
				}',
				'onClose'=>'js:function(dateText, inst){
					var epoch = $.datepicker.formatDate("@", new Date(dateText)) / 1000+10800;
					$("#public_time").val(epoch);
				}',
			),
		));
		echo CHtml::hiddenField('MediaEvent[public_time]', $model->public_time, array('id'=>'public_time'));
		?>
	</div>
</div>

<?php echo $form->dropDownListRow($model, 'status', MediaEvent::$statusNames, array('class'=>'span5')); ?>

<div class="actions">
	<?php echo CHtml::submitButton('Сохранить',array('class'=>'btn primary')); ?>
</div>

<?php $this->endWidget(); ?>

<script type="text/javascript">
	// Добавление соавтора
	$('#place-create-button').click(function(){
		$.ajax({
			url: "<?php echo '/'.$this->module->id.'/'.$this->id.'/getplace'; ?>",
			data: {"eventId":'<?php echo $model->id; ?>'},
			dataType: "json",
			type: "post",
			success: function(response){
				if (response.success) {
					$('#place-table').append(response.data);
				}
				if (response.error) {
					alert(response.error);
				}
			}
		});
		return false;
	});

	function removePlace(self, placeId){
		$.ajax({
			url:"<?php echo '/'.$this->module->id.'/'.$this->id.'/removeplace'; ?>",
			data:{"placeId":placeId},
			dataType:"json",
			type: "post",
			success: function(response){
				if (response.success) {
					$(self).parents(".place-item").remove();
				}
			}
		});
		return false;
	}
</script>