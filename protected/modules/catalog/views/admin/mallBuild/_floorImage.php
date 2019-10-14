<div class="clearfix">
	<div class="input">
		<?php $src = $image->getPreviewName(MallFloor::$preview['resize_190']); ?>
		<img src="<?php echo '/'.$src;?>" <?php echo UploadedFile::getImageSize($src, 'str');?> />
	</div>
</div>
