<?php
$this->breadcrumbs=array(
	'Каталог товаров'=>array('/catalog/admin/catgory/index'),
	'Главная товаров, список помещений',
);

Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
$('.search-form form').submit(function(){
	$.fn.yiiGridView.update('main-room-grid', {
		data: $(this).serialize()
	});
	return false;
});
");
?>

<?php // Подключаем скрипт для смены позиций элементов в списке
Yii::app()->getClientScript()->registerScriptFile('/js/admin/arrowsUpDown.js'); ?>

<script type="text/javascript">
	// Инициализируем стрелки перемещения позиции
	arrowsUpDown.init("main-room-grid", "/catalog/admin/mainroom/");
</script>

<h1>Главная товаров, список помещений</h1>

<?php echo CHtml::link('Расширенный поиск','#',array('class'=>'search-button btn')); ?>
<div class="search-form" style="display:none">
<?php $this->renderPartial('_search',array(
	'model'=>$model,
)); ?>
</div><!-- search-form -->

<div>
	<?php echo CHtml::button('Добавить помещение', array('class'=>'primary btn','style'=>'float:right; margin-left: 10px;', 'onclick'=>'document.location = \''.$this->createUrl('/catalog/admin/mainroom/create/').'\''))?>
</div>

<?php $this->widget('ext.bootstrap.widgets.BootGridView',array(
	'id'=>'main-room-grid',
	'dataProvider'=>$model->search(),
	'selectionChanged' => 'js:function(){
		arrowsUpDown.showArrows();
		arrowsUpDown.moveToCursor();
	}',
	'afterAjaxUpdate' => 'js:function(){
		arrowsUpDown.selectLastElement();
	}',
	'columns'=>array(
		array(
			'name' => 'id',
			'value' => '$data->id',
			'htmlOptions' => array("class" => 'elementId')
		),
		array(
			'name'=>'status',
			'type'=>'raw',
			'value'=>'MainRoom::$statusNames[$data->status]',
		),
		'name',
		array(
			'name'=>'create_time',
			'type'=>'raw',
			'value'=>'date("d.m.Y H:i", $data->create_time);',
			'sortable'=>false,
		),
		array(
			'name'=>'update_time',
			'type'=>'raw',
			'value'=>'date("d.m.Y H:i", $data->update_time);',
			'sortable'=>false,
		),
		array(
			'class'=>'CButtonColumn',
			'template'=>'{update} {delete}',
		),
	),
)); ?>
