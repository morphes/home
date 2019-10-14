<?php
$this->breadcrumbs=array(
	'Разделы форума',
);

$this->menu=array(
	array('label'=>'List ForumSection','url'=>array('index')),
	array('label'=>'Create ForumSection','url'=>array('create')),
);

Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
$('.search-form form').submit(function(){
	$.fn.yiiGridView.update('forum-section-grid', {
		data: $(this).serialize()
	});
	return false;
});
");
?>

<h1>Разделы форума</h1>

<?php echo CHtml::link('Создать раздел', array('/social/admin/forumSection/create'), array('class' => 'primary btn')); ?>

<?php $this->widget('ext.bootstrap.widgets.BootGridView',array(
	'id'=>'forum-section-grid',
	'dataProvider'=>$model->search(),
	//'filter'=>$model,
	'columns'=>array(
		'id',

		'name',
		'key',
		array(
			'name' => 'theme_id',
			'type' => 'raw',
			'value' => '($th = array("0"=>"&mdash;")+MediaTheme::getThemes()) ? $th[$data->theme_id] : ""'
		),
		array(
			'name' => 'create_time',
			'value' => 'date("d.m.Y", $data->create_time)'
		),
		array(
			'name' => 'status',
			'type' => 'raw',
			'value' => '($data->status == ForumSection::STATUS_PUBLIC) ? "<span class=\"label success\">".ForumSection::$statusNames[$data->status]."</span>" : "<span class=\"label important\">".ForumSection::$statusNames[$data->status]."</span>"'
		),
		array(
			'name' => 'action',
			'type' => 'raw',
			'value' => '"<a href=\"".Yii::app()->controller->createUrl("admin/forumTopic/index", array("ForumTopic" => array("section_id" => $data->id)))."\">список тем</a>"'
		),
		array(
			'class'=>'CButtonColumn',
			'template' => '{update} {delete}'
		),
	),
)); ?>
