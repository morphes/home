<li class="span4">
    <a href="<?php echo '/'.$data->getPreviewName(Config::$preview['resize_710x475'] ); ?>" class="preview" rel="tooltip" data-title="Tooltip">
	 <?php echo CHtml::image( '/'.$data->getPreviewName( Config::$preview['crop_230'] ), '', array('class'=>'thumbnail') ); ?>
    </a>
</li>