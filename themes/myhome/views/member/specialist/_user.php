<div class="item"><a href="<?php echo $data->getLinkProfile();?>">
	<span class="photo">
		<?php echo CHtml::image(
			'/'.$data->getPreview( Config::$preview['crop_45'] ), '',
			array('width' => 45, 'heght' => 45)
		);?>
	</span>
	<span class="name">
		<strong><?php echo CHtml::value($data, 'name');?></strong><br>
		<?php echo CHtml::value($data, 'login');?>
	</span>
	<span class="rating-nu"><?php echo CHtml::value($data, 'count_interior');?></span>
</a></div>