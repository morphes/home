<?php
$this->breadcrumbs=array(
	'Разделы форума' => array('admin/forumSection/index'),
	'Темы форума'    => array('admin/forumTopic/index'),
	'Ответы'         => array('index'),
	'Редактирование «' . $model->id . "»"
);

$this->menu=array(
	array('label'=>'List ForumAnswer','url'=>array('index')),
	array('label'=>'Create ForumAnswer','url'=>array('create')),
	array('label'=>'View ForumAnswer','url'=>array('view','id'=>$model->id)),
	array('label'=>'Manage ForumAnswer','url'=>array('admin')),
);
?>

<h1>Update ForumAnswer <?php echo $model->id; ?></h1>

<?php echo $this->renderPartial('_form',array('model'=>$model)); ?>