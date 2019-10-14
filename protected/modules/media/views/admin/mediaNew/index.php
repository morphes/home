<?php
$this->breadcrumbs=array(
	'Медиа новости'=>array('index'),
	'Управление',
);

Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
$('.search-form form').submit(function(){
	$.fn.yiiGridView.update('media-new-grid', {
		data: $(this).serialize()
	});
	return false;
});
");
?>

<h1>Медиа «Новости»</h1>


<div style="margin-bottom: 15px;"><?php echo CHtml::link('Фильтр поиска','#',array('class'=>'search-button')); ?></div>

<div class="search-form" style="display:none">
	<?php $this->renderPartial('_search',array(
		'model'=>$model,
	)); ?>
</div>



<?php echo CHtml::link('Создать новость', array('/media/admin/mediaNew/create'), array('class' => 'primary btn')); ?>

<?php $this->widget('ext.bootstrap.widgets.BootGridView',array(
	'id'=>'media-new-grid',
	'dataProvider'=>$model->search(),
	'columns'=>array(
		'id',
		array(
			'name' => 'image_id',
			'value' => '($data->image_id) ? " <a href=\"".$data->getElementLink()."\"><img src=\"/".$data->preview->getPreviewName(MediaNew::$preview["crop_80"])."\" ></a>" : "&mdash;"',
			'type' => 'raw'
		),
		'title',
		'lead',
		array(
			'name' => 'author_id',
			'value' => '($data->author_id) ? $data->author->name : "&mdash;"',
			'type' => 'raw'
		),
		array(
			'name' => 'status',
			'value' => 'MediaNew::$statusNames[$data->status]'
		),
		array(
			'name'	=> 'public_time',
			'value' => 'date("d.m.Y", $data->public_time)',

		),
		array(
			'class'=>'CButtonColumn',
			'template' => '{update} {delete}'
		),
	),
)); ?>
