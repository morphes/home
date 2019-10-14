<div class="photo_item uploaded_photo"
     id="<?php echo $file->id; ?>">
	<?php echo CHtml::image('/' . $file->getPreviewName(StoreNews::$preview['crop_140']), '', array('width' => 140)); ?>
	<div class="icons">
		<i></i>

		<div class="clear"></div>
                <span>
                        <a href="#" class="image_delete">удалить</a> — <a href="/download/productImgOriginal/file_id/<?php echo $file->id;?>">скачать</a>
                </span>
	</div>
</div>