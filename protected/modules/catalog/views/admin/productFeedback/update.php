<?php
$this->breadcrumbs=array(
	'Отзывы на товары'=>array('index'),
	$model->id=>array('view','id'=>$model->id),
	'Редактирование',
);
?>

<h1>Редактирование отзыва на товар <?php echo $model->id; ?></h1>

<?php echo $this->renderPartial('_form',array('model'=>$model)); ?>