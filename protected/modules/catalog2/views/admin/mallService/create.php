<?php
$this->breadcrumbs=array(
	'Услуги в ТЦ'=>array('index'),
	'Добавление',
);
?>

<h1>Добавление услуги</h1>

<?php echo $this->renderPartial('_form', array('model'=>$model)); ?>