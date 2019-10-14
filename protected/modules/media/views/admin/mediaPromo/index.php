<?php
$this->breadcrumbs=array(
	'Медиа промоблок'=>array('index'),
	'Управление',
);

Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
$('.search-form form').submit(function(){
	$.fn.yiiGridView.update('media-promo-grid', {
		data: $(this).serialize()
	});
	return false;
});
");
?>

<h1>Медиа «Промоблок»</h1>


<?php echo CHtml::link('Создать элемент', array('/media/admin/mediaPromo/create'), array('class' => 'primary btn')); ?>

<?php $this->widget('ext.bootstrap.widgets.BootGridView',array(
	'id'=>'media-promo-grid',
	'dataProvider'=>$model->search(),
	'columns'=>array(
		'id',
		array(
			'name' => 'image_id',
			'value' => '($data->image_id) ? "<img src=\"/".$data->preview->getPreviewName(MediaPromo::$preview["crop_80"])."\" >" : "&mdash;"',
			'type' => 'raw'
		),
		'title',
		'lead',
		'url',
		array(
			'name' => 'status',
			'value' => 'MediaPromo::$statusNames[$data->status]'
		),
		array(
			'name' => 'update_time',
			'value' => 'date("d.m.Y H:i", $data->update_time)'
		),
		array(
			'class'=>'CButtonColumn',
			'template' => '{update} {delete}'
		),
	),
)); ?>
