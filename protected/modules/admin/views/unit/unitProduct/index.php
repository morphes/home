<?php
$this->breadcrumbs=array(
	'Юнит Товары'=>array('index'),
	'Управление',
);


Yii::app()->clientScript->registerScript('search', "
$('.search-form form').submit(function(){
	$.fn.yiiGridView.update('unit-product-grid', {
		data: $(this).serialize()
	});
	return false;
});
");
?>

<h1>Управление товарами на главной странице</h1>

<?php echo CHtml::link('Новый товар', array('create'), array('class' => 'btn primary')); ?>

<?php $this->widget('ext.bootstrap.widgets.BootGridView',array(
	'id'=>'unit-product-grid',
	'dataProvider'=>$model->search(),
	'columns'=>array(
		'id',
		array(
			'name' => 'status',
			'value' => '$data->getHtmlStatus()',
			'type' => 'raw'
		),
		array(
			'name' => 'Товар',
			'value' => '$data->product->name'
		),
		array(
			'name' => 'Фото',
			'value' => '$data->getProdPhoto()',
			'type' => 'raw'
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
