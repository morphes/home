<?php
$this->breadcrumbs=array(
	'Производители'=>array('index'),
	'Управление',
);

Yii::app()->clientScript->registerScript('search', "
$('#search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
$('.search-form form').submit(function(){
	$.fn.yiiGridView.update('vendor-grid', {
		data: $(this).serialize()
	});
	return false;
});
");
?>

<h1>Управление производителями</h1>

<div>
        <?php echo CHtml::button('Новый производитель', array('class'=>'primary btn','style'=>'float:right', 'onclick'=>'document.location = \''.$this->createUrl('create').'\''))?>
</div>

<?php echo CHtml::button('Фильтр', array('class'=>'btn', 'id'=>'search-button', 'style'=>'margin-bottom: 10px;'));?>
<div class="search-form" style="display:none">
        <?php $this->renderPartial('_search',array('model'=>$model)); ?>
</div><!-- search-form -->

<?php $this->widget('ext.bootstrap.widgets.BootGridView',array(
	'id'=>'vendor-grid',
        'dataProvider'=>$dataProvider,
        'template'=> "{items}\n{pager}",
	'columns'=>array(
		'id',
		'name',
		array(
			'class'=>'CButtonColumn',
			'htmlOptions'=>array('width'=>'80px'),
			'buttons'=>array(
				'exportImport' => array(
					'label'=>'<img src="/img/admin/small/import-export.png">',
					'options'=>array('title'=>'Экспорт-импорт товаров производителя'),
					'url'=>'Yii::app()->createUrl("/catalog2/admin/vendor/exportImport/", array("vid"=>$data->id))',
				),
			),
			'template'=>'{exportImport} {view} {update}',
		),
	),
)); ?>
