<?php
$this->breadcrumbs=array(
	'Услуги'=>array('index'),
	'Добавление услуги',
);
?>

<h1>Добавление услуги</h1>

<?php echo $this->renderPartial('_form', array('model'=>$model)); ?>