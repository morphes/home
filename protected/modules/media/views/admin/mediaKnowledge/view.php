<?php
$this->breadcrumbs=array(
	'Media Knowledges'=>array('index'),
	$model->title,
);

$this->menu=array(
	array('label'=>'List MediaKnowledge','url'=>array('index')),
	array('label'=>'Create MediaKnowledge','url'=>array('create')),
	array('label'=>'Update MediaKnowledge','url'=>array('update','id'=>$model->id)),
	array('label'=>'Delete MediaKnowledge','url'=>'#','linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?')),
	array('label'=>'Manage MediaKnowledge','url'=>array('admin')),
);
?>

<h1>View MediaKnowledge #<?php echo $model->id; ?></h1>

<?php $this->widget('ext.bootstrap.widgets.BootDetailView',array(
	'data'=>$model,
	'attributes'=>array(
		'id',
		'status',
		'author_id',
		'image_id',
		'whom_interest',
		'title',
		'lead',
		'content',
		'genre',
		'cat_category_name',
		'public_time',
		'create_time',
		'update_time',
		'count_comment',
	),
)); ?>
