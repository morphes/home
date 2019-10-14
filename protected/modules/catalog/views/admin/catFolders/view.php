<?php
$this->breadcrumbs=array(
	'Папки миры'=>array('index'),
	$model->name,
);

$this->menu=array(
	array('label'=>'List CatFolders','url'=>array('index')),
	array('label'=>'Create CatFolders','url'=>array('create')),
	array('label'=>'Update CatFolders','url'=>array('update','id'=>$model->id)),
	array('label'=>'Delete CatFolders','url'=>'#','linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?')),
	array('label'=>'Manage CatFolders','url'=>array('admin')),
);
?>

<h1>Просмотр папки <?php echo $model->name; ?></h1>

<?php $this->widget('ext.bootstrap.widgets.BootDetailView',array(
	'data'=>$model,
	'attributes'=>array(
		'id',
		'name',
		'user_id',
		'status',
		'description',
		'count',
		'update_time',
		'create_time',
	),
)); ?>
