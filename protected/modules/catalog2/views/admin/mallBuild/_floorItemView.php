<div class="well" style="float: left; height: 210px;">

	<h4><?php echo CHtml::value($floor, 'name'); ?></h4>


	<img src="/img/loaderT.gif" alt="" id="load_img" style="margin-top: 8px; margin-left: 6px; width: 16px; display: none;">

	<div class="floor_image">
		<?php
		if ($floor->image_id) {
			$this->renderPartial('_floorImage', array('image' => $floor->image));
		}
		?>
	</div>
</div>