<?php
$this->breadcrumbs=array(
	'Блок специалистов. Ссылки'=>array('index'),
	'Список',
);



Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
$('.search-form form').submit(function(){
	$.fn.yiiGridView.update('index-spec-block-grid', {
		data: $(this).serialize()
	});
	return false;
});
");
?>

<h1>Блок специалистов. Ссылки</h1>


<?php echo CHtml::link('Фильтр','#',array('class'=>'search-button btn')); ?>
<div class="search-form" style="display:none">
	<?php $this->renderPartial('_search',array(
		'model'=>$model,
	)); ?>
</div><!-- search-form -->


<div style="margin-bottom: 10px;"></div>
<?php echo CHtml::link('Добавить запись', array('/admin/indexSpecBlock/create'), array('class' => 'primary btn')); ?>

<div class="well" style="margin-top: 10px;">
	ВНИМАНИЕ! Добавленные ссылки начнут отображаться на главной странице
	только в том случае, когда добавленных элементов >=3.
	Иначе показывается заглушка
</div>


<?php $this->widget('ext.bootstrap.widgets.BootGridView',array(
	'id'           => 'index-spec-block-grid',
	'dataProvider' => $model->search(),
	'columns'      => array(
		'id',
		'name',
		'url',
		'position',
		array(
			'name'  => 'update_time',
			'value' => 'date("d.m.Y H:i", $data->update_time)'
		),
		array(
			'class' => 'CButtonColumn',
			'template' => '{update} {delete}'
		),
	),
)); ?>
