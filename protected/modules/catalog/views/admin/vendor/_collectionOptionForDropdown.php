<option value="0" selected="selected">Не выбрано</option>


<?php
if ( ! is_array($colls))
	$colls = array();

foreach($colls as $collection) {
	echo CHtml::tag('option', array('value'=>$collection['id']), $collection['name']);
}
?>

