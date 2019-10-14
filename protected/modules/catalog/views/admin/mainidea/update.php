<?php
/**
 * @var $model MainUnit
 * @var $photo UploadedFile
 */
$this->breadcrumbs=array(
	'Каталог товаров'=>array('/catalog/admin/catgory/index'),
	'Главная товаров, список помещений'=>array('/catalog/admin/mainidea/index'),
	'Редактирование идеи',
);
?>

<h1>Редактирование идеи #<?php echo $model->id; ?></h1>

<?php /** @var $form BootActiveForm */
$form=$this->beginWidget('ext.bootstrap.widgets.BootActiveForm',array(
	'id'=>'main-unit-form',
	'enableAjaxValidation'=>false,
)); ?>

<?php echo $form->errorSummary($model); ?>

<div class="row">
	<div class="span8 clearfix">
		<?php echo $form->textFieldRow($model,'origin_id',array('class'=>'span5 product_id', 'autocomplete' => 'off', 'id'=>'origin')); ?>
	</div>
	<div class="span2">
		<span id="show" class="btn primary small">показать</span>
	</div>
</div>

<div id="replace" class="clearfix">
	<?php echo $form->textFieldRow($model, 'name', array('class'=>'span5')); ?>
	<div class="clearfix">
		<label>Пикча</label>
		<div class="input">
			<?php
			if ($photo===null)
				$src = UploadedFile::getDefaultImage('default', '150x150');
			else
				$src = $photo->getPreviewName(MainUnit::$preview['crop_150']);
			echo CHtml::image('/'.$src, '', array('id'=>'big_photo'));
			?>
		</div>
	</div>
</div>

<input type="hidden" id="x" name="img[x]" />
<input type="hidden" id="y" name="img[y]" />
<input type="hidden" id="w" name="img[w]" />
<input type="hidden" id="h" name="img[h]" />
<input type="hidden" id="photo_id" name="MainUnit[file_id]" value="<?php echo $model->file_id; ?>"/>

<div class="clearfix">
	<label>Помещения</label>
	<div class="input">
		<ul><?php foreach ($mainRooms as $room) {
			echo CHtml::openTag('li');
			$checked = isset($sRooms[$room->id]);
			echo CHtml::checkBox('MainUnitRoom['.$room->id.']', $checked, array()) . $room->name;
			echo CHtml::closeTag('li');
		} ?></ul>
	</div>
</div>

<?php echo $form->dropDownListRow($model, 'status', MainUnit::$statusNames, array('class'=>'span5')); ?>

<div class="actions">
	<?php echo CHtml::submitButton('Сохранить', array('class'=>'btn primary')); ?>
	<?php echo CHtml::button('Отмена', array('class'=>'btn default','onclick' => "document.location = '".$this->createUrl('/catalog/admin/mainidea/index')."'"));?>
</div>

<?php $this->endWidget(); ?>

<script type="text/javascript">
	$('#origin').keydown(function(event){
		if (event.keyCode == 13) {
			$('#show').trigger('click');
			return false;
		}
	});
	$('#replace').on('click', '.photos img', function(){
		$('#photo_id').val( $(this).data('id') );
		$('#replace .photos img').each(function(){
			$(this).css({'border-color':'#ddd'});
		});
		$(this).css({'border-color':'red'});
		return false;
	});

	$('#replace').on('change', '#room_select', function(){
		$('.photos .media-grid').each(function(){
			$(this).hide();
		});
		$('#room_'+this.value).show();
		return false;
	});

	$('#show').click(function(){
		var ideaId = parseInt( $('#origin').val() );
		if (isNaN(ideaId)){
			$('#origin').parents('.clearfix').addClass('error');
			return false;
		}

		$('#origin').parents('.clearfix').removeClass('error');

		$.ajax({
			url: 	'/catalog/admin/mainidea/axgetcontent',
			dataType: 'json',
			type: 'post',
			data: {idea_id: ideaId},
			async: 	false,
			success:function (response) {
				if (response.success){
					$('#replace').html( response.html );
				}
			},
			error: function(response){
				if (response.statusText){
					alert(response.statusText);
				} else {
					window.location.reload();
				}
			}

		});
	});

</script>