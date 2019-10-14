<?php
$this->breadcrumbs=array(
	'Торговые центры'=>array('index'),
	'Список',
);
?>

<h1>Торговые центры</h1>

<?php echo CHtml::link('Добавить ТЦ', '/catalog/admin/mallBuild/create/', array('class' => 'btn primary'));?>

<?php $this->widget('ext.bootstrap.widgets.BootGridView',array(
	'id'=>'mall-build-grid',
	'dataProvider'=>$model->search(),
	'columns'=>array(
		'id',
		array(
			'name' => 'name',
			'type' => 'html',
			'value' => 'Chtml::link($data->name, MallBuild::getLink("adminView", $data->id))'
		),
		array(
			'name' => 'city_id',
			'type' => 'html',
			'value' => '$data->city->name." (".$data->city->country->name.")"'
		),
		array(
			'name' => 'Этажей',
			'type' => 'raw',
			'value' => '($data->floors) ? count($data->floors) : 0'
		),

		array(
			'name' => 'create_time',
			'value' => 'date("d.m.Y", $data->create_time)'
		),
		array(
			'class'=>'CButtonColumn',
		),
	),
)); ?>
