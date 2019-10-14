<?php
$this->breadcrumbs=array(
	'Торговые центры'=>array('index'),
	$model->name=>array('view','id'=>$model->id),
	'Редактирование',
);
?>

<h1>Редактирование ТЦ #<?php echo $model->id; ?></h1>

<?php echo $this->renderPartial('_form',array('model'=>$model, 'workTime' => $workTime)); ?>