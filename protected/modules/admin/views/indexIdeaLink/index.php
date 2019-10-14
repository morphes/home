<?php
$this->breadcrumbs = array(
	'Блок идей. Ссылки' => array('index'),
	'Список',
);


Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
$('.search-form form').submit(function(){
	$.fn.yiiGridView.update('index-idea-link-grid', {
		data: $(this).serialize()
	});
	return false;
});
");
?>

<h1>Блок идей. Ссылки</h1>


<div style="margin-bottom: 10px;"></div>
<?php echo CHtml::link('Добавить запись', array('/admin/indexIdeaLink/create'), array('class' => 'primary btn')); ?>

<div class="well" style="margin-top: 10px;">
	ВНИМАНИЕ! Добавленные ссылки начнут отображаться на главной странице
	только в том случае, когда добавленных элементов >=6.
	Иначе показывается заглушка
</div>

<?php $this->widget('ext.bootstrap.widgets.BootGridView', array(
	'id'           => 'index-idea-link-grid',
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
		),
	),
)); ?>
