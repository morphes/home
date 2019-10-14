<?php
if ( ! $uploadedImage)
	return true;

echo CHtml::tag('table').CHtml::tag('tr');
				
echo CHtml::tag('td', array('width' => '20%'));
$image = CHtml::image('/'.$uploadedImage->getPreviewName(Config::$preview['crop_150'], 'interior'), '');
$src_big = '/'.$uploadedImage->getPreviewName(Config::$preview['resize_710x475'], 'interior');
echo CHtml::link($image, $src_big, array('class' => 'preview') );
echo CHtml::closeTag('td');



echo CHtml::tag('td');
echo CHtml::activeLabel($uploadedImage, 'desc');
echo CHtml::value($uploadedImage, 'desc');
echo '<br /><br />';
echo CHtml::activeLabel($uploadedImage, 'keywords');
echo CHtml::value($uploadedImage, 'keywords');
echo CHtml::closeTag('td');

echo CHtml::closeTag('tr').CHtml::closeTag('table');

?>