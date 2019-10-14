<?php
$this->breadcrumbs=array(
	'Цвета'=>array('index'),
	$model->name,
);
?>

<h1>Просмотр цвета (<?php echo $model->name; ?>)</h1>

<?php $this->widget('ext.bootstrap.widgets.BootDetailView',array(
	'data'=>$model,
	'attributes'=>array(
		'id',
		'name',
		'desc',
		'param',
	),
)); ?>
