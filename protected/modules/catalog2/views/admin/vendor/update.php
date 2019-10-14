<?php
$this->breadcrumbs=array(
	'Производители'=>array('index'),
	$model->name=>array('view','id'=>$model->id),
	'Редактирование',
);
?>

<h1>Редактирование производителя (<?php echo $model->name; ?>)</h1>

<?php echo $this->renderPartial('_form',array('model'=>$model)); ?>