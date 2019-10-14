<?php
$this->breadcrumbs=array(
	'Контент',
);
?>

<h1>Контент</h1>

<?php $this->widget('ext.bootstrap.widgets.BootListView', array(
	'dataProvider'=>$dataProvider,
	'itemView'=>'_view',
)); ?>
