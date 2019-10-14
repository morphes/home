<?php
$this->breadcrumbs=array(
	'Включаемые области'=>array('index'),
	$model->id=>array('view','id'=>$model->id),
	'Редактирование',
);
?>

<h1>Редактирование области #<?php echo $model->id; ?></h1>

<?php echo $this->renderPartial('_form',array('model'=>$model)); ?>