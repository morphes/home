<?php
$this->breadcrumbs=array(
	'Media Themes',
);

$this->menu=array(
	array('label'=>'Create MediaTheme','url'=>array('create')),
	array('label'=>'Manage MediaTheme','url'=>array('admin')),
);
?>

<h1>Media Themes</h1>

<?php $this->widget('ext.bootstrap.widgets.BootListView',array(
	'dataProvider'=>$dataProvider,
	'itemView'=>'_view',
)); ?>
