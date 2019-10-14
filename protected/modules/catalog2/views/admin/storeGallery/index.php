<?php
$this->breadcrumbs=array(
	'Галереи магазинов'=>array('index'),
	'Список',
);



Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
$('.search-form form').submit(function(){
	$.fn.yiiGridView.update('store-gallery-grid', {
		data: $(this).serialize()
	});
	return false;
});
");
?>

<h1>Список фотографий</h1>


<?php echo CHtml::link('Фильтр','#',array('class'=>'search-button btn')); ?>
<div class="search-form" style="display:none">
<?php $this->renderPartial('_search',array(
	'model'=>$model,
)); ?>
</div><!-- search-form -->

<div style="margin-top: 20px;"></div>

<?php echo CHtml::link('Добавить фотографию', '/catalog2/admin/storeGallery/create/', array('class' => 'btn primary')); ?>

<?php $this->widget('ext.bootstrap.widgets.BootGridView',array(
	'id'           => 'store-gallery-grid',
	'dataProvider' => $model->search(),
	'columns'      => array(
		'id',

		array(
			'name' => 'image_id',
			'value' => '($data->image_id) ? "<img width=\"100\" src=\"/".$data->preview->getPreviewName(StoreNews::$preview["crop_140"])."\" >" : "—"',
			'type' => 'raw'
		),
		array(
			'name' => 'store_id',
			'value' => 'Store::model()->findByPk($data->store_id)->name'
		),
		array(
			'name'  => 'status',
			'value' => '$data->getStatusHtml()',
			'type'  => 'raw'
		),
		array(
			'name' => 'user_id',
			'value' => '($data->user_id) ? $data->author->name : "—"'
		),
		'name',
		array(
			'name'	=> 'create_time',
			'value' => 'date("d.m.Y H:i", $data->create_time)',

		),
		array(
			'class' => 'CButtonColumn',
		),
	),
)); ?>
