<?php
$this->breadcrumbs=array(
	'Стили'=>array('index'),
	'Управление',
);

Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
$('.search-form form').submit(function(){
	$.fn.yiiGridView.update('style-grid', {
		data: $(this).serialize()
	});
	return false;
});
");
?>

<h1>Управление стилями</h1>

<?php /*
<?php echo CHtml::link('Advanced Search','#',array('class'=>'search-button btn')); ?>
<div class="search-form" style="display:none">
<?php $this->renderPartial('_search',array(
	'model'=>$model,
)); ?>
</div><!-- search-form -->
*/?>

<div>
        <?php echo CHtml::button('Новый стиль', array('class'=>'primary btn','style'=>'float:right', 'onclick'=>'document.location = \''.$this->createUrl('create').'\''))?>
</div>

<?php $this->widget('ext.bootstrap.widgets.BootGridView',array(
	'id'=>'style-grid',
        'dataProvider'=>$dataProvider,
        'template'=>"{items}\n{pager}",
	'columns'=>array(
		'id',
		'name',
		array(
			'class'=>'CButtonColumn',
		),
	),
)); ?>
