<div class="well floor alert-message block-message" data-floor_id="<?php echo $floor->id;?>" id="floor_<?php echo $floor->id;?>">
	<a class="close">×</a>

	<div class="clearfix">
		<label>Название:&nbsp;</label>
		<?php echo CHtml::activeTextField($floor, 'name', array('class' => 'floor_name')); ?>
	</div>

	<div class="clearfix" >
		<label>План этажа:&nbsp;</label>
		<?php $this->widget('ext.FileUpload.FileUpload', array(
		'url'         => $this->createUrl('ajaxAppendFloorImage', array('fid' => $floor->id)),
		'postParams'  => array(),
		'config'      => array(
			'fileName'   => 'MallFloor[file]',
			'onSuccess'  => 'js:function(response){ $("#floor_"+'.$floor->id.').find(".floor_image").html(response.html); }',
			'onStart'    => 'js:function(data){ $("#load_img").show(); }',
			'onFinished' => 'js:function(data){ $("#load_img").hide(); }'
		),
		'htmlOptions' => array('size' => 61, 'accept' => 'image', 'class' => 'img_input', 'id' => 'image_input_'.$floor->id),
	)); ?>
		<img src="/img/loaderT.gif" alt="" id="load_img" style="margin-top: 8px; margin-left: 6px; width: 16px; display: none;">


		<div class="floor_image">
			<?php
			if ($floor->image_id) {
				$this->renderPartial('_floorImage', array('image' => $floor->image));
			}
			?>
		</div>
	</div>

</div>