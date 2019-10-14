<?php
$this->breadcrumbs=array(
	'Блок идей. Ссылки'=>array('index'),
	$model->name,
);
?>

<h1>Просмотр ссылки #<?php echo $model->id; ?></h1>

<?php $this->widget('ext.bootstrap.widgets.BootDetailView',array(
	'data'=>$model,
	'attributes'=>array(
		'id',
		'name',
		'url',
		'position',
		array(
			'name'  => 'create_time',
			'value' => date("d.m.Y H:i", $model->create_time)
		),
		array(
			'name'  => 'update_time',
			'value' => date("d.m.Y H:i", $model->update_time)
		),
	),
)); ?>
