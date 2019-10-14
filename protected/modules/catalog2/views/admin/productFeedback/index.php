<?php
$this->breadcrumbs=array(
	'Отзывы на товары'=>array('index'),
	'Список',
);



Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
$('.search-form form').submit(function(){
	$.fn.yiiGridView.update('feedback-grid', {
		data: $(this).serialize()
	});
	return false;
});
");
?>

<h1>Отзывы на товары</h1>


<?php echo CHtml::link('Фильтр','#',array('class'=>'search-button btn')); ?>
<div class="search-form" style="display:none">
<?php $this->renderPartial('_search',array(
	'model'=>$model,
)); ?>
</div><!-- search-form -->

<?php $this->widget('ext.bootstrap.widgets.BootGridView',array(
	'id'           => 'feedback-grid',
	'dataProvider' => $model->search(),
	'columns'      => array(
		'id',
		array(
			'name'  => 'product_id',
			'value' => '($p = $data->product) ? $p->name : "—"'
		),
		array(
			'name'  => 'Автор',
			'value' => '($u = $data->author) ? $u->name : "—"'
		),
		'mark',
		array(
			'name'  => 'create_time',
			'value' => 'date("d.m.Y H:i", $data->create_time)'
		),
		array(
			'class' => 'CButtonColumn',
		),
	),
)); ?>
