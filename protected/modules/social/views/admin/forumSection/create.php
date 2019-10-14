<?php
$this->breadcrumbs=array(
	'Разделы форума'=>array('index'),
	'Создание',
);

$this->menu=array(
	array('label'=>'List ForumSection','url'=>array('index')),
	array('label'=>'Manage ForumSection','url'=>array('admin')),
);
?>

<h1>Создание раздела форума</h1>

<?php echo $this->renderPartial('_form', array('model'=>$model)); ?>