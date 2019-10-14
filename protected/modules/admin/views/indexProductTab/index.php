<?php
$this->breadcrumbs=array(
	'Промоблок. Товары. Вкладки'=>array('index'),
	'Список',
);

Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
$('.search-form form').submit(function(){
	$.fn.yiiGridView.update('index-product-tab-grid', {
		data: $(this).serialize()
	});
	return false;
});
");
?>

<h1>Промоблок. Товары. Вкладки</h1>


<?php echo CHtml::link('Фильтр','#',array('class'=>'search-button btn')); ?>
<div class="search-form" style="display:none">
<?php $this->renderPartial('_search',array(
	'model'=>$model,
)); ?>
</div><!-- search-form -->

<div style="margin-bottom: 10px;"></div>
<?php echo CHtml::link('Добавить запись', array('/admin/indexProductTab/create'), array('class' => 'primary btn')); ?>

<h1 style="margin-top:20px;"><?php // Включаемая область
$this->widget('application.components.widgets.WIncludes', array('key' => 'main_products_for_home'));?></h1>

<?php $this->widget('ext.bootstrap.widgets.BootGridView',array(
	'id'           => 'index-product-tab-grid',
	'dataProvider' => $model->search(),
	'columns'      => array(
		'name',
		'position',

		'url',
		array(
			'class' => 'CButtonColumn',
		),
	),
)); ?>
