<?php
$this->breadcrumbs=array(
	'PDF-свидетельства'=>array('index'),
	$model->name,
);

$this->menu=array(
	array('label'=>'List CopyrightFile','url'=>array('index')),
	array('label'=>'Create CopyrightFile','url'=>array('create')),
	array('label'=>'Update CopyrightFile','url'=>array('update','id'=>$model->id)),
	array('label'=>'Delete CopyrightFile','url'=>'#','linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?')),
	array('label'=>'Manage CopyrightFile','url'=>array('admin')),
);
?>

<h1>Просмотр PDF-свидетельства #<?php echo $model->id; ?></h1>

<?php $this->widget('ext.bootstrap.widgets.BootDetailView',array(
	'data'=>$model,
	'attributes'=>array(
		'id',
		'number',
		'name',
		array(
			'name' => 'author_id',
			'value' => $model->author->getName()." (".$model->author->login.")"
		),
		'interior_id',
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
