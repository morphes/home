<?php
$this->breadcrumbs=array(
	'Медиа знания'=>array('index'),
	'Управление',
);

Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
$('.search-form form').submit(function(){
	$.fn.yiiGridView.update('media-knowledge-grid', {
		data: $(this).serialize()
	});
	return false;
});
");
?>

<h1>Медиа «Знания»</h1>


<div style="margin-bottom: 15px;"><?php echo CHtml::link('Фильтр поиска','#',array('class'=>'search-button')); ?></div>

<div class="search-form" style="display:none">
	<?php $this->renderPartial('_search',array(
		'model'=>$model,
	)); ?>
</div>



<?php echo CHtml::link('Создать знание', array('/media/admin/mediaKnowledge/create'), array('class' => 'primary btn')); ?>

<?php $this->widget('ext.bootstrap.widgets.BootGridView',array(
	'id'=>'media-knowledge-grid',
	'dataProvider'=>$model->search(),
	'columns'=>array(
		'id',
		array(
			'name' => 'image_id',
			'value' => '($data->image_id) ? " <a href=\"".$data->getElementLink()."\"><img src=\"/".$data->preview->getPreviewName(MediaKnowledge::$preview["crop_80"])."\" ></a>" : "&mdash;"',
			'type' => 'raw'
		),
		'title',
		'lead',
		'section_url',

		array(
			'name' => 'genre',
			'value' => '($data->genre) ? MediaKnowledge::$genreNames[$data->genre] : "&mdash;"',
			'type' => 'raw',
		),
		array(
			'name' => 'author_id',
			'value' => '($data->author_id) ? $data->author->name : "&mdash;"',
			'type' => 'raw'
		),
		array(
			'name' => 'status',
			'value' => 'MediaKnowledge::$statusNames[$data->status]'
		),
		array(
			'name'	=> 'public_time',
			'value' => 'date("d.m.Y", $data->public_time)',
			'sortable' => true
		),
		array(
			'class'=>'CButtonColumn',
			'template' => '{update} {delete}'
		),
	),
)); ?>
