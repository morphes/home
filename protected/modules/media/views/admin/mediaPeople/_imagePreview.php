<div class="clearfix" style="height: 260px;">
	<label for="MediaKnowledge_image_id"><?php echo $model->getAttributeLabel('image_id'); ?> <span class="require">*</span></label>
	<div class="input">
		<div class="row">
		<span class="span4">
			<img src="/<?php echo $image->getPreviewName($model::$preview['crop_210']);?>" width="210" />
		</span>
		<span class="span7">
			<?php echo CHtml::link('Удалить', '#', array('class' => 'btn small danger delete_preview', 'data-model-id' => $model->id, 'data-image-id' => $image->id));?>
		</span>
		</div>
	</div>
	<?php echo CHtml::fileField('preview_image', '', array('id' => 'preview_image')); ?>
	<?php echo CHtml::activeHiddenField($model, 'image_id');?>


</div>