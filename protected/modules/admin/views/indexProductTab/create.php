<?php
$this->breadcrumbs=array(
	'Промоблок. Товары. Вкладки'=>array('index'),
	'Создание',
);

?>

<h1>Создание вкладки</h1>

<?php echo $this->renderPartial('_form', array('model'=>$model)); ?>