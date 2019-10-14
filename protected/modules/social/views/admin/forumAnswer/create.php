<?php
$this->breadcrumbs=array(
	'Разделы форума' => array('admin/forumSection/index'),
	'Темы форума' => array('admin/forumTopic/index'),
	'Ответы' => array('index'),
	'Создание ответа'
);
?>

<h1>Создание ответа</h1>

<?php echo $this->renderPartial('_form', array('model'=>$model)); ?>