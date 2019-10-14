<?php
$this->breadcrumbs=array(
	'Услуги в ТЦ'=>array('index'),
	'Список',
);
?>

<h1>Список услуг для ТЦ</h1>

<?php echo CHtml::link('Добавить услугу', '/catalog2/admin/mallService/create/', array('class' => 'btn primary'));?>

<?php $this->widget('ext.bootstrap.widgets.BootGridView',array(
	'id'           => 'mall-service-grid',
	'dataProvider' => $model->search(),
	'columns'      => array(
		'name',
		'pos',
		array(
			'class' => 'CButtonColumn',
		),
	),
)); ?>
