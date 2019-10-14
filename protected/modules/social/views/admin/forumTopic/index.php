<?php
$this->breadcrumbs=array(
	'Разделы форума'=>array('admin/forumSection/index'),
	'Темы форума',
);

Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
$('.search-form form').submit(function(){
	$.fn.yiiGridView.update('forum-topic-grid', {
		data: $(this).serialize()
	});
	return false;
});
");
?>

<h1>Темы форума</h1>


<div style="margin-bottom: 15px;"><?php echo CHtml::link('Фильтр поиска','#',array('class'=>'search-button')); ?></div>


<div class="search-form" style="display:none">
	<?php $this->renderPartial('_search',array(
	'model'=>$model,
)); ?>
</div>


<?php echo CHtml::link('Создать тему', array('/social/admin/forumTopic/create'), array('class' => 'primary btn')); ?>

<?php $this->widget('ext.bootstrap.widgets.BootGridView',array(
	'id'=>'forum-topic-grid',
	'dataProvider'=>$model->search(),
	'columns'=>array(
		'id',
		'name',
		array(
			'name' => 'description',
			'type' => 'raw',
			'value' => 'Amputate::getLimb($data->description, 100)'
		),
		array(
			'name' => 'author_id',
			'type' => 'raw',
			'value' => '($data->author_id) ? $data->author->name." (".$data->author->login.")"."<br>". long2ip($data->author_ip) : "Гость"."<br>". long2ip($data->author_ip)'
		),
		array(
			'name' => 'section_id',
			'type' => 'raw',
			'value' => '($th = array("0"=>"&mdash;")+ForumSection::getSections()) ? $th[$data->section_id] : ""'
		),
		array(
			'name' => 'create_time',
			'type' => 'raw',
			'value' => 'date("d.m.Y", $data->create_time)'
		),
		array(
			'name' => 'status',
			"type" => 'raw',
			'value' => '($data->status == ForumTopic::STATUS_PUBLIC) ? "<span class=\"label success\">".ForumTopic::$statusNames[$data->status]."</span>" : "<span class=\"label important\">".ForumTopic::$statusNames[$data->status]."</span>"'
		),
		array(
			'name' => 'action',
			'type' => 'raw',
			'value' => '"<a href=\"".Yii::app()->controller->createUrl("admin/forumAnswer/create", array("topic_id" => $data->id))."\">добавить ответ</a>"'
		),
		/*
		'theme_id',
		'count_answer',
		'create_time',
		'update_time',
		*/
		array(
			'class'=>'CButtonColumn',
		),
	),
)); ?>
