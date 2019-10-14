<div class="photo_item uploaded_photo"
     id="<?php echo $file->id; ?>">
	<?php echo CHtml::image('/' . $file->getPreviewName(Store::$preview['resize_280']), '', array('width' => 280)); ?>
	<div class="icons">
		<i></i>

		<div class="clear"></div>
                <span>
                        <a href="#" class="head_image_delete">удалить</a> — <a href="/download/productImgOriginal/file_id/<?php echo $file->id;?>">скачать</a>
                </span>
	</div>
</div>