<?php
$this->breadcrumbs=array(
	'Цвета'=>array('index'),
	$model->name=>array('view','id'=>$model->id),
	'Редактирование',
);
?>

<h1>Редактирование цвета (<?php echo $model->name; ?>)</h1>

<?php echo $this->renderPartial('_form',array('model'=>$model)); ?>