<?php
/**
 * @var $file UploadedFile
 */
?>
<div class="row" id="main-image">
	<span class="span3">
		<a href="<?php echo '/'.$file->getPreviewName(Interior::$preview['resize_710x475']); ?>" class="preview" title="">
			<?php echo CHtml::image('/'.$file->getPreviewName(Interior::$preview['crop_150']), '', array('width'=>150, 'height'=>150) ); ?>
		</a>
	</span>
	<div class="clearfix span12">
		<label>Описание<br><textarea maxlength="1000" data-id="<?php echo $file->id; ?>" rows="6" class="span12 img_desc"><?php echo $file->desc; ?></textarea></label>
	</div>
	<div style="clear: both"></div>
</div>