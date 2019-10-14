<?php
$this->breadcrumbs=array(
	'Источники',
);

$this->menu=array(
	array('label'=>'List Source', 'url'=>array('index')),
	array('label'=>'Create Source', 'url'=>array('create')),
);

Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
$('.search-form form').submit(function(){
	$.fn.yiiGridView.update('source-grid', {
		data: $(this).serialize()
	});
	return false;
});
");
?>

<h1>Управление Источниками</h1>


<?php echo CHtml::link('Фильтр поиска','#',array('class'=>'search-button')); ?>

<div class="search-form" style="display:none">
	<?php $this->renderPartial('_search',array(
		'model'=>$model,
	)); ?>
</div><!-- search-form -->

<?php $this->widget('ext.bootstrap.widgets.BootGridView', array(
	'id'=>'source-grid',
	'itemsCssClass' => 'zebra-striped condensed-table bordered-table',
	'dataProvider'=>$model->search(),
	'columns'=>array(
		'id',
		'name',
		'url',
		'desc',
		array(
			'class'=>'CButtonColumn',
		),
	)
)); ?>
