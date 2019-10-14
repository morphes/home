<?php
$this->breadcrumbs=array(
	'Разделы форума' => array('admin/forumSection/index'),
	'Темы форума' => array('admin/forumTopic/index'),
	'Ответы' => array('index'),
	'Просмотр #'.$model->id,
);

?>

<h1>Ответ #<?php echo $model->id; ?></h1>

<?php $this->widget('ext.bootstrap.widgets.BootDetailView',array(
	'data'=>$model,
	'attributes'=>array(
		'id',
		array(
			'name' => 'status',
			'type' => 'raw',
			'value' => ($model->status == ForumAnswer::STATUS_PUBLIC) ? "<span class=\"label success\">".ForumAnswer::$statusNames[$model->status]."</span>" : "<span class=\"label important\">".ForumAnswer::$statusNames[$model->status]."</span>"
		),
		array(
			'name' => 'author',
			'value' => $model->author->name." (".$model->author->login.")"
		),
		array(
			'name' => 'topic_id',
			'type' => 'raw',
			'value' => ForumTopic::model()->findByPk($model->topic_id)->name
		),
		array(
			'name' => 'answer',
			'type' => 'raw',
			'value' => nl2br($model->answer)
		),
		'count_like',
		array(
			'name' => 'create_time',
			'type' => 'raw',
			'value' => date("d.m.Y H:i:s", $model->create_time)
		),
	),
)); ?>
