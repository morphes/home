<?php
$this->breadcrumbs=array(
	'Управление баннерами'=>array('index'),
	'Список',
);

Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
$('.search-form form').submit(function(){
	$.fn.yiiGridView.update('banner-item-grid', {
		data: $(this).serialize()
	});
	return false;
});
");
?>

<h1>Управление баннерами</h1>

<?php echo CHtml::link('Фильтр','#',array('class'=>'search-button btn')); ?>
<div class="search-form" style="display:none">
<?php $this->renderPartial('_search',array(
	'model'=>$model,
)); ?>
</div><!-- search-form -->

<?php echo CHtml::button('Новый баннер', array('onclick'=>'document.location = \''.$this->createUrl('create').'\'', 'class' => 'btn primary', 'style'=>'float:right;'))?>

<?php $this->widget('ext.bootstrap.widgets.BootGridView',array(
	'id'=>'banner-item-grid',
	'dataProvider'=>$model->search(),
	'columns'=>array(
		'id',
		'customer',
		array(
			'name'=>'Показы',
			'value'=>'$data->statViews',
		),
		array(
			'name'=>'Клики',
			'value'=>'$data->statClicks',
		),
		array(
			'name'=>'Конверсия, %',
			'value'=>' ($data->statViews) ? round(100/$data->statViews*$data->statClicks, 2) : 0',
		),
		array(
			'name'=>'Тип',
			'value'=>'BannerItem::$typeLabels[$data->type_id]',
		),
		array(
			'name'=>'status',
			'value'=>'isset(BannerItem::$statusLabels[$data->status]) ? BannerItem::$statusLabels[$data->status] : ""',
		),
		array(
			'class'=>'CButtonColumn',
			'template'=>'{update}{delete}'
		),
	),
)); ?>
