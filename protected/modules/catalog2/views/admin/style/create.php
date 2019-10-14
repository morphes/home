<?php
$this->breadcrumbs=array(
	'Стили'=>array('index'),
	'Создание',
);
?>

<h1>Новый стиль</h1>

<?php echo $this->renderPartial('_form', array('model'=>$model)); ?>