<?php
$this->breadcrumbs=array(
	'Promocodes'=>array('index'),
	$model->name,
);

$this->menu=array(
	array('label'=>'List Promocode', 'url'=>array('index')),
	array('label'=>'Create Promocode', 'url'=>array('create')),
	array('label'=>'Update Promocode', 'url'=>array('update', 'id'=>$model->id)),
	array('label'=>'Delete Promocode', 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?')),
	array('label'=>'Manage Promocode', 'url'=>array('admin')),
);
?>

<h1>Промокод #<?php echo $model->id; ?></h1>

<?php $this->widget('zii.widgets.CDetailView', array(
	'data'=>$model,
	'attributes'=>array(
		'id',
		'name',
		'status',
		'desc',
	),
)); ?>
