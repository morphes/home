<?php
$this->breadcrumbs=array(
	'Промоблок. Товары. Вкладки'=>array('index'),
	$model->name,
);
?>

<h1>Вкладка #<?php echo $model->id; ?></h1>

<?php $this->widget('ext.bootstrap.widgets.BootDetailView',array(
	'data'=>$model,
	'attributes'=>array(
		'id',
		'name',
		'url',
		'position',

		array(
			'name' => 'rubric',
			'type' => 'raw',
			'value' => '<pre>'.print_r(unserialize($model->rubric), true).'</pre>'
		)
	),
)); ?>


<?php echo CHtml::link('Редактировать', array('/admin/indexProductTab/update', 'id' => $model->id), array('class' => 'primary btn')); ?>