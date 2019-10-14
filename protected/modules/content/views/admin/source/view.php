<?php
$this->breadcrumbs=array(
	'Иcточники'=>array('admin'),
	$model->name,
);

$this->menu=array(
	array('label'=>'List Source', 'url'=>array('index')),
	array('label'=>'Create Source', 'url'=>array('create')),
	array('label'=>'Update Source', 'url'=>array('update', 'id'=>$model->id)),
	array('label'=>'Delete Source', 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?')),
	array('label'=>'Manage Source', 'url'=>array('admin')),
);
?>

<h1>Иcточник #<?php echo $model->id; ?></h1>

<?php $this->widget('zii.widgets.CDetailView', array(
	'data'=>$model,
	'htmlOptions' => array('class' => ''),
	'attributes'=>array(
		'id',
		'name',
		'url',
		'desc',
	),
)); ?>

<?php echo CHtml::button('Редактировать', array('class' => 'btn primary', 'onclick' => 'document.location = "'.$this->createUrl('update', array('id' => $model->id)).'";'));?>
