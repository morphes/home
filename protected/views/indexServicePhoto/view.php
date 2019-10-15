<?php
/* @var $this IndexServicePhotoController */
/* @var $model IndexServicePhoto */

$this->breadcrumbs=array(
	'Index Service Photos'=>array('index'),
	$model->name,
);

$this->menu=array(
	array('label'=>'List IndexServicePhoto', 'url'=>array('index')),
	array('label'=>'Create IndexServicePhoto', 'url'=>array('create')),
	array('label'=>'Update IndexServicePhoto', 'url'=>array('update', 'id'=>$model->id)),
	array('label'=>'Delete IndexServicePhoto', 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?')),
	array('label'=>'Manage IndexServicePhoto', 'url'=>array('admin')),
);
?>

<h1>View IndexServicePhoto #<?php echo $model->id; ?></h1>

<?php $this->widget('zii.widgets.CDetailView', array(
	'data'=>$model,
	'attributes'=>array(
		'id',
		'image_id',
		'product_id',
		'type',
		'status',
		'price',
		'name',
		'create_time',
		'update_time',
	),
)); ?>
