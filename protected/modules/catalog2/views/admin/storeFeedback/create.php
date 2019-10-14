<?php
$this->breadcrumbs=array(
	'Отзывы на магазины'=>array('index'),
	'Создание',
);

?>

<h1>Создание отзыва на магазин</h1>

<?php echo $this->renderPartial('_form', array('model'=>$model)); ?>