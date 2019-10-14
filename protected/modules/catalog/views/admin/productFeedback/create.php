<?php
$this->breadcrumbs=array(
	'Отзывы на товары'=>array('index'),
	'Создание',
);

?>

<h1>Создание отзыва на товар</h1>

<?php echo $this->renderPartial('_form', array('model'=>$model)); ?>