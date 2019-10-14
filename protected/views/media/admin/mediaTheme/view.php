<?php
$this->breadcrumbs=array(
	'Media Themes'=>array('index'),
	$model->name,
);

$this->menu=array(
	array('label'=>'List MediaTheme','url'=>array('index')),
	array('label'=>'Create MediaTheme','url'=>array('create')),
	array('label'=>'Update MediaTheme','url'=>array('update','id'=>$model->id)),
	array('label'=>'Delete MediaTheme','url'=>'#','linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?')),
	array('label'=>'Manage MediaTheme','url'=>array('admin')),
);
?>

<h1>View MediaTheme #<?php echo $model->id; ?></h1>

<?php $this->widget('ext.bootstrap.widgets.BootDetailView',array(
	'data'=>$model,
	'attributes'=>array(
		'id',
		'status',
		'name',
		'create_time',
	),
)); ?>
