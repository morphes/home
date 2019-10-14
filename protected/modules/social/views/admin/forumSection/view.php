<?php
$this->breadcrumbs=array(
	'Forum Sections'=>array('index'),
	$model->name,
);

$this->menu=array(
	array('label'=>'List ForumSection','url'=>array('index')),
	array('label'=>'Create ForumSection','url'=>array('create')),
	array('label'=>'Update ForumSection','url'=>array('update','id'=>$model->id)),
	array('label'=>'Delete ForumSection','url'=>'#','linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?')),
	array('label'=>'Manage ForumSection','url'=>array('admin')),
);
?>

<h1>View ForumSection #<?php echo $model->id; ?></h1>

<?php $this->widget('ext.bootstrap.widgets.BootDetailView',array(
	'data'=>$model,
	'attributes'=>array(
		'id',
		'status',
		'name',
		'key',
		'theme_id',
		'create_time',
		'update_time',
	),
)); ?>
