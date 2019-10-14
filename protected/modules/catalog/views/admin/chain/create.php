<?php
$this->breadcrumbs=array(
	'Сети магазинов'=>array('index'),
	'Новая сеть',
);
?>

<h1>Новая сеть магазинов</h1>

<?php echo $this->renderPartial('_form', array('model'=>$model)); ?>