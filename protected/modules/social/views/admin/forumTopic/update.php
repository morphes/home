<?php
$this->breadcrumbs=array(
	'Разделы форума'=>array('admin/forumSection/index'),
	'Темы форума'=>array('index'),
	'Редактирование «'.$model->name.'»'

);
?>

<h1>Тема #<?php echo $model->id; ?></h1>

<?php echo $this->renderPartial('_form',array('model'=>$model)); ?>