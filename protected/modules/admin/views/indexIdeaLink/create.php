<?php
$this->breadcrumbs=array(
	'Блок идей. Ссылки'=>array('index'),
	'Создание',
);

?>

<h1>Добавление ссылки</h1>

<?php echo $this->renderPartial('_form', array('model'=>$model)); ?>