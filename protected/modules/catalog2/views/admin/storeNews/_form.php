<?php /** @var $form BootActiveForm */
$form = $this->beginWidget('ext.bootstrap.widgets.BootActiveForm', array(
	'id'                   => 'store-news-form',
	'enableAjaxValidation' => false,
	'htmlOptions'          => array(
		'enctype' => 'multipart/form-data',
	),
)); ?>


<?php echo $form->errorSummary($model); ?>

<?php echo $form->dropDownListRow($model, 'status', array('' => '') + StoreNews::$statuses, array('class' => 'span5')); ?>


<div class="clearfix">
	<label><?php echo StoreNews::model()->getAttributeLabel('image_id');?></label>
	<div class="input">
		<?php $this->widget('ext.FileUpload.FileUpload', array(
			'url'         => $this->createUrl('imageUpload'),
			'postParams'  => array('nid' => $model->id),
			'config'      => array(
				'fileName'   => 'StoreNews[file]',
				'onSuccess'  => 'js:function(response){
							if (response.success) {
								$("#add-image").html(response.html);
								$(".photo_hint").hide();
							} else {

								$(".uploaded_image_error").html(response.message).show();
							}
						}',
				'onStart'    => 'js:function(data){
							$(".uploaded_image_error").hide();
							$(".photofile_input").addClass("disabled");
						}',
				'onFinished' => 'js:function(data){
							$(".photofile_input").removeClass("disabled");
					}'
			),
			'htmlOptions' => array('accept' => 'image', 'class' => 'photofile_input'),
		)); ?>

		<div id="add-image">
			<?php
			$file = UploadedFile::model()->findByPk($model->image_id);
			if ($file) {
				echo $this->renderPartial('_image', array('file' => $file));
			}
			?>
		</div>
		<span class="uploaded_image_error hide label important"></span>

		<script>
			// Удаление загруженной фотографии для шапки Минисайта
			$('#add-image').on('click', '.image_delete', function(){
				$.ajax({
					url: '/catalog2/admin/storeNews/imageDelete',
					data: {
						nid: '<?php echo $model->id;?>',
						fid: $('.uploaded_photo').attr('id')
					},
					dataType: 'json',
					method: 'POST',
					success: function(data){
						if (!data.success) {
							alert(data.message);
						} else {
							$('#add-image').empty();
						}
					},
					error: function(data){
						alert(data.responseText + '('+data.responseStatus+')');
					}

				});

				return false;
			});
		</script>

	</div>
</div>

<?php echo $form->textFieldRow($model, 'store_id', array('class' => 'span5')); ?>

<?php echo $form->textFieldRow($model, 'title', array('class' => 'span5', 'maxlength' => 255)); ?>

<?php echo $form->textAreaRow($model, 'content', array('rows' => 12, 'class' => 'span10')); ?>


<div class="actions">
	<?php echo CHtml::submitButton($model->isNewRecord ? 'Создать'
		: 'Сохранить', array('class' => 'btn primary')); ?>
</div>

<?php $this->endWidget(); ?>
