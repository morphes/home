<?php
$this->breadcrumbs=array(
	'Store Offers'=>array('index'),
	'Список',
);



Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
$('.search-form form').submit(function(){
	$.fn.yiiGridView.update('store-offer-grid', {
		data: $(this).serialize()
	});
	return false;
});
");
?>

<h1>Заявки на магазин</h1>


<?php echo CHtml::link('Фильтр','#',array('class'=>'search-button btn')); ?>
<div class="search-form" style="display:none">
<?php $this->renderPartial('_search',array(
	'model'=>$model,
)); ?>
</div><!-- search-form -->

<?php $this->widget('ext.bootstrap.widgets.BootGridView',array(
	'id'           => 'store-offer-grid',
	'dataProvider' => $model->search(),
	'columns'      => array(
		'id',
		'company',
		'city_name',
		'company_phone',
		'email',
		'name',
		'selected_services',
		/*
		'job',
		'site',
		'comment',
		'accept_rule',
		'status',
		*/
		array(
			'name' => 'create_time',
			'value' => 'date("d.m.Y H:i", $data->create_time)'
		),
		array(
			'class'=>'CButtonColumn',
			'template' => '{view} {delete}'
		),
	),
)); ?>
