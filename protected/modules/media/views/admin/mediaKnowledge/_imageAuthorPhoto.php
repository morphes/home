<?php
if ($image) {
	$src = '/'.$image->getPreviewName(MediaKnowledge::$preview['crop_60']);
} else {
	$src = '/'.UploadedFile::model()->getPreviewName(MediaKnowledge::$preview['crop_60']);
}
?>

<img src="<?php echo $src;?>" width="60" alt="" />
<?php echo CHtml::activeHiddenField($model, 'author_image_id');?>
