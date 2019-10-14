<div class="form">

        <?php echo CHtml::beginForm($this->createUrl($this->id.'/image_update'), 'post', array('name'=>'image','enctype'=>'multipart/form-data'));?>        
                
                <div class="row">
                        <?php echo CHtml::image('/'.$image->getPreviewName(Config::$preview['crop_150']));?><br />
                        <?php echo CHtml::activeFileField($image,'uploadfile', array('onchange' => 'document.forms.image.submit();'));?>
                        <?php echo CHtml::error($image,'image_file', array('style'=>'color: #900;'));?>
                </div>
                
        <?php echo CHtml::endForm();?>

</div>
<script type="text/javascript">
	window.onload = function () {
		elem = parent.document.getElementById("file_id");
		elem.value = <?php echo $image->id; ?>;
	}
</script>