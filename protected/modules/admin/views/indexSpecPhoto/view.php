<?php
$this->breadcrumbs=array(
	'Index Spec Photos'=>array('index'),
	$model->name,
);
?>

<h1>Просмотр IndexSpecPhoto #<?php echo $model->id; ?></h1>

<?php $this->widget('ext.bootstrap.widgets.BootDetailView',array(
	'data'=>$model,
	'attributes'=>array(
		'id',
		'image_id',
		'model_id',
		'status',
		'name',
		'create_time',
		'update_time',
	),
)); ?>
