<?php
$this->breadcrumbs=array(
	'Включаемые области'=>array('index'),
	$model->id,
);

$this->menu=array(
	array('label'=>'List Includes','url'=>array('index')),
	array('label'=>'Create Includes','url'=>array('create')),
	array('label'=>'Update Includes','url'=>array('update','id'=>$model->id)),
	array('label'=>'Delete Includes','url'=>'#','linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?')),
	array('label'=>'Manage Includes','url'=>array('admin')),
);
?>

<h1>View Includes #<?php echo $model->id; ?></h1>

<?php $this->widget('ext.bootstrap.widgets.BootDetailView',array(
	'data'=>$model,
	'attributes'=>array(
		'id',
		'key',
		'text',
		array(
			'name' => 'create_time',
			'value' => date('d.m.Y', $model->create_time)
		),
		array(
			'name' => 'update_time',
			'value' => date('d.m.Y', $model->update_time)
		),
	),
)); ?>
