<?php
/**
 * @var $file UploadedFile
 * @var $type - Type of view (layout, interiorContent etc.)
 * @var $parentId - id of parent document (interior, interiorContent etc.)
 */
?>
<div class="row">
	<span class="span3" style="margin-bottom: 10px;">
		<a href="<?php echo '/'.$file->getPreviewName(Interior::$preview['resize_710x475']); ?>" class="preview" title="">
			<?php echo CHtml::image('/'.$file->getPreviewName(Interior::$preview['crop_150']), '', array('width'=>150, 'height'=>150) ); ?>
		</a>
		<?php echo CHtml::link('скачать оригинал', $this->createUrl('/download/productImgOriginal', array('file_id'=>$file->id))); ?>
	</span>
	<div class="clearfix span12">
		<label>Описание<br>
			<textarea maxlength="1000" data-id="<?php echo $file->id; ?>" rows="6" class="span12 img_desc"><?php echo $file->desc; ?></textarea>
		</label>
	</div>
	<div class="span3">
		<a class="img_del" data-parent="<?php echo $parentId; ?>" data-type="<?php echo $type; ?>" data-id="<?php echo $file->id; ?>" href="#">Удалить</a>
	</div>
	<div style="clear: both"></div>
</div>
<div class="clearfix"></div>