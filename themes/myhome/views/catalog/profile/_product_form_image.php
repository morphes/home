<div class="photo_item uploaded_photo" id="<?php echo $file->id; ?>">
    <?php echo CHtml::image('/' . $file->getPreviewName(Config::$preview['crop_78']), '', array('width'=>78)); ?>
    <div class="icons">
        <i></i>
        <div class="clear"></div>
                <span>
                        <span>удалить</span><br>
                        <span>фото</span>
                </span>
    </div>
</div>