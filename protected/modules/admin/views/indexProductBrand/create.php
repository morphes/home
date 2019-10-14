<?php
$this->breadcrumbs=array(
	'Лента брендов'=>array('index'),
	'Создание',
);

?>

<h1>Создать бренд</h1>

<?php echo $this->renderPartial('_form', array('model'=>$model)); ?>