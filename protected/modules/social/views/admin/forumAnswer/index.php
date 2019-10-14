<?php
$this->breadcrumbs=array(
	'Разделы форума' => array('admin/forumSection/index'),
	'Темы форума' => array('admin/forumTopic/index'),
	'Ответы',
);

Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
$('.search-form form').submit(function(){
	$.fn.yiiGridView.update('forum-answer-grid', {
		data: $(this).serialize()
	});
	return false;
});
");
?>

<h1>Ответы форума</h1>

<div style="margin-bottom: 15px;"><?php echo CHtml::link('Фильтр поиска','#',array('class'=>'search-button')); ?></div>


<div class="search-form" style="display:none">
	<?php $this->renderPartial('_search',array(
	'model'=>$model,
)); ?>
</div>


<?php $this->widget('ext.bootstrap.widgets.BootGridView',array(
	'id'=>'forum-answer-grid',
	'dataProvider'=>$model->search(),
	'columns'=>array(
		'id',
		array(
			'name' => 'answer',
			'type' => 'raw',
			'value' => 'Amputate::getLimb($data->answer, 100)'
		),
		array(
			'name' => 'author',
			'type' => 'raw',
			'value' => '($data->author) ? $data->author->name." (".$data->author->login.")"."<br>". long2ip($data->author_ip) : "гость"."<br>". long2ip($data->author_ip)'
		),
		array(
			'name' => 'topic_id',
			'type' => 'raw',
			'value' => '($data->topic_id) ? ForumTopic::model()->findByPk($data->topic_id)->name : "&mdash;"'
		),
		array(
			'name' => 'create_time',
			'type' => 'raw',
			'value' => 'date("d.m.Y", $data->create_time)'
		),
		array(
			'name' => 'status',
			"type" => 'raw',
			'value' => 'ForumAnswer::$statusNames[$data->status]'
		),
		array(
			'class'=>'CButtonColumn',
		),
	),
)); ?>
