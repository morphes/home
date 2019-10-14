<div class="img_item" style="margin-bottom: 10px;">
    <div class="row">
	<span class="span2">
		<img src="/<?php echo $image->getPreviewName($model::$preview['crop_80']);?>" width="80" />
	</span>
	<span class="span6">
		<textarea name="short_description[<?php echo $image->id;?>]" rows="5" cols="15"><?php echo $image->desc;?></textarea>

		<?php echo CHtml::link('Удалить', '#', array('class' => 'btn small danger delete_img_gallery', 'data-model-id' => $model->id, 'data-image-id' => $image->id));?>
	</span>
    </div>

    <div class="row">
	<span class="span6">
	    <label>Подробное описание</label>
	    <textarea name="detail_description[<?php echo $image->id;?>]" rows="5" cols="15" class="span6"><?php echo $detail_description;?></textarea>
	</span>
    </div>
</div>
