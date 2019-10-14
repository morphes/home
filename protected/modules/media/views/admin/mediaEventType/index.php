<?php
$this->breadcrumbs=array(
	'Медиа тематики'=>array('index'),
	'Управление',
);
?>

<h1>Медиа типы событий</h1>

<?php echo CHtml::link('Создать тип события', array('/media/admin/mediaEventType/create'), array('class' => 'primary btn')); ?>

<?php $this->widget('ext.bootstrap.widgets.BootGridView',array(
	'id'=>'media-event-type-grid',
	'updateSelector'=> '#dummy', // Устанавливаем пустым, чтобы без ajax'а была постраничка
	'dataProvider'=>$model->search(),
	'columns'=>array(
		'id',
		'name',
		array(
			'name'	=> 'create_time',
			'value' => 'date("d.m.Y H:i:s", $data->create_time)',
			'sortable' => true
		),
		array(
			'name'	=> 'update_time',
			'value' => 'date("d.m.Y H:i:s", $data->update_time)',
			'sortable' => true
		),
		array(
			'class'=>'CButtonColumn',
			'template' => '{update} {delete}'
		),
	),
)); ?>
