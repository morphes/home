<?php
$this->breadcrumbs=array(
	'Цвета'=>array('index'),
	'Создание',
);
?>

<h1>Новый цвет</h1>

<?php echo $this->renderPartial('_form', array('model'=>$model)); ?>