<?php
$this->breadcrumbs=array(
	'Производители'=>array('index'),
	'Создание',
);
?>

<h1>Новый производитель</h1>

<?php echo $this->renderPartial('_form', array('model'=>$model)); ?>