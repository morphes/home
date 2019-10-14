<?php
$this->breadcrumbs=array(
	'Портфолио'=>array('index'),
	'Список' => array('index'),
	'Добавление'
);
?>

<h1>Добавление портфолио</h1>

<?php echo $this->renderPartial('_form', array('model'=>$model)); ?>