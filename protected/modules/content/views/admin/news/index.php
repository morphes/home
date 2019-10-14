<?php
$this->breadcrumbs=array(
	'Новости',
);

$this->menu=array(
	array('label'=>'Create News','url'=>array('create')),
	array('label'=>'Manage News','url'=>array('admin')),
);
?>

<h1>News</h1>

<?php $this->widget('ext.bootstrap.widgets.BootListView',array(
	'dataProvider'=>$dataProvider,
	'itemView'=>'_view',
)); ?>
