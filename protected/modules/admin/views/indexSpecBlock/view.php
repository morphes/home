<?php
$this->breadcrumbs=array(
	'Index Spec Blocks'=>array('index'),
	$model->name,
);
?>

<h1>Просмотр IndexSpecBlock #<?php echo $model->id; ?></h1>

<?php $this->widget('ext.bootstrap.widgets.BootDetailView',array(
	'data'=>$model,
	'attributes'=>array(
		'id',
		'name',
		'url',
		'position',
		'create_time',
		'update_time',
	),
)); ?>
