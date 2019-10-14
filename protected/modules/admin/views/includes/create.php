<?php
$this->breadcrumbs=array(
	'Включаемые области'=>array('index'),
	'Создание',
);

?>

<h1>Создать включаемую область</h1>

<?php echo $this->renderPartial('_form', array('model'=>$model)); ?>