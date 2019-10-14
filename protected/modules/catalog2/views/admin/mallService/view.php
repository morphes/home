<?php
$this->breadcrumbs=array(
	'Услуги в ТЦ'=>array('index'),
	$model->name,
);
?>

<h1>Просмотр услуги #<?php echo $model->id; ?></h1>

<?php $this->widget('ext.bootstrap.widgets.BootDetailView',array(
	'data'=>$model,
	'attributes'=>array(
		'id',
		'name',
		'pos',
		array(
			'name' => 'create_time',
			'value' => date("d.m.Y H:i", $model->create_time)
		),
		array(
			'name' => 'update_time',
			'value' => date("d.m.Y H:i", $model->update_time)
		),

	),
)); ?>

<div class="actions">
	<?php echo CHtml::link('Редкатировать', array('/catalog2/admin/mallService/update/', 'id' => $model->id), array('class' => 'btn primary')); ?>
</div>