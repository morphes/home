<?php
$this->breadcrumbs=array(
	'Медиа Люди говорят'=>array('index'),
	'Управление',
);

Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
$('.search-form form').submit(function(){
	$.fn.yiiGridView.update('media-people-grid', {
		data: $(this).serialize()
	});
	return false;
});
");
?>

<h1>Медиа «Люди говорят»</h1>


<?php echo CHtml::link('Создать элемент', array('/media/admin/mediaPeople/create'), array('class' => 'primary btn')); ?>

<?php $this->widget('ext.bootstrap.widgets.BootGridView',array(
	'id'=>'media-people-grid',
	'dataProvider'=>$model->search(),
	'columns'=>array(
		'id',
		array(
			'name' => 'image_id',
			'value' => '($data->image_id) ? "<img src=\"/".$data->photo->getPreviewName(MediaPeople::$preview["crop_80"])."\" >" : "&mdash;"',
			'type' => 'raw'
		),
		'fio',
		'job',
		'message',
		'url',
		array(
			'name' => 'status',
			'value' => 'MediaPeople::$statusNames[$data->status]'
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
