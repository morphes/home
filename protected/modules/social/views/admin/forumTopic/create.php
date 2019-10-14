<?php
$this->breadcrumbs=array(
	'Разделы форума'=>array('admin/forumSection/index'),
	'Темы форума'=>array('index'),
	'Создание',
);
?>

<h1>Создать тему форума</h1>

<?php echo $this->renderPartial('_form', array('model'=>$model)); ?>