<?php
$this->breadcrumbs=array(
	'Стили'=>array('index'),
	$model->name=>array('view','id'=>$model->id),
	'Редактирование',
);
?>

<h1>Редактирование стиля (<?php echo $model->name; ?>)</h1>

<?php echo $this->renderPartial('_form',array('model'=>$model)); ?>