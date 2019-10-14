<?php
$this->breadcrumbs=array(
	'Отзывы на магазины'=>array('index'),
	'Список',
);

/**
 * @var $model StoreFeedback
 */


Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
$('.search-form form').submit(function(){
	$.fn.yiiGridView.update('store-feedback-grid', {
		data: $(this).serialize()
	});
	return false;
});
");
?>

<h1>Отзывы на магазины</h1>


<?php echo CHtml::link('Фильтр','#',array('class'=>'search-button btn')); ?>
<div class="search-form" style="display:none">
<?php $this->renderPartial('_search',array(
	'model'=>$model,
)); ?>
</div><!-- search-form -->

<?php $this->widget('ext.bootstrap.widgets.BootGridView',array(
	'id'           => 'store-feedback-grid',
	'dataProvider' => $model->search(),
	'columns'      => array(
		'id',
		array(
			'name' => 'store_id',
			'value' => '($s = Store::model()->findByPk($data->store_id)) ? $s->name : "—";'
		),
		array(
			'name' => 'Автор',
			'value' => '($u = $data->author) ? $u->name : "—"'
		),
		array(
			'name' => 'parent_id',
			'value' => '($data->parent_id > 0) ? "ответ на " . CHtml::link("отзыв", array("/catalog/admin/storeFeedback/view/", "id" => $data->parent_id)) : ""',
			'type' => 'raw'
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
