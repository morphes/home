<?php
$this->breadcrumbs=array(
	'Лента брендов'=>array('index'),
	$model->name,
);
?>

<h1>Просмотр бренда #<?php echo $model->id; ?></h1>

<?php $this->widget('ext.bootstrap.widgets.BootDetailView',array(
	'data'=>$model,
	'attributes'=>array(
		'id',
		'type',
		'image_id',
		'status',
		'name',
		'create_time',
		'update_time',
	),
)); ?>
