<?php
$this->breadcrumbs=array(
	'Производители'=>array('index'),
	$model->name,
);
?>

<h1>Просмотр производителя (<?php echo $model->id; ?>)</h1>

<?php $this->widget('ext.bootstrap.widgets.BootDetailView',array(
	'data'=>$model,
	'attributes'=>array(
		'id',
		'name',
		'desc',
	),
)); ?>
