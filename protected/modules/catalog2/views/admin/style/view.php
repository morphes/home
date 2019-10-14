<?php
$this->breadcrumbs=array(
	'Стили'=>array('index'),
	$model->name,
);
?>

<h1>Просмотр стиля (<?php echo $model->name; ?>)</h1>

<?php $this->widget('ext.bootstrap.widgets.BootDetailView',array(
	'data'=>$model,
	'attributes'=>array(
		'id',
		'name',
		'desc',
	),
)); ?>
