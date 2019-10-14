<?php
$this->breadcrumbs=array(
	'Новости магазинов'=>array('index'),
	'Создание',
);

?>

<h1>Добавление новости магазина</h1>

<?php echo $this->renderPartial('_form', array('model'=>$model)); ?>