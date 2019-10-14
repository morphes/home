<?php
$this->breadcrumbs=array(
	'Promocodes'=>array('index'),
	'Manage',
);

$this->menu=array(
	array('label'=>'List Promocode', 'url'=>array('index')),
	array('label'=>'Create Promocode', 'url'=>array('create')),
);

Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
$('.search-form form').submit(function(){
	$.fn.yiiGridView.update('promocode-grid', {
		data: $(this).serialize()
	});
	return false;
});
");
?>

<h1>Управление промокодами</h1>

<?php echo CHtml::link('Расширенный поиск','#',array('class'=>'search-button')); ?>
<div class="search-form" style="display:none">
<?php $this->renderPartial('_search',array(
	'model'=>$model,
)); ?>
</div><!-- search-form -->

<?php $this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'promocode-grid',
	'dataProvider'=>$model->search(),
	'columns'=>array(
		'id',
		'name',
		array(
			'name' => 'status',
			'type' => 'raw',
			'value' => 'Promocode::$promocodeStatus[$data->status]'
		),
		'desc',
		array(
			'class'=>'CButtonColumn',
			'template' => '{view} {update}'
		),
	),
)); ?>
