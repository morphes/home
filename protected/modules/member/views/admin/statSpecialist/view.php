<?php
$this->breadcrumbs=array(
	'Stat Specialists'=>array('index'),
	$model->id,
);

$this->menu=array(
	array('label'=>'List StatSpecialist','url'=>array('index')),
	array('label'=>'Create StatSpecialist','url'=>array('create')),
	array('label'=>'Update StatSpecialist','url'=>array('update','id'=>$model->id)),
	array('label'=>'Delete StatSpecialist','url'=>'#','linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?')),
	array('label'=>'Manage StatSpecialist','url'=>array('admin')),
);
?>

<h1>View StatSpecialist #<?php echo $model->id; ?></h1>

<?php $this->widget('ext.bootstrap.widgets.BootDetailView',array(
	'data'=>$model,
	'attributes'=>array(
		'id',
		'specialist_id',
		'view',
		'type',
		'time',
	),
)); ?>
