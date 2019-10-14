<?php
$this->breadcrumbs=array(
	'Медиа тематики'=>array('index'),
	'Управление',
);


Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
$('.search-form form').submit(function(){
	$.fn.yiiGridView.update('media-theme-grid', {
		data: $(this).serialize()
	});
	return false;
});
");
?>

<h1>Медиа тематики</h1>

<?php echo CHtml::link('Создать тематику', array('/media/admin/mediaTheme/create'), array('class' => 'primary btn')); ?>

<?php $this->widget('ext.bootstrap.widgets.BootGridView',array(
	'id'=>'media-theme-grid',
	'dataProvider'=>$model->search(),
	'columns'=>array(
		'id',
		'name',
		'pos',
		array(
			'class'=>'CButtonColumn',
			'template' => '{update} {delete}'
		),
	),
)); ?>
