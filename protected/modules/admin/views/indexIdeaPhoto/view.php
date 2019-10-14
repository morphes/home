<?php
$this->breadcrumbs=array(
	'Блок идей. Фотографии'=>array('index'),
	$model->name,
);
?>

<h1>Просмотр фотографии #<?php echo $model->id; ?></h1>

<?php $this->widget('ext.bootstrap.widgets.BootDetailView',array(
	'data'=>$model,
	'attributes'=>array(
		'id',
		'image_id',
		'model_id',
		'status',
		'name',
		'user_id',
		'create_time',
		'update_time',
	),
)); ?>
