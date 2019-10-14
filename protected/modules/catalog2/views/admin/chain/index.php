<?php
$this->breadcrumbs=array(
	'Сети магазинов'=>array('index'),
	'Управление',
);
Yii::app()->clientScript->registerScript('search', "
$('#search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
$('#search-form form').submit(function(){
	$.fn.yiiGridView.update('vendor-grid', {
		data: $(this).serialize()
	});
	return false;
});
");
?>

<h1>Сети магазинов</h1>

<div>
        <?php echo CHtml::button('Новая сеть магазинов', array('class'=>'primary btn','style'=>'float:right; margin-left: 10px;', 'onclick'=>'document.location = \''.$this->createUrl('/catalog2/admin/chain/create/').'\''))?>
</div>

<?php echo CHtml::button('Фильтр', array('class'=>'btn', 'id'=>'search-button', 'style'=>'margin-bottom: 10px;'));?>
<div class="search-form" style="display:none">
        <?php $this->renderPartial('_search',array(
        'model'=>$model,
        'date_from'=>$date_from,
        'date_to'=>$date_to,
)); ?>
</div><!-- search-form -->

<?php $this->widget('ext.bootstrap.widgets.BootGridView',array(
	'id'=>'chain-grid',
	'dataProvider'=>$dataProvider,
	'columns'=>array(
		'id',
		'name',
                array(
                        'name'=> 'create_time',
                        'value'=>'date("d.m.Y", $data->create_time)',
                ),
		array(
			'class'=>'CButtonColumn',
		),
	),
)); ?>
