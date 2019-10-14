<?php
$this->breadcrumbs=array(
	'Включяемые области'=>array('index'),
	'Список',
);


Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
$('.search-form form').submit(function(){
	$.fn.yiiGridView.update('includes-grid', {
		data: $(this).serialize()
	});
	return false;
});
");
?>

<h1>Включаемые области</h1>

<?php echo CHtml::link('Фильтр','#',array('class'=>'search-button btn')); ?>
<div class="search-form" style="display:none">
<?php $this->renderPartial('_search',array(
	'model'=>$model,
)); ?>
</div><!-- search-form -->
<div style="margin-bottom: 10px;"></div>
<?php echo CHtml::link('Добавить запись', array('/admin/includes/create'), array('class' => 'primary btn')); ?>

<?php $this->widget('ext.bootstrap.widgets.BootGridView',array(
	'id'           => 'includes-grid',
	'dataProvider' => $model->search(),
	'columns'      => array(
		'id',
		'key',
		'text',
		array(
			'name' => 'create_time',
			'value' => 'date("d.m.Y", $data->create_time)'
		),
		array(
			'name' => 'update_time',
			'value' => 'date("d.m.Y", $data->update_time)'
		),
		array(
			'class' => 'CButtonColumn',
		),
	),
)); ?>
