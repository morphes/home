<?php
$this->breadcrumbs=array(
	'Content Categories'=>array('index'),
	$model->title,
);

$this->menu=array(
	array('label'=>'List ContentCategory', 'url'=>array('index')),
	array('label'=>'Create ContentCategory', 'url'=>array('create')),
	array('label'=>'Update ContentCategory', 'url'=>array('update', 'id'=>$model->id)),
	array('label'=>'Delete ContentCategory', 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?')),
	array('label'=>'Manage ContentCategory', 'url'=>array('admin')),
);
?>

<h1>View ContentCategory #<?php echo $model->id; ?></h1>

<?php $this->widget('zii.widgets.CDetailView', array(
	'data'=>$model,
	'attributes'=>array(
		'id',
		'left_key',
		'right_key',
		'level',
		'author_id',
		'status',
		'title',
		'alias',
		'desc',
		'create_time',
		'update_time',
	),
)); ?>
