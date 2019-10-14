<?php
$this->breadcrumbs=array(
	'Отзывы на магазины'=>array('index'),
	$model->id=>array('view','id'=>$model->id),
	'Редактирование',
);
?>

<h1>Редактирование StoreFeedback <?php echo $model->id; ?></h1>

<?php echo $this->renderPartial('_form',array('model'=>$model)); ?>