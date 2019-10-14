<?php
$this->breadcrumbs=array(
	'Разделы форума' => array('admin/forumSection/index'),
	'Темы форума' => array('admin/forumTopic/index'),
	'Просмотр #'.$model->id,
);
?>

<h1>Тема #<?php echo $model->id; ?></h1>

<?php $this->widget('ext.bootstrap.widgets.BootDetailView',array(
	'data'=>$model,
	'attributes'=>array(
		'id',
		array(
			'name' => 'status',
			'type' => 'raw',
			'value' => ($model->status == ForumTopic::STATUS_PUBLIC) ? "<span class=\"label success\">".ForumTopic::$statusNames[$model->status]."</span>" : "<span class=\"label important\">".ForumTopic::$statusNames[$model->status]."</span>"
		),
		'name',
		array(
			'name' => 'description',
			'type' => 'raw',
			'value' => nl2br($model->description)
		),
		array(
			'name' => 'author',
			'value' => ($model->author_id) ? $model->author->name." (".$model->author->login.")" : 'Гость'
		),
		'guest_email',
		'guest_name',

		array(
			'name' => 'section_id',
			'type' => 'raw',
			'value' => ($th = array("0"=>"&mdash;")+ForumSection::getSections()) ? $th[$model->section_id] : ""
		),
		'count_answer',
		array(
			'name' => 'create_time',
			'type' => 'raw',
			'value' => date("d.m.Y H:i:s", $model->create_time)
		),
	),
)); ?>
